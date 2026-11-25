// =========================================================================
// Helpers  (el(), setParams(), DataTable live in datatable.js)
// =========================================================================

const HANDLER = 'admin_handler.php';
const USER_HANDLER = 'user_handler.php';
const BULK_HANDLER = 'bulk_handler.php';

function toFormData(params) {
	if (params instanceof FormData) return params;
	const fd = new FormData();
	Object.entries(params).forEach(([k, v]) => {
		if (Array.isArray(v)) v.forEach(x => fd.append(k + '[]', x));
		else fd.append(k, v);
	});
	return fd;
}

function postText(url, params) {
	return fetch(url, { method: 'POST', body: toFormData(params) }).then(async res => {
		const text = await res.text();
		if (!res.ok) throw new Error(text || 'HTTP ' + res.status);
		return text;
	});
}

function postJson(url, params) {
	return postText(url, params).then(text => {
		try {
			return JSON.parse(text);
		} catch (e) {
			console.error('Invalid JSON from server:', text);
			throw e;
		}
	});
}

function postRawJson(url, payload) {
	return fetch(url, {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify(payload)
	}).then(async res => {
		const text = await res.text();
		let data;
		try {
			data = JSON.parse(text);
		} catch (e) {
			throw new Error(text || 'HTTP ' + res.status);
		}
		if (!res.ok || data.error) {
			throw new Error(data.error || 'HTTP ' + res.status);
		}
		return data;
	});
}

function makeBtn(label, cls, onClick) {
	const b = document.createElement('button');
	b.type = 'button';
	b.textContent = label;
	b.className = 'btn btn-sm ' + cls;
	b.addEventListener('click', onClick);
	return b;
}

function toast(message, type) {
	const host = document.getElementById('toastHost');
	if (!host) return;
	const t = el('div', 'toast ' + (type || ''), message);
	host.appendChild(t);
	setTimeout(() => t.remove(), 4000);
}

function fmtDate(s) {
	const d = s ? new Date(String(s).replace(' ', 'T')) : null;
	return d && !isNaN(d) ? d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '—';
}

const ROLE_LABELS = { officer: 'Officer', program: 'Program Director', executive: 'Executive Director' };

// =========================================================================
// Modals
// =========================================================================

function openModal(id) {
	const m = document.getElementById(id);
	if (!m) return;
	m.hidden = false;
	document.body.classList.add('modal-open');
	const f = m.querySelector('input:not([type=hidden]), select');
	if (f) f.focus();
}

function closeModal(id) {
	const m = document.getElementById(id);
	if (m) m.hidden = true;
	if (!document.querySelector('.modal-backdrop:not([hidden])')) document.body.classList.remove('modal-open');
}

function showDyn(title, bodyNode) {
	document.getElementById('dynTitle').textContent = title;
	document.getElementById('dynBody').replaceChildren(bodyNode);
	openModal('dynModal');
}

document.addEventListener('click', e => {
	if (e.target.classList.contains('modal-backdrop')) closeModal(e.target.id);

	const closer = e.target.closest('[data-close-modal]');
	if (closer) closeModal(closer.closest('.modal-backdrop').id);

	const v = e.target.closest('[data-ml-view]');
	if (v) showMasterlistView(v.dataset.mlView);

	const a = e.target.closest('[data-act]');
	if (a) {
		const acts = {
			'add-school': () => openModal('addSchoolModal'),
			'add-program': () => openProgramModal(),
			'add-user': () => openUserModal()
		};
		if (acts[a.dataset.act]) acts[a.dataset.act]();
	}
});

document.addEventListener('keydown', e => {
	if (e.key !== 'Escape') return;
	const open = [...document.querySelectorAll('.modal-backdrop:not([hidden])')].pop();
	if (open) closeModal(open.id);
});

function formModal(opts) {
	const form = el('form', 'stacked-form flat');
	opts.fields.forEach(f => {
		const label = el('label', null, f.label + (f.required ? ' *' : ''));
		label.htmlFor = 'fm_' + f.name;
		const input = document.createElement('input');
		input.type = f.type || 'text';
		input.id = 'fm_' + f.name;
		input.name = f.name;
		input.value = f.value || '';
		if (f.required) input.required = true;
		form.append(label, input);
	});
	const actions = el('div', 'modal-actions');
	const cancel = makeBtn('Cancel', 'btn-outline', () => closeModal('dynModal'));
	const save = el('button', 'btn btn-primary', opts.submit || 'Save changes');
	save.type = 'submit';
	actions.append(cancel, save);
	form.appendChild(actions);

	form.addEventListener('submit', e => {
		e.preventDefault();
		opts.onSubmit(Object.fromEntries(new FormData(form)))
			.then(() => closeModal('dynModal'))
			.catch(err => toast(err.message, 'error'));
	});
	showDyn(opts.title, form);
}

// =========================================================================
// Tables & shared state
// =========================================================================

let schoolTable, userTable;

function refreshAll() {
	loadProgramFormSchools();
	if (roleNeedsSchool()) loadUserFormSchools();
	return Promise.all([schoolTable.reload(true), userTable.reload(true)]);
}

function normalizeSchool(s) {
	const programs = s.programs || [];
	const executives = s.executives || [];
	return Object.assign({}, s, {
		programs,
		executives,
		program_count: programs.length,
		status: !programs.length ? 'No programs' : (!executives.length ? 'No executive' : 'Ready')
	});
}

function statusBadge(text) {
	return el('span', 'badge ' + (text === 'Ready' || text === 'Active' ? 'badge-active' : 'badge-archived'), text);
}

function mutedText(t) { return el('span', 'muted', t); }

// Track expanded schools for program sub-rows
const expandedSchools = new Set();

function buildSchoolTable() {
	return new DataTable({
		key: 'schools',
		prefix: 's_',
		mount: 'schoolTableMount',
		searchPlaceholder: 'Search schools, programs, executives…',
		id: r => r.school_id,
		load: () => postJson(HANDLER, { action: 'get_schools_table' }).then(d => d.map(normalizeSchool)),
		onChange: renderHome,
		onAdd: () => openModal('addSchoolModal'),
		addLabel: 'Add school',
		columns: [
			{
				key: 'school', label: 'School', sticky: true, get: r => r.school_name,
				render: r => {
					const wrap = el('span', 'school-cell-wrap');
					wrap.appendChild(el('strong', null, r.school_name));
					return wrap;
				}
			},
			{
				key: 'executive', label: 'Executive Director', get: r => r.executives.join('; '),
				render: r => r.executives.length ? r.executives.join(', ') : mutedText('Not assigned')
			},
			{
				key: 'programs', label: 'Programs', get: r => r.programs.map(p => p.program_name).join('; '),
				render: r => {
					if (!r.programs.length) return mutedText('No programs');
					const container = el('div', 'school-table-list');
					container.style.cssText = 'display: flex; flex-direction: column; gap: 4px;';
					r.programs.forEach(p => {
						const item = el('div', null, p.program_name);
						container.appendChild(item);
					});
					return container;
				}
			},
			{
				key: 'programdir', label: 'Program Director', get: r => r.programs.map(p => (p.directors && p.directors.length ? p.directors.join(', ') : 'Not assigned')).join('; '),
				render: r => {
					if (!r.programs.length) return mutedText('Not assigned');
					const container = el('div', 'school-table-list');
					container.style.cssText = 'display: flex; flex-direction: column; gap: 4px;';
					r.programs.forEach(p => {
						const dirs = p.directors && p.directors.length ? p.directors.join(', ') : 'Not assigned';
						const item = el('div', null, dirs);
						if (dirs === 'Not assigned') {
							item.className = 'muted';
						}
						container.appendChild(item);
					});
					return container;
				}
			},
			{ key: 'status', label: 'Setup', get: r => r.status, render: r => statusBadge(r.status) }
		],
		filters: [{ key: 'status', label: 'Setup', get: r => r.status }],
		rowActions: r => {
			const actions = [
				{ label: 'View / manage programs', run: () => openSchoolModal(r.school_id) }
			];
			if (!r.executives || r.executives.length === 0) {
				actions.push({
					label: 'Add executive director user',
					run: () => openUserModalWithPrefill({ role: 'executive', schoolId: r.school_id })
				});
			}
			actions.push(
				{ label: 'Edit school name', run: () => editSchool(r) },
				{ label: 'Add program', run: () => openProgramModal(r.school_id) },
				{ label: 'Delete school', danger: true, run: () => deleteSchools([r.school_id]) }
			);
			return actions;
		},
		bulkActions: [{ label: 'Delete selected', danger: true, run: ids => deleteSchools(ids) }]
	});
}


function buildUserTable() {
	return new DataTable({
		key: 'users',
		prefix: 'u_',
		mount: 'userTableMount',
		searchPlaceholder: 'Search name, email, given ID, school…',
		id: r => r.id,
		load: () => fetch('getusertable.php')
			.then(res => { if (!res.ok) throw new Error('HTTP ' + res.status); return res.json(); })
			.then(d => d.users.map(u => Object.assign({}, u, {
				name: (u.first_name + ' ' + u.last_name).trim(),
				roleLabel: ROLE_LABELS[u.role] || (u.role ? u.role.charAt(0).toUpperCase() + u.role.slice(1) : 'User'),
				statusText: u.archived ? 'Archived' : 'Active'
			}))),
		onChange: renderHome,
		onAdd: () => openUserModal(),
		addLabel: 'Add user',
		columns: [
			{ key: 'name', label: 'Full name', sticky: true, get: r => r.name, render: r => el('strong', null, r.name) },
			{ key: 'email', label: 'Email', get: r => r.email, render: r => el('span', 'muted', r.email) },
			{ key: 'given_id', label: 'Given ID', get: r => r.given_id || '' },
			{ key: 'role', label: 'Role', get: r => r.roleLabel, render: r => el('span', 'role-pill role-' + r.role, r.roleLabel) },
			{ key: 'school', label: 'School', get: r => r.school || '', render: r => r.school || mutedText('—') },
			{ key: 'program', label: 'Program', get: r => r.program || '', render: r => r.program || mutedText('—') },
			{ key: 'status', label: 'Status', get: r => r.statusText, render: r => statusBadge(r.statusText) },
			{
				key: 'created', label: 'Date created', get: r => fmtDate(r.created),
				sortVal: r => Date.parse(String(r.created || '').replace(' ', 'T')) || 0,
				render: r => el('span', 'muted small', fmtDate(r.created))
			}
		],
		filters: [
			{ key: 'role', label: 'Roles', get: r => r.roleLabel },
			{ key: 'status', label: 'Statuses', get: r => r.statusText },
			{ key: 'school', label: 'Schools', get: r => r.school || 'No school' }
		],
		rowActions: r => [
			{ label: 'View details', run: () => openUserDetails(r) },
			{ label: 'Edit', run: () => editUser(r) },
			r.archived
				? { label: 'Restore', run: () => userAction('restore_users', [r.id], 'Restore ' + r.name + '?') }
				: { label: 'Archive', run: () => userAction('archive_users', [r.id], 'Archive ' + r.name + '?') },
			{ label: 'Delete', danger: true, run: () => userAction('delete_users', [r.id], 'Permanently delete ' + r.name + '? This cannot be undone.') }
		],
		bulkActions: [
			{ label: 'Archive', run: ids => userAction('archive_users', ids, 'Archive ' + ids.length + ' user(s)?') },
			{ label: 'Restore', run: ids => userAction('restore_users', ids, 'Restore ' + ids.length + ' user(s)?') },
			{ label: 'Delete', danger: true, run: ids => userAction('delete_users', ids, 'Permanently delete ' + ids.length + ' user(s)? This cannot be undone.') }
		]
	});
}


// =========================================================================
// Masterlist front page (dashboard)
// =========================================================================

function renderHome() {
	if (!schoolTable || !userTable) return;
	const S = schoolTable, U = userTable;
	const txt = (id, v) => { const n = document.getElementById(id); if (n) n.textContent = v; };
	const sOk = S.status === 'ready', uOk = U.status === 'ready';

	txt('statSchools', sOk ? S.rows.length : '—');
	txt('statPrograms', sOk ? S.rows.reduce((a, s) => a + s.program_count, 0) : '—');
	txt('statUsers', uOk ? U.rows.length : '—');
	txt('statUsersSub', uOk
		? U.rows.filter(u => !u.archived).length + ' active, ' + U.rows.filter(u => u.archived).length + ' archived'
		: (U.status === 'error' ? 'Could not load' : 'Loading…'));
	const needs = sOk ? S.rows.filter(s => s.status !== 'Ready').length : 0;
	txt('statAttention', sOk ? needs : '—');
	txt('statAttentionSub', sOk ? (needs ? 'Schools missing programs or an executive' : 'Every school is set up') : (S.status === 'error' ? 'Could not load' : 'Loading…'));

	const hs = document.getElementById('homeSchools');
	hs.replaceChildren();
	if (!sOk) hs.appendChild(el('p', 'empty-state', S.status === 'error' ? 'Could not load schools.' : 'Loading…'));
	else if (!S.rows.length) hs.appendChild(el('p', 'empty-state', 'No schools yet. Use “Add school” to create one.'));
	else {
		S.rows.slice().sort((a, b) => b.program_count - a.program_count).slice(0, 5).forEach(s => {
			const row = el('div', 'mini-row');
			const left = el('div');
			left.append(
				el('div', 'mini-title', s.school_name),
				el('div', 'mini-sub', s.executives.length ? 'Executive: ' + s.executives.join(', ') : 'No executive assigned')
			);
			row.append(left, el('span', 'chip', s.program_count + (s.program_count === 1 ? ' program' : ' programs')));
			hs.appendChild(row);
		});
	}

	const hu = document.getElementById('homeUsers');
	hu.replaceChildren();
	if (!uOk) hu.appendChild(el('p', 'empty-state', U.status === 'error' ? 'Could not load users.' : 'Loading…'));
	else if (!U.rows.length) hu.appendChild(el('p', 'empty-state', 'No users yet. Use “Add user” to create one.'));
	else {
		U.rows.slice().sort((a, b) => Number(b.id) - Number(a.id)).slice(0, 5).forEach(u => {
			const row = el('div', 'mini-row');
			const left = el('div');
			left.append(el('div', 'mini-title', u.name), el('div', 'mini-sub', u.email + ' · ' + fmtDate(u.created)));
			row.append(left, el('span', 'role-pill role-' + u.role, u.roleLabel));
			hu.appendChild(row);
		});
	}

	const rb = document.getElementById('roleBreakdown');
	rb.replaceChildren();
	if (uOk && U.rows.length) {
		Object.keys(ROLE_LABELS).forEach(role => {
			const n = U.rows.filter(u => u.role === role).length;
			const pct = Math.round((n / U.rows.length) * 100);
			const line = el('div', 'bar-line');
			const head = el('div', 'bar-head');
			head.append(el('span', null, ROLE_LABELS[role]), el('span', 'muted', n + ' (' + pct + '%)'));
			const bg = el('div', 'progress-bar-bg');
			const fill = el('div', 'progress-bar-fill');
			fill.style.width = pct + '%';
			bg.appendChild(fill);
			line.append(head, bg);
			rb.appendChild(line);
		});
	} else {
		rb.appendChild(el('p', 'empty-state', uOk ? 'No users yet.' : 'Loading…'));
	}
}

function showMasterlistView(view) {
	if (!['home', 'schools', 'users', 'bulk-add'].includes(view)) view = 'home';
	document.querySelectorAll('.ml-view').forEach(v => v.classList.toggle('active', v.id === 'ml-' + view));
	if (view === 'bulk-add') initBulkAddPage();
	setParams({ view: view === 'home' ? null : view });
	window.scrollTo(0, 0);
}

function openBulkAddModal(mode) {
	closeModal('addSchoolModal');
	closeModal('addProgramModal');
	closeModal('addUserModal');
	showMasterlistView('bulk-add');
	setBulkMode(mode);
}

// =========================================================================
// Schools & programs: actions & Add School optional programs
// =========================================================================

function addOptionalProgramInput() {
	const container = document.getElementById('optionalProgramsContainer');
	const row = el('div', 'optional-program-row');
	const input = document.createElement('input');
	input.type = 'text';
	input.name = 'optional_programs[]';
	input.placeholder = 'Program name (optional)';
	const removeBtn = makeBtn('Remove', 'btn-outline btn-sm', () => row.remove());
	row.append(input, removeBtn);
	container.appendChild(row);
	input.focus();
}

function resetOptionalProgramInputs() {
	const container = document.getElementById('optionalProgramsContainer');
	if (!container) return;
	container.innerHTML = `
		<label>Programs under this school (optional)</label>
		<div class="optional-program-row">
			<input type="text" name="optional_programs[]" placeholder="Program name (optional)">
			<button type="button" class="btn btn-outline btn-sm" onclick="addOptionalProgramInput()" title="Add another program">+</button>
		</div>
	`;
}

function editSchool(s) {
	formModal({
		title: 'Edit school',
		fields: [{ name: 'school_name', label: 'School name', required: true, value: s.school_name }],
		onSubmit: v => postText(HANDLER, { action: 'edit_school', school_id: s.school_id, school_name: v.school_name })
			.then(msg => { toast(msg, 'success'); return refreshAll(); })
	});
}

function deleteSchools(ids) {
	const rows = schoolTable.rows.filter(r => ids.includes(r.school_id));
	const programs = rows.reduce((a, r) => a + r.program_count, 0);
	const label = rows.length === 1 ? '“' + rows[0].school_name + '”' : rows.length + ' schools';
	if (!confirm('Delete ' + label + (programs ? ' and its ' + programs + ' program(s)' : '') + '? This cannot be undone.')) return;

	postText(HANDLER, { action: 'delete_schools', ids })
		.then(msg => { toast(msg, 'success'); schoolTable.selected.clear(); return refreshAll(); })
		.catch(err => toast(err.message, 'error'));
}

function openDirectorInfoModal(dir, contextLabel, roleLabel, onReturn) {
	const fullUser = (userTable && userTable.rows)
		? userTable.rows.find(u => String(u.id) === String(dir.user_id))
		: null;

	const u = fullUser || {
		id: dir.user_id,
		first_name: dir.first_name || '',
		last_name: dir.last_name || '',
		name: dir.name || ((dir.first_name || '') + ' ' + (dir.last_name || '')).trim(),
		email: dir.email || '—',
		given_id: dir.given_id || '—',
		role: roleLabel === 'Executive Director' ? 'executive' : 'program',
		roleLabel: roleLabel || 'Program Director',
		school: contextLabel || '—',
		program: roleLabel === 'Program Director' ? (contextLabel || '—') : '—',
		statusText: 'Active',
		created: null
	};

	const dl = el('dl', 'detail-list');
	[
		['User ID', '#' + u.id],
		['Full name', u.name],
		['Email', u.email],
		['Given ID', u.given_id || '—'],
		['Role', u.roleLabel],
		['School', u.school || '—'],
		['Program', u.program || '—'],
		['Status', u.statusText],
		['Date created', fmtDate(u.created)]
	].forEach(([k, v]) => dl.append(el('dt', null, k), el('dd', null, v)));

	const actions = el('div', 'modal-actions');
	if (typeof onReturn === 'function') {
		actions.append(makeBtn('Back', 'btn-outline', onReturn));
	}
	actions.append(
		makeBtn('Edit user info', 'btn-primary', () => editUser(u, onReturn)),
		makeBtn('Close', 'btn-outline', () => closeModal('dynModal'))
	);
	const wrap = el('div');
	wrap.append(dl, actions);
	showDyn(u.name, wrap);
}

function buildProgramManageRow(opts) {
	const row = el('li', 'program-item program-manage-row');

	const leftCol = el('div', 'program-manage-main');
	const titleWrap = el('div', 'program-manage-title');
	const text = el('span', 'program-name-label', opts.programName);
	const input = document.createElement('input');
	input.type = 'text';
	input.value = opts.programName;
	input.className = 'program-edit-input';
	input.style.display = 'none';
	titleWrap.append(text, input);

	const dirWrap = el('div', 'program-manage-director');
	const dirs = opts.directorDetails || [];
	if (dirs.length > 0) {
		const lbl = el('span', 'muted small', 'Program Director: ');
		dirWrap.appendChild(lbl);
		dirs.forEach((d, idx) => {
			if (idx > 0) dirWrap.appendChild(document.createTextNode(', '));
			const linkBtn = document.createElement('button');
			linkBtn.type = 'button';
			linkBtn.className = 'director-link-btn';
			linkBtn.textContent = d.name;
			linkBtn.title = 'View / edit director info';
			linkBtn.addEventListener('click', () => opts.onViewDirector(d));
			dirWrap.appendChild(linkBtn);
		});
	} else {
		const noDirSpan = el('span', 'muted small', 'No Program Director ');
		const addDirBtn = makeBtn('+ Add program director user', 'btn-outline btn-xs', opts.onAddDirector);
		dirWrap.append(noDirSpan, addDirBtn);
	}

	leftCol.append(titleWrap, dirWrap);

	const actions = el('div', 'row-actions');
	let editBtn, saveBtn, cancelBtn, delBtn;
	function setEditing(editing) {
		text.style.display = editing ? 'none' : '';
		input.style.display = editing ? 'inline-block' : 'none';
		editBtn.style.display = editing ? 'none' : '';
		delBtn.style.display = editing ? 'none' : '';
		saveBtn.style.display = editing ? '' : 'none';
		cancelBtn.style.display = editing ? '' : 'none';
		if (editing) input.focus();
	}

	editBtn = makeBtn('Edit', 'btn-outline', () => setEditing(true));
	saveBtn = makeBtn('Save', 'btn-primary', () => {
		const newName = input.value.trim();
		if (newName === '') { toast('Name cannot be empty.', 'error'); return; }
		if (!confirm(opts.confirmSave)) return;
		opts.onSave(newName);
	});
	cancelBtn = makeBtn('Cancel', 'btn-outline', () => { input.value = opts.programName; setEditing(false); });
	delBtn = makeBtn('Delete', 'btn-danger-text', () => {
		if (!confirm(opts.confirmDelete)) return;
		opts.onDelete();
	});

	input.addEventListener('keydown', e => {
		if (e.key === 'Enter') { e.preventDefault(); saveBtn.click(); }
		if (e.key === 'Escape') { e.stopPropagation(); cancelBtn.click(); }
	});

	actions.append(editBtn, saveBtn, cancelBtn, delBtn);
	row.append(leftCol, actions);
	setEditing(false);
	return row;
}

function openSchoolModal(schoolId) {
	const s = schoolTable.rows.find(r => String(r.school_id) === String(schoolId));
	if (!s) return;

	const body = el('div');
	const meta = el('div', 'detail-meta');

	const execWrap = el('span');
	const execDetails = s.executive_details || [];
	if (execDetails.length > 0) {
		execWrap.appendChild(document.createTextNode('Executive: '));
		execDetails.forEach((ed, idx) => {
			if (idx > 0) execWrap.appendChild(document.createTextNode(', '));
			const linkBtn = document.createElement('button');
			linkBtn.type = 'button';
			linkBtn.className = 'director-link-btn';
			linkBtn.textContent = ed.name;
			linkBtn.title = 'View / edit executive director info';
			linkBtn.addEventListener('click', () => {
				openDirectorInfoModal(ed, s.school_name, 'Executive Director', () => openSchoolModal(schoolId));
			});
			execWrap.appendChild(linkBtn);
		});
	} else {
		execWrap.appendChild(document.createTextNode('Executive: not assigned '));
		const addExecBtn = makeBtn('+ Add executive director user', 'btn-outline btn-xs', () => {
			closeModal('dynModal');
			openUserModalWithPrefill({ role: 'executive', schoolId: s.school_id });
		});
		execWrap.appendChild(addExecBtn);
	}

	meta.append(
		execWrap,
		statusBadge(s.status)
	);
	body.appendChild(meta);

	const afterChange = msg => {
		toast(msg, 'success');
		return schoolTable.reload(true).then(() => { loadProgramFormSchools(); openSchoolModal(schoolId); });
	};

	if (s.programs.length) {
		const ul = el('ul', 'program-list program-manage-list');
		s.programs.forEach(p => ul.appendChild(buildProgramManageRow({
			programName: p.program_name,
			directorDetails: p.director_details || [],
			confirmSave: 'Save changes to this program?',
			confirmDelete: 'Delete this program?',
			onViewDirector: dir => {
				openDirectorInfoModal(dir, s.school_name + ' — ' + p.program_name, 'Program Director', () => openSchoolModal(schoolId));
			},
			onAddDirector: () => {
				closeModal('dynModal');
				openUserModalWithPrefill({
					role: 'program',
					schoolId: s.school_id,
					programId: p.program_id,
					programName: p.program_name
				});
			},
			onSave: name => postText(HANDLER, { action: 'edit_program', program_id: p.program_id, program_name: name })
				.then(afterChange).catch(err => toast(err.message, 'error')),
			onDelete: () => postText(HANDLER, { action: 'delete_program', program_id: p.program_id })
				.then(afterChange).catch(err => toast(err.message, 'error'))
		})));
		body.appendChild(ul);
	} else {
		body.appendChild(el('p', 'empty-state', 'This school has no programs yet.'));
	}

	const actions = el('div', 'modal-actions');
	actions.append(
		makeBtn('Edit school name', 'btn-outline', () => editSchool(s)),
		makeBtn('Add program', 'btn-primary', () => { closeModal('dynModal'); openProgramModal(s.school_id); })
	);
	body.appendChild(actions);
	showDyn(s.school_name, body);
}

function initAddForms() {
	const schoolForm = document.getElementById('schoolForm');
	if (schoolForm) {
		schoolForm.addEventListener('submit', function (e) {
			e.preventDefault();
			const fd = new FormData(this);
			const openUserAfter = document.getElementById('openUserAfterSchool').checked;
			const createdSchoolName = String(fd.get('school_name') || '').trim();

			postText(HANDLER, fd)
				.then(async msg => {
					toast(msg, 'success');
					schoolForm.reset();
					resetOptionalProgramInputs();
					closeModal('addSchoolModal');
					await refreshAll();

					if (openUserAfter) {
						await openUserModalWithPrefill({
							role: 'executive',
							schoolName: createdSchoolName
						});
					}
				})
				.catch(err => toast(err.message, 'error'));
		});
	}

	const programForm = document.getElementById('programForm');
	if (programForm) {
		programForm.addEventListener('submit', function (e) {
			e.preventDefault();
			const fd = new FormData(this);
			const openUserAfter = document.getElementById('openUserAfterProgram').checked;
			const targetSchoolId = String(fd.get('school_id') || '');
			const createdProgramName = String(fd.get('program_name') || '').trim();

			postText(HANDLER, fd)
				.then(async msg => {
					toast(msg, 'success');
					programForm.reset();
					closeModal('addProgramModal');
					await refreshAll();

					if (openUserAfter) {
						await openUserModalWithPrefill({
							role: 'program',
							schoolId: targetSchoolId,
							programName: createdProgramName
						});
					}
				})
				.catch(err => toast(err.message, 'error'));
		});
	}
}

async function openUserModalWithPrefill(opts) {
	openModal('addUserModal');
	const roleSel = document.getElementById('user_role');
	if (roleSel && opts.role) {
		roleSel.value = opts.role;
	}
	await updateDropdowns();

	const schoolSel = document.getElementById('school_id');
	if (schoolSel) {
		if (opts.schoolId) {
			schoolSel.value = opts.schoolId;
		} else if (opts.schoolName) {
			const match = [...schoolSel.options].find(o => o.textContent.trim().toLowerCase() === opts.schoolName.toLowerCase());
			if (match) schoolSel.value = match.value;
		}

		if (opts.role === 'program' && schoolSel.value) {
			await loadUserFormPrograms();
			const progSel = document.getElementById('program_id');
			if (progSel) {
				if (opts.programId) {
					progSel.value = String(opts.programId);
				} else if (opts.programName) {
					const pMatch = [...progSel.options].find(o => o.textContent.trim().toLowerCase() === opts.programName.toLowerCase());
					if (pMatch) progSel.value = pMatch.value;
				}
			}
		}
	}
}

function openProgramModal(schoolId) {
	openModal('addProgramModal');
	loadProgramFormSchools().then(() => {
		if (schoolId) document.getElementById('schoolDropdown').value = schoolId;
	});
}

function loadProgramFormSchools() {
	const dropdown = document.getElementById('schoolDropdown');
	if (!dropdown) return Promise.resolve();

	return postJson(HANDLER, { action: 'get_schools' })
		.then(data => {
			const previous = dropdown.value;
			dropdown.innerHTML = '<option value="">Select School</option>';
			data.forEach(school => {
				const option = document.createElement('option');
				option.value = school.id;
				option.textContent = school.school_name;
				dropdown.appendChild(option);
			});
			if (previous) dropdown.value = previous;
		})
		.catch(() => {
			dropdown.innerHTML = '<option value="">Could not load schools</option>';
		});
}

// =========================================================================
// Users: actions & add-user form
// =========================================================================

function userAction(action, ids, confirmMsg) {
	if (!confirm(confirmMsg)) return;
	return postText(USER_HANDLER, { action, ids })
		.then(msg => { toast(msg, 'success'); userTable.selected.clear(); return refreshAll(); })
		.catch(err => toast(err.message, 'error'));
}

function editUser(u, onAfterSave) {
	formModal({
		title: 'Edit user',
		fields: [
			{ name: 'first_name', label: 'First name', required: true, value: u.first_name },
			{ name: 'last_name', label: 'Last name', required: true, value: u.last_name },
			{ name: 'email', label: 'Email', required: true, value: u.email, type: 'email' }
		],
		onSubmit: v => postText(USER_HANDLER, Object.assign({ action: 'edit_user', user_id: u.id }, v))
			.then(async msg => {
				toast(msg, 'success');
				await refreshAll();
				if (typeof onAfterSave === 'function') {
					onAfterSave();
				}
			})
	});
}

function openUserDetails(u) {
	const dl = el('dl', 'detail-list');
	[
		['User ID', '#' + u.id], ['Full name', u.name], ['Email', u.email], ['Given ID', u.given_id || '—'],
		['Role', u.roleLabel], ['School', u.school || '—'], ['Program', u.program || '—'],
		['Status', u.statusText], ['Date created', fmtDate(u.created)]
	].forEach(([k, v]) => dl.append(el('dt', null, k), el('dd', null, v)));

	const actions = el('div', 'modal-actions');
	actions.append(
		makeBtn('Edit', 'btn-outline', () => editUser(u)),
		makeBtn('Close', 'btn-primary', () => closeModal('dynModal'))
	);
	const wrap = el('div');
	wrap.append(dl, actions);
	showDyn(u.name, wrap);
}

function handleFormSubmit(event) {
	event.preventDefault();
	const form = event.target;
	const formData = new FormData(form);

	fetch('createuser.php', { method: 'POST', body: formData })
		.then(response => response.json().then(data => ({ ok: response.ok, data })))
		.then(({ ok, data }) => {
			if (!ok || data.error) {
				console.error('Error creating user:', data.error);
				toast('Could not create user: ' + (data.error || 'Unknown error'), 'error');
				return;
			}
			form.reset();
			updateDropdowns();
			closeModal('addUserModal');
			refreshAll();
			toast(data.message || 'User created successfully.', 'success');
		})
		.catch(err => {
			console.error('Network or parsing error:', err);
			toast('Could not reach the server. Please try again.', 'error');
		});
}

function currentRole() {
	const el = document.getElementById('user_role');
	return el ? el.value : '';
}

function roleNeedsSchool() {
	const r = currentRole();
	return r === 'executive' || r === 'program';
}

function openUserModal() {
	openModal('addUserModal');
	updateDropdowns();
}

function updateDropdowns() {
	const role = currentRole();
	const needsSchool = role === 'executive' || role === 'program';
	const needsProgram = role === 'program';

	const schoolBox = document.getElementById('schoolDropdownContainer');
	const programBox = document.getElementById('programDropdownContainer');
	const schoolSel = document.getElementById('school_id');
	const programSel = document.getElementById('program_id');

	schoolBox.style.display = needsSchool ? 'block' : 'none';
	schoolSel.disabled = !needsSchool;
	schoolSel.required = needsSchool;

	programBox.style.display = needsProgram ? 'block' : 'none';
	programSel.disabled = !needsProgram;
	programSel.required = needsProgram;
	programSel.innerHTML = '<option value="">Select a school first</option>';

	if (needsSchool) return loadUserFormSchools();
	return Promise.resolve();
}

function loadUserFormSchools() {
	return fetch('createuser.php?action=load_schools')
		.then(res => res.json())
		.then(schools => {
			const select = document.getElementById('school_id');
			const previous = select.value;
			select.innerHTML = '<option value="">Select a School</option>';
			schools.forEach(school => {
				const option = document.createElement('option');
				option.value = school.school_id;
				option.textContent = school.school_name;
				select.appendChild(option);
			});
			if (previous) select.value = previous;
		})
		.catch(err => console.error('Could not load schools:', err));
}

function loadUserFormPrograms() {
	if (currentRole() !== 'program') return Promise.resolve();
	const schoolId = document.getElementById('school_id').value;
	const programSel = document.getElementById('program_id');

	if (!schoolId) {
		programSel.innerHTML = '<option value="">Select a school first</option>';
		return Promise.resolve();
	}

	return fetch('createuser.php?action=load_programs&school_id=' + encodeURIComponent(schoolId))
		.then(res => res.json())
		.then(programs => {
			programSel.innerHTML = '';
			if (programs.length === 0) {
				programSel.innerHTML = '<option value="">No programs for this school</option>';
				return;
			}
			programSel.innerHTML = '<option value="">Select a Program</option>';
			programs.forEach(program => {
				const option = document.createElement('option');
				option.value = program.program_id;
				option.textContent = program.program_name;
				programSel.appendChild(option);
			});
		})
		.catch(err => console.error('Could not load programs:', err));
}

// =========================================================================
// Bulk Add Feature Implementation
// =========================================================================

let bulkMode = 'schools'; // 'schools' or 'users'
let bulkRows = [];
let availableSchoolsData = []; // [{ school_id, school_name, programs: [{ program_id, program_name }] }]
let isTextEditorView = false;

function getBulkHeaderLine() {
	return getBulkColumns().map(c => c.label.replace(' *', '')).join(', ');
}

function setBulkMode(mode) {
	bulkMode = mode;
	document.getElementById('bulkModeSchools').className = 'btn btn-sm ' + (mode === 'schools' ? 'btn-primary' : 'btn-outline');
	document.getElementById('bulkModeUsers').className = 'btn btn-sm ' + (mode === 'users' ? 'btn-primary' : 'btn-outline');
	document.getElementById('bulkTitle').textContent = mode === 'schools' ? 'Bulk Add Schools & Programs' : 'Bulk Add Users';

	// Reset inputs, text editor, and file picker completely when switching modes
	const fileInput = document.getElementById('bulkFileInput');
	if (fileInput) fileInput.value = '';
	const textarea = document.getElementById('bulkTextarea');
	if (textarea) textarea.value = isTextEditorView ? getBulkHeaderLine() + '\n' : '';

	const headerHint = document.getElementById('bulkHeaderHint');
	if (headerHint) {
		headerHint.textContent = 'Column order: ' + getBulkHeaderLine();
	}

	postJson(HANDLER, { action: 'get_all_data' }).then(data => {
		availableSchoolsData = Array.isArray(data) ? data : [];
		resetBulkTable();
	}).catch(() => {
		availableSchoolsData = [];
		resetBulkTable();
	});
}

function initBulkAddPage() {
	setBulkMode(bulkMode);
}

function getBulkColumns() {
	if (bulkMode === 'schools') {
		return [
			{ key: 'school_name', label: 'School Name *', type: 'writable_school_dropdown', required: true },
			{ key: 'program_name', label: 'Program Name', type: 'text', required: false }
		];
	} else {
		return [
			{ key: 'first_name', label: 'First Name *', type: 'text', required: true },
			{ key: 'last_name', label: 'Last Name *', type: 'text', required: true },
			{ key: 'email', label: 'Email *', type: 'email', required: true },
			{ key: 'given_id', label: 'Given ID *', type: 'text', required: true },
			{ key: 'role', label: 'Role *', type: 'strict_dropdown', options: ['officer', 'program', 'executive'], required: true },
			{ key: 'school_name', label: 'School *', type: 'writable_school_dropdown', required: true },
			{ key: 'program_name', label: 'Program *', type: 'writable_program_dropdown', required: true }
		];
	}
}

function getEmptyBulkRow() {
	const cols = getBulkColumns();
	const r = {};
	cols.forEach(c => {
		r[c.key] = '';
	});
	return r;
}

function isRowEmpty(r) {
	if (!r || typeof r !== 'object') return true;
	const cols = getBulkColumns();
	return !cols.some(c => {
		const val = r[c.key];
		return val !== undefined && val !== null && String(val).trim() !== '' && val !== 'n/a';
	});
}

function resetBulkTable() {
	bulkRows = [getEmptyBulkRow()];
	const textarea = document.getElementById('bulkTextarea');
	if (textarea) textarea.value = isTextEditorView ? getBulkHeaderLine() + '\n' : '';
	renderBulkTable();
}

function clearBulkRow(index) {
	if (bulkRows.length > 1) {
		bulkRows.splice(index, 1);
	} else {
		bulkRows[0] = getEmptyBulkRow();
	}
	pruneAndEnsureTrailingEmptyRow();
	renderBulkTable();
}

function pruneAndEnsureTrailingEmptyRow() {
	// Remove any completely blank rows except the final trailing blank row
	const activeEl = document.activeElement;
	let focusColIdx = -1;
	let focusRowIdx = -1;
	if (activeEl && activeEl.classList.contains('bulk-cell-input')) {
		const tr = activeEl.closest('tr');
		const td = activeEl.closest('td');
		if (tr && td) {
			focusRowIdx = parseInt(tr.dataset.rowIndex, 10);
			focusColIdx = [...tr.children].indexOf(td);
		}
	}

	let removedBeforeFocus = false;
	for (let i = bulkRows.length - 2; i >= 0; i--) {
		if (isRowEmpty(bulkRows[i])) {
			bulkRows.splice(i, 1);
			if (i <= focusRowIdx) removedBeforeFocus = true;
		}
	}

	if (bulkRows.length === 0 || !isRowEmpty(bulkRows[bulkRows.length - 1])) {
		bulkRows.push(getEmptyBulkRow());
	}

	if (removedBeforeFocus) {
		renderBulkTable();
		const tbody = document.getElementById('bulkTbody');
		if (tbody && tbody.children.length > 0) {
			const targetRowIdx = Math.min(focusRowIdx, tbody.children.length - 1);
			const targetTr = tbody.children[targetRowIdx];
			if (targetTr && targetTr.children[focusColIdx]) {
				const targetInp = targetTr.children[focusColIdx].querySelector('.bulk-cell-input');
				if (targetInp) targetInp.focus();
			}
		}
		return true;
	}
	return false;
}

function isCellNa(row, colKey) {
	if (bulkMode !== 'users') return false;
	const role = (row['role'] || '').trim().toLowerCase();
	if (colKey === 'school_name' && role === 'officer') return true;
	if (colKey === 'program_name' && (role === 'officer' || role === 'executive')) return true;
	return false;
}

function handleBulkCellChange(rIdx, tr) {
	const didReRender = pruneAndEnsureTrailingEmptyRow();
	if (!didReRender) {
		checkRowValidity(rIdx, tr);
		// Sync DOM row indices and append new blank last row if needed
		const tbody = document.getElementById('bulkTbody');
		if (tbody) {
			while (tbody.children.length < bulkRows.length) {
				const newIdx = tbody.children.length;
				tbody.appendChild(buildBulkRowElement(bulkRows[newIdx], newIdx, getBulkColumns()));
			}
			[...tbody.children].forEach((rowEl, idx) => {
				rowEl.dataset.rowIndex = String(idx);
			});
		}
	}
	updateBulkStatus();
}

function buildBulkRowElement(row, rIdx, cols) {
	const tr = document.createElement('tr');
	tr.className = 'bulk-row';
	tr.dataset.rowIndex = String(rIdx);

	// Selection checkbox
	const tdCheck = document.createElement('td');
	tdCheck.style.textAlign = 'center';
	const cb = document.createElement('input');
	cb.type = 'checkbox';
	cb.className = 'bulk-row-cb';
	cb.setAttribute('aria-label', 'Select row ' + (rIdx + 1));
	cb.addEventListener('change', () => tr.classList.toggle('bulk-row-selected', cb.checked));
	tdCheck.appendChild(cb);
	tr.appendChild(tdCheck);

	cols.forEach(c => {
		const td = document.createElement('td');
		const na = isCellNa(row, c.key);

		if (na) {
			td.className = 'bulk-cell-na';
			td.textContent = 'n/a';
			row[c.key] = 'n/a';
		} else {
			// Clear stale 'n/a' when field becomes editable
			if (row[c.key] === 'n/a') {
				row[c.key] = '';
			}
			const val = row[c.key] || '';

			if (c.type === 'writable_school_dropdown') {
				const wrap = el('div', 'bulk-cell-wrap');
				const input = document.createElement('input');
				input.type = 'text';
				input.className = 'bulk-cell-input';
				input.value = val;
				input.placeholder = 'Select or type school...';
				const listId = 'dl_school_' + rIdx + '_' + Math.random().toString(36).slice(2, 6);
				input.setAttribute('list', listId);

				const datalist = document.createElement('datalist');
				datalist.id = listId;
				availableSchoolsData.forEach(s => {
					const opt = document.createElement('option');
					opt.value = s.school_name;
					datalist.appendChild(opt);
				});

				const badge = el('span', 'cell-note-new', 'NEW');
				const updateNewBadge = v => {
					const trimmed = v.trim();
					const exists = availableSchoolsData.some(s => s.school_name.toLowerCase() === trimmed.toLowerCase());
					badge.style.display = (trimmed !== '' && !exists) ? 'inline-block' : 'none';
				};
				updateNewBadge(val);

				input.addEventListener('input', e => {
					row[c.key] = e.target.value;
					updateNewBadge(e.target.value);
					if (bulkMode === 'users') {
						updateRowProgramDatalist(tr, row);
					}
					handleBulkCellChange(parseInt(tr.dataset.rowIndex, 10), tr);
				});

				wrap.append(input, datalist, badge);
				td.appendChild(wrap);

			} else if (c.type === 'writable_program_dropdown') {
				const wrap = el('div', 'bulk-cell-wrap');
				const input = document.createElement('input');
				input.type = 'text';
				input.className = 'bulk-cell-input';
				input.dataset.colKey = 'program_name';
				input.value = val;
				input.placeholder = 'Select or type program...';
				const listId = 'dl_prog_' + rIdx + '_' + Math.random().toString(36).slice(2, 6);
				input.setAttribute('list', listId);

				const datalist = document.createElement('datalist');
				datalist.id = listId;
				populateProgramDatalist(datalist, row['school_name']);

				input.addEventListener('input', e => {
					row[c.key] = e.target.value;
					handleBulkCellChange(parseInt(tr.dataset.rowIndex, 10), tr);
				});

				wrap.append(input, datalist);
				td.appendChild(wrap);

			} else if (c.type === 'strict_dropdown') {
				const wrap = el('div', 'bulk-cell-wrap');
				const input = document.createElement('input');
				input.type = 'text';
				input.className = 'bulk-cell-input';
				input.value = val;
				input.placeholder = 'Select or type role...';
				const listId = 'dl_role_' + rIdx + '_' + Math.random().toString(36).slice(2, 6);
				input.setAttribute('list', listId);

				const datalist = document.createElement('datalist');
				datalist.id = listId;
				c.options.forEach(optVal => {
					const opt = document.createElement('option');
					opt.value = optVal;
					datalist.appendChild(opt);
				});

				input.addEventListener('input', e => {
					const prevRole = (row[c.key] || '').trim().toLowerCase();
					const newRole = e.target.value.trim().toLowerCase();
					row[c.key] = newRole;

					const prevSchoolNa = prevRole === 'officer';
					const newSchoolNa = newRole === 'officer';
					const prevProgNa = prevRole === 'officer' || prevRole === 'executive';
					const newProgNa = newRole === 'officer' || newRole === 'executive';

					if (prevSchoolNa !== newSchoolNa || prevProgNa !== newProgNa) {
						pruneAndEnsureTrailingEmptyRow();
						renderBulkTable();
						// Restore focus back to the role input of the current row
						const tbody = document.getElementById('bulkTbody');
						const currentIdx = Math.min(parseInt(tr.dataset.rowIndex, 10), tbody.children.length - 1);
						const targetTr = tbody.children[currentIdx];
						if (targetTr) {
							const roleColIdx = cols.findIndex(col => col.key === 'role') + 1;
							const roleInp = targetTr.children[roleColIdx]?.querySelector('.bulk-cell-input');
							if (roleInp) {
								roleInp.focus();
								roleInp.setSelectionRange(roleInp.value.length, roleInp.value.length);
							}
						}
					} else {
						handleBulkCellChange(parseInt(tr.dataset.rowIndex, 10), tr);
					}
				});

				wrap.append(input, datalist);
				td.appendChild(wrap);

			} else {
				const input = document.createElement('input');
				input.type = c.type === 'email' ? 'email' : 'text';
				input.className = 'bulk-cell-input';
				input.value = val;
				input.placeholder = c.required ? 'Required' : 'Optional';
				input.addEventListener('input', e => {
					row[c.key] = e.target.value;
					handleBulkCellChange(parseInt(tr.dataset.rowIndex, 10), tr);
				});
				td.appendChild(input);
			}
		}
		tr.appendChild(td);
	});

	// Clear row button
	const tdAct = document.createElement('td');
	tdAct.style.textAlign = 'center';
	const clearBtn = makeBtn('Clear', 'btn-outline btn-sm', () => clearBulkRow(parseInt(tr.dataset.rowIndex, 10)));
	clearBtn.title = 'Clear this row';
	tdAct.appendChild(clearBtn);
	tr.appendChild(tdAct);

	checkRowValidity(rIdx, tr);
	return tr;
}

function populateProgramDatalist(datalist, schoolName) {
	datalist.innerHTML = '';
	if (!schoolName) return;
	const schoolObj = availableSchoolsData.find(s => s.school_name.toLowerCase() === String(schoolName).trim().toLowerCase());
	if (schoolObj && Array.isArray(schoolObj.programs)) {
		schoolObj.programs.forEach(p => {
			const opt = document.createElement('option');
			opt.value = p.program_name;
			datalist.appendChild(opt);
		});
	}
}

function updateRowProgramDatalist(tr, row) {
	const progInput = tr.querySelector('input[data-col-key="program_name"]');
	if (!progInput) return;
	const listId = progInput.getAttribute('list');
	const datalist = listId ? document.getElementById(listId) : null;
	if (datalist) populateProgramDatalist(datalist, row['school_name']);
}

function renderBulkTable() {
	const theadRow = document.getElementById('bulkTheadRow');
	const tbody = document.getElementById('bulkTbody');
	if (!theadRow || !tbody) return;
	const cols = getBulkColumns();

	// Header
	theadRow.innerHTML = '';
	const thCheck = document.createElement('th');
	thCheck.style.width = '42px';
	thCheck.style.textAlign = 'center';
	thCheck.textContent = '#';
	theadRow.appendChild(thCheck);

	cols.forEach(c => {
		const th = document.createElement('th');
		th.textContent = c.label;
		theadRow.appendChild(th);
	});

	const thAct = document.createElement('th');
	thAct.style.width = '76px';
	thAct.style.textAlign = 'center';
	thAct.textContent = 'Action';
	theadRow.appendChild(thAct);

	// Body
	tbody.innerHTML = '';
	bulkRows.forEach((row, rIdx) => {
		tbody.appendChild(buildBulkRowElement(row, rIdx, cols));
	});

	updateBulkStatus();
}

function validateSingleRow(row) {
	if (isRowEmpty(row)) return { empty: true, valid: true, errors: {} };
	const cols = getBulkColumns();
	const errors = {};
	let valid = true;

	cols.forEach(c => {
		if (isCellNa(row, c.key)) return;
		const val = String(row[c.key] ?? '').trim();

		if (c.required && val === '') {
			valid = false;
			errors[c.key] = true;
		}
		if (c.type === 'email' && val !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
			valid = false;
			errors[c.key] = true;
		}
		if (c.type === 'strict_dropdown' && !c.options.includes(val.toLowerCase())) {
			valid = false;
			errors[c.key] = true;
		}
	});

	return { empty: false, valid, errors };
}

function checkRowValidity(rIdx, tr) {
	const row = bulkRows[rIdx];
	if (!row || !tr) return;
	const res = validateSingleRow(row);
	const cols = getBulkColumns();

	const tds = [...tr.children].slice(1, 1 + cols.length);
	tds.forEach((td, idx) => {
		const col = cols[idx];
		const inp = td.querySelector('.bulk-cell-input');
		if (!inp) return;
		if (!res.empty && res.errors[col.key]) {
			inp.classList.add('bulk-cell-invalid');
		} else {
			inp.classList.remove('bulk-cell-invalid');
		}
	});
}

function updateBulkStatus() {
	const nonEmpty = bulkRows.filter(r => !isRowEmpty(r));
	const invalidCount = nonEmpty.filter(r => !validateSingleRow(r).valid).length;
	const msgEl = document.getElementById('bulkStatusMsg');
	if (!msgEl) return;

	if (nonEmpty.length === 0) {
		msgEl.textContent = '0 rows entered';
	} else if (invalidCount > 0) {
		msgEl.textContent = `${nonEmpty.length} row(s) entered (${invalidCount} row(s) have incomplete or invalid fields)`;
	} else {
		msgEl.textContent = `${nonEmpty.length} valid row(s) ready to save`;
	}
}

// Paste support (Excel tab-delimited or CSV)
document.addEventListener('paste', e => {
	const bulkView = document.getElementById('ml-bulk-add');
	if (!bulkView || !bulkView.classList.contains('active')) return;
	const activeEl = document.activeElement;
	if (!activeEl || !activeEl.classList.contains('bulk-cell-input')) return;

	const clipboardData = e.clipboardData || window.clipboardData;
	const pastedText = clipboardData ? clipboardData.getData('text') : '';
	if (!pastedText || (!pastedText.includes('\t') && !pastedText.includes('\n'))) return;

	e.preventDefault();
	const lines = pastedText.split(/\r?\n/).filter(l => l.trim() !== '');
	const startTr = activeEl.closest('tr');
	const startRowIdx = parseInt(startTr.dataset.rowIndex, 10);
	const cols = getBulkColumns();
	const startTd = activeEl.closest('td');
	const startColIdx = [...startTr.children].indexOf(startTd) - 1;

	lines.forEach((line, rOffset) => {
		const cells = line.split('\t');
		const targetRowIdx = startRowIdx + rOffset;
		while (targetRowIdx >= bulkRows.length) {
			bulkRows.push(getEmptyBulkRow());
		}
		cells.forEach((cellVal, cOffset) => {
			const targetColIdx = startColIdx + cOffset;
			if (targetColIdx >= 0 && targetColIdx < cols.length) {
				const colKey = cols[targetColIdx].key;
				if (!isCellNa(bulkRows[targetRowIdx], colKey)) {
					bulkRows[targetRowIdx][colKey] = cellVal.trim();
				}
			}
		});
	});

	pruneAndEnsureTrailingEmptyRow();
	renderBulkTable();
	toast(`Pasted ${lines.length} row(s) from clipboard.`, 'success');
});

// Delete key clears selected rows when not typing inside a cell
document.addEventListener('keydown', e => {
	if (e.key !== 'Delete' && e.key !== 'Backspace') return;
	const bulkView = document.getElementById('ml-bulk-add');
	if (!bulkView || !bulkView.classList.contains('active')) return;
	const activeEl = document.activeElement;
	if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) return;

	const selectedRows = document.querySelectorAll('.bulk-row-selected');
	if (selectedRows.length > 0) {
		e.preventDefault();
		const indices = [...selectedRows].map(tr => parseInt(tr.dataset.rowIndex, 10)).sort((a, b) => b - a);
		indices.forEach(idx => clearBulkRow(idx));
		toast('Cleared selected row(s).', 'success');
	}
});

// Text Editor View Toggle (Plaintext CSV without quotation marks, keeping header reference on line 1)
function toggleBulkTextEditor() {
	isTextEditorView = !isTextEditorView;
	const tableWrap = document.getElementById('bulkTableViewWrap');
	const textWrap = document.getElementById('bulkTextViewWrap');
	const btn = document.getElementById('bulkViewToggleBtn');

	if (isTextEditorView) {
		tableWrap.style.display = 'none';
		textWrap.style.display = 'block';
		btn.textContent = 'Switch to Table View';

		const cols = getBulkColumns();
		const headerLine = getBulkHeaderLine();
		const nonEmpty = bulkRows.filter(r => !isRowEmpty(r));
		const dataLines = nonEmpty.map(r => {
			return cols.map(c => {
				const v = r[c.key];
				return (v === undefined || v === null || v === 'n/a') ? '' : String(v).trim();
			}).join(', ');
		});
		document.getElementById('bulkTextarea').value = [headerLine, ...dataLines].join('\n');
	} else {
		syncBulkFromText();
		tableWrap.style.display = 'block';
		textWrap.style.display = 'none';
		btn.textContent = 'Text Editor (CSV)';
		renderBulkTable();
	}
}

function isHeaderRowLine(parts, cols) {
	if (parts.length < cols.length) return false;
	return cols.every((c, idx) => {
		const expected = c.label.replace(' *', '').trim().toLowerCase();
		const actual = (parts[idx] || '').replace(' *', '').trim().toLowerCase();
		return actual === expected;
	});
}

function syncBulkFromText() {
	const textarea = document.getElementById('bulkTextarea');
	if (!textarea) return;
	const text = textarea.value;
	const lines = text.split(/\r?\n/).filter(l => l.trim() !== '');
	const cols = getBulkColumns();

	const newRows = [];
	lines.forEach((line, lineIdx) => {
		const delimiter = line.includes('\t') ? '\t' : ',';
		const parts = line.split(delimiter).map(p => p.trim());
		// Skip the header reference line on line 0 if it matches the column headers
		if (lineIdx === 0 && isHeaderRowLine(parts, cols)) {
			return;
		}
		const r = getEmptyBulkRow();
		cols.forEach((c, idx) => {
			if (parts[idx] !== undefined && parts[idx] !== '') {
				r[c.key] = c.key === 'role' ? parts[idx].toLowerCase() : parts[idx];
			}
		});
		if (!isRowEmpty(r)) {
			newRows.push(r);
		}
	});
	newRows.push(getEmptyBulkRow());
	bulkRows = newRows;
	updateBulkStatus();
}

function handleBulkFileImport(e) {
	const file = e.target.files[0];
	if (!file) return;
	const reader = new FileReader();
	reader.onload = function (evt) {
		const content = evt.target.result;
		const lines = content.split(/\r?\n/).filter(l => l.trim() !== '');
		const cols = getBulkColumns();
		const headerLine = getBulkHeaderLine();

		// Ensure header reference is at top if in text editor view
		if (lines.length > 0) {
			const firstDelim = lines[0].includes('\t') ? '\t' : ',';
			const firstParts = lines[0].split(firstDelim).map(p => p.trim());
			if (!isHeaderRowLine(firstParts, cols)) {
				lines.unshift(headerLine);
			}
		} else {
			lines.push(headerLine);
		}

		document.getElementById('bulkTextarea').value = lines.join('\n');
		syncBulkFromText();
		if (!isTextEditorView) {
			renderBulkTable();
		}
		toast('File imported successfully.', 'success');
	};
	reader.readAsText(file);
}

function submitBulkData() {
	if (isTextEditorView) syncBulkFromText();

	const nonEmptyRows = bulkRows.filter(r => !isRowEmpty(r));
	if (nonEmptyRows.length === 0) {
		toast('Please enter at least one row before saving.', 'error');
		return;
	}

	// Strict validation: block entire batch if even one row is invalid or incomplete
	for (let i = 0; i < nonEmptyRows.length; i++) {
		const res = validateSingleRow(nonEmptyRows[i]);
		if (!res.valid) {
			if (!isTextEditorView) renderBulkTable();
			toast(`Row #${i + 1} has missing or invalid fields. Fix all highlighted cells before saving.`, 'error');
			return;
		}
	}

	postRawJson(BULK_HANDLER, { mode: bulkMode, rows: nonEmptyRows })
		.then(res => {
			toast(res.message || 'Bulk entries saved successfully.', 'success');
			resetBulkTable();
			refreshAll();
			showMasterlistView('home');
		})
		.catch(err => toast(err.message || 'Bulk submission failed.', 'error'));
}

// =========================================================================
// Layout: sidebar, navigation, profile menu
// =========================================================================

if (window.lucide) lucide.createIcons();

const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');
const profileTrigger = document.getElementById('profileTrigger');
const profilePanel = document.getElementById('profilePanel');
const profileMenuContainer = document.getElementById('profileMenu');
const navButtons = document.querySelectorAll('#mainNav .sidebar-item');

let isCollapsed = true;

function updateLayout(collapsedState) {
	isCollapsed = collapsedState;
	const isMobile = window.innerWidth < 768;

	if (isCollapsed) {
		sidebar.classList.add('sidebar-collapsed');
		sidebar.classList.remove('sidebar-expanded');
		mainContent.style.transform = 'translateX(0)';
	} else {
		sidebar.classList.add('sidebar-expanded');
		sidebar.classList.remove('sidebar-collapsed');
		mainContent.style.transform = isMobile ? 'translateX(256px)' : 'translateX(176px)';
	}
}

sidebarToggle.addEventListener('click', () => updateLayout(!isCollapsed));

sidebar.addEventListener('mouseleave', () => {
	if (!isCollapsed) updateLayout(true);
});

function showPage(pageId) {
	document.querySelectorAll('section').forEach(sec => sec.classList.remove('active'));
	const targetSection = document.getElementById(pageId);
	if (targetSection) targetSection.classList.add('active');

	navButtons.forEach(btn => {
		btn.classList.toggle('nav-active', btn.getAttribute('data-page') === pageId);
	});

	setParams({ tab: pageId === 'dashboard' ? null : pageId });
	if (window.innerWidth < 768) updateLayout(true);
}

navButtons.forEach(button => {
	button.addEventListener('click', () => showPage(button.getAttribute('data-page')));
});

profileTrigger.addEventListener('click', e => {
	e.stopPropagation();
	profilePanel.classList.toggle('show');
});

profileMenuContainer.addEventListener('mouseleave', () => profilePanel.classList.remove('show'));
document.addEventListener('click', () => profilePanel.classList.remove('show'));

function handleLogout() {
	window.location.replace('../login/login.php');
}

window.addEventListener('resize', () => updateLayout(isCollapsed));

// =========================================================================
// Init
// =========================================================================

document.addEventListener('DOMContentLoaded', function () {
	const roleSelect = document.getElementById('user_role');
	const schoolSelect = document.getElementById('school_id');
	if (roleSelect) roleSelect.addEventListener('change', updateDropdowns);
	if (schoolSelect) schoolSelect.addEventListener('change', loadUserFormPrograms);

	initAddForms();
	updateDropdowns();
	loadProgramFormSchools();

	const p = new URLSearchParams(location.search);
	const tab = p.get('tab');
	if (tab && document.getElementById(tab) && document.getElementById(tab).tagName === 'SECTION') showPage(tab);
	showMasterlistView(p.get('view') || 'home');

	schoolTable = buildSchoolTable();
	userTable = buildUserTable();
	renderHome();
	schoolTable.reload(true);
	userTable.reload(true);
});
