// =========================================================================
// Helpers  (el(), setParams(), DataTable live in datatable.js)
// =========================================================================

const API_INTERNS = 'api_interns.php';
const API_COMPANIES = 'api_companies.php';
const API_DEPTS = 'api_departments_supervisors.php';
const API_LOOKUPS = 'api_lookups.php';

// Every officer endpoint answers with JSON. Both helpers return the parsed
// body and throw a readable Error if the server sent something else.
function apiRequest(url, payload) {
	const opts = payload === undefined ? {} : {
		method: 'POST',
		headers: { 'Content-Type': 'application/json' },
		body: JSON.stringify(payload)
	};
	return fetch(url, opts).then(async res => {
		const text = await res.text();
		try {
			return JSON.parse(text);
		} catch (e) {
			console.error('Invalid JSON from server:', text);
			throw new Error(res.ok ? 'The server sent an unexpected response.' : 'HTTP ' + res.status);
		}
	});
}
const apiGet = url => apiRequest(url);
const apiPost = (url, payload) => apiRequest(url, payload);

// For endpoints that answer { success, message }.
function expectSuccess(data) {
	if (!data || data.success === false) throw new Error((data && data.message) || 'Request failed.');
	return data;
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

function fmtDay(s) {
	const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(s || ''));
	if (!m) return '—';
	const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
	return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
}

function mutedText(t) { return el('span', 'muted', t); }

function statusBadge(text) {
	return el('span', 'badge ' + (text === 'OK' ? 'badge-active' : 'badge-archived'), text || '—');
}

// Blank-safe number helpers for the companies table.
const numOrBlank = v => (v === null || v === undefined || v === '') ? '' : Number(v);
const numSort = v => (v === null || v === undefined || v === '') ? -Infinity : Number(v);

// =========================================================================
// Modals
// =========================================================================

const MODAL_PARTS = { dynModal: ['dynTitle', 'dynBody'], subModal: ['subTitle', 'subBody'] };

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

function showDyn(title, bodyNode, opts) {
	opts = opts || {};
	const id = opts.id || 'dynModal';
	const [titleId, bodyId] = MODAL_PARTS[id];
	document.getElementById(titleId).textContent = title;
	document.getElementById(bodyId).replaceChildren(bodyNode);
	document.getElementById(id).querySelector('.modal').classList.toggle('wide', !!opts.wide);
	openModal(id);
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
			'add-interns': () => openBulkAdd('interns'),
			'add-companies': () => openBulkAdd('companies')
		};
		if (acts[a.dataset.act]) acts[a.dataset.act]();
	}
});

document.addEventListener('keydown', e => {
	if (e.key !== 'Escape') return;
	const open = [...document.querySelectorAll('.modal-backdrop:not([hidden])')].pop();
	if (open) closeModal(open.id);
});

function setSelectOptions(select, options, value, placeholder) {
	select.replaceChildren();
	if (placeholder !== undefined) {
		const ph = document.createElement('option');
		ph.value = '';
		ph.textContent = placeholder;
		select.appendChild(ph);
	}
	options.forEach(o => {
		const opt = document.createElement('option');
		opt.value = String(o.value);
		opt.textContent = o.label;
		select.appendChild(opt);
	});
	select.value = value === undefined || value === null ? '' : String(value);
}

// Builds a form inside a dialog. Field types: text (default), email, date,
// number, select. Returns the <form> so callers can wire extra behaviour.
function formModal(opts) {
	const modalId = opts.modal || 'dynModal';
	const form = el('form', 'stacked-form flat' + (opts.columns === 2 ? ' two-col' : ''));

	opts.fields.forEach(f => {
		const wrap = el('div', 'fm-field' + (f.full ? ' fm-full' : ''));
		const label = el('label', null, f.label + (f.required ? ' *' : ''));
		const fieldId = 'fm_' + modalId + '_' + f.name;
		label.htmlFor = fieldId;

		let input;
		if (f.type === 'select') {
			input = document.createElement('select');
			setSelectOptions(input, f.options || [], f.value, f.placeholder || 'Select…');
		} else {
			input = document.createElement('input');
			input.type = f.type || 'text';
			input.value = f.value === null || f.value === undefined ? '' : f.value;
		}
		input.id = fieldId;
		input.name = f.name;
		if (f.required) input.required = true;
		if (f.attrs) Object.entries(f.attrs).forEach(([k, v]) => input.setAttribute(k, v));

		wrap.append(label, input);
		form.appendChild(wrap);
	});

	const actions = el('div', 'modal-actions');
	const cancel = makeBtn('Cancel', 'btn-outline', () => closeModal(modalId));
	const save = el('button', 'btn btn-primary', opts.submit || 'Save changes');
	save.type = 'submit';
	actions.append(cancel, save);
	form.appendChild(actions);

	form.addEventListener('submit', e => {
		e.preventDefault();
		save.disabled = true;
		opts.onSubmit(Object.fromEntries(new FormData(form)))
			.then(() => closeModal(modalId))
			.catch(err => toast(err.message, 'error'))
			.finally(() => { save.disabled = false; });
	});
	showDyn(opts.title, form, { id: modalId, wide: !!opts.wide });
	return form;
}

// =========================================================================
// Lookups (schools and programs, used by the edit dialog and bulk add)
// =========================================================================

let lookups = { schools: [], programs: [] };
let lookupsLoaded = false;

function loadLookups() {
	return Promise.all([
		apiGet(API_LOOKUPS + '?type=schools'),
		apiGet(API_LOOKUPS + '?type=programs')
	]).then(([schools, programs]) => {
		if (!Array.isArray(schools) || !Array.isArray(programs)) throw new Error('Could not load schools and programs.');
		lookups = { schools, programs };
		lookupsLoaded = true;
		fillSchoolList();
		return lookups;
	});
}

function programsOfSchool(schoolId) {
	return lookups.programs.filter(p => String(p.school_id) === String(schoolId));
}

function findSchoolByName(name) {
	const n = String(name || '').trim().toLowerCase();
	return lookups.schools.find(s => String(s.school_name).trim().toLowerCase() === n) || null;
}

function findProgram(schoolId, programName) {
	const n = String(programName || '').trim().toLowerCase();
	return programsOfSchool(schoolId).find(p => String(p.program_name).trim().toLowerCase() === n) || null;
}

function fillSchoolList() {
	const dl = document.getElementById('bulkSchoolList');
	if (!dl) return;
	dl.replaceChildren();
	lookups.schools.forEach(s => {
		const o = document.createElement('option');
		o.value = s.school_name;
		dl.appendChild(o);
	});
}

// =========================================================================
// Tables & shared state
// =========================================================================

let internTable, companyTable, supervisorTable;

function refreshAll() {
	return Promise.all([internTable.reload(true), companyTable.reload(true), supervisorTable.reload(true)]);
}

function splitName(full) {
	const parts = String(full || '').trim().split(/\s+/);
	return { first: parts.shift() || '', last: parts.join(' ') };
}

function buildInternTable() {
	return new DataTable({
		key: 'interns',
		prefix: 'i_',
		mount: 'internTableMount',
		searchPlaceholder: 'Search name, email, school user ID, school…',
		id: r => r.intern_id,
		load: () => apiGet(API_INTERNS).then(d => {
			if (!Array.isArray(d)) throw new Error((d && d.message) || 'Could not load interns.');
			return d;
		}),
		onChange: renderHome,
		onAdd: () => openBulkAdd('interns'),
		addLabel: 'Add interns',
		columns: [
			{ key: 'id', label: 'ID', num: true, get: r => Number(r.intern_id), render: r => el('span', 'mono', '#' + r.intern_id) },
			{ key: 'name', label: 'Full name', sticky: true, get: r => r.full_name || '', render: r => el('strong', null, r.full_name || '—') },
			{ key: 'given_id', label: 'School user ID', get: r => r.schooluser_given_id || '' },
			{ key: 'program', label: 'Program', get: r => r.program_name || '' },
			{ key: 'school', label: 'School', get: r => r.school_name || '' },
			{ key: 'email', label: 'Email', get: r => r.email || '', render: r => el('span', 'muted', r.email || '') },
			{
				key: 'birthdate', label: 'Birthdate', get: r => r.birthdate || '',
				sortVal: r => Date.parse(r.birthdate || '') || 0,
				render: r => r.birthdate ? el('span', 'muted small', fmtDay(r.birthdate)) : mutedText('—')
			},
			{ key: 'gender', label: 'Gender', get: r => r.gender || '' },
			{ key: 'city', label: 'City', get: r => r.city || '' },
			{ key: 'province', label: 'Province/State', get: r => r.province_or_state || '' },
			{ key: 'postal', label: 'Postal code', get: r => r.postal_code || '' },
			{ key: 'country', label: 'Country', get: r => r.country || '' },
			{ key: 'status', label: 'Account status', get: r => r.account_status || '', render: r => statusBadge(r.account_status) }
		],
		filters: [
			{ key: 'school', label: 'Schools', get: r => r.school_name || 'No school' },
			{ key: 'status', label: 'Statuses', get: r => r.account_status || 'Unknown' },
			{ key: 'gender', label: 'Genders', get: r => r.gender || 'Not set' }
		],
		rowActions: r => [
			{ label: 'View details', run: () => openInternDetails(r) },
			{ label: 'Edit intern', run: () => editIntern(r) }
		]
	});
}

function buildCompanyTable() {
	const numCol = (key, label, field) => ({
		key, label, num: true, get: r => numOrBlank(r[field]), sortVal: r => numSort(r[field])
	});
	return new DataTable({
		key: 'companies',
		prefix: 'c_',
		mount: 'companyTableMount',
		searchPlaceholder: 'Search company, email, address, status…',
		id: r => r.company_id,
		load: () => apiGet(API_COMPANIES).then(d => {
			if (!d || d.success === false) throw new Error((d && d.message) || 'Could not load companies.');
			return d.data || [];
		}),
		onChange: renderHome,
		onAdd: () => openBulkAdd('companies'),
		addLabel: 'Add companies',
		columns: [
			{ key: 'id', label: 'ID', num: true, get: r => Number(r.company_id), render: r => el('span', 'mono', '#' + r.company_id) },
			{ key: 'name', label: 'Company', sticky: true, get: r => r.company_name || '', render: r => el('strong', null, r.company_name || '—') },
			{ key: 'email', label: 'Email', get: r => r.company_email || '', render: r => el('span', 'muted', r.company_email || '') },
			{ key: 'website', label: 'Website', get: r => r.company_website || '' },
			{ key: 'address', label: 'Address', get: r => r.company_address || '' },
			numCol('allowance', 'Allowance', 'intern_allowance'),
			{ key: 'partnership', label: 'Partnership status', get: r => r.partnership_status || '', render: r => r.partnership_status ? el('span', 'chip', r.partnership_status) : mutedText('—') },
			numCol('slots', 'Max intern slots', 'max_internslots'),
			numCol('revenue', 'Revenue growth', 'revenue_growth'),
			numCol('margins', 'Profit margins', 'profit_margins'),
			numCol('roi', 'ROI', 'roi'),
			numCol('roa', 'ROA', 'roa'),
			{ key: 'departments', label: 'Departments', num: true, get: r => Number(r.departments) || 0 }
		],
		filters: [{ key: 'partnership', label: 'Partnership statuses', get: r => r.partnership_status || 'Not set' }],
		rowActions: r => [
			{ label: 'Manage departments', run: () => openDepartments(r.company_id) },
			{ label: 'Edit company', run: () => editCompany(r) }
		]
	});
}

function buildSupervisorTable() {
	return new DataTable({
		key: 'supervisors',
		prefix: 'sv_',
		mount: 'supervisorTableMount',
		searchPlaceholder: 'Search name, email, role, department, company…',
		id: r => r.supervisor_id,
		load: () => apiGet(API_DEPTS + '?action=get_all_supervisors_table').then(d => {
			if (!d || d.success === false) throw new Error((d && d.message) || 'Could not load supervisors.');
			return d.data || [];
		}),
		onChange: renderHome,
		columns: [
			{ key: 'id', label: 'ID', num: true, get: r => Number(r.supervisor_id), render: r => el('span', 'mono', '#' + r.supervisor_id) },
			{ key: 'name', label: 'Name', sticky: true, get: r => r.full_name || '', render: r => el('strong', null, r.full_name || '—') },
			{ key: 'email', label: 'Email', get: r => r.user_email || '', render: r => el('span', 'muted', r.user_email || '') },
			{ key: 'contact', label: 'Contact no.', get: r => r.contact_no || '' },
			{ key: 'role', label: 'Job role', get: r => r.supervisor_jobrole || '' },
			{ key: 'department', label: 'Department', get: r => r.department_name || '' },
			{ key: 'company', label: 'Company', get: r => r.company_name || '' }
		],
		filters: [
			{ key: 'company', label: 'Companies', get: r => r.company_name || 'No company' },
			{ key: 'department', label: 'Departments', get: r => r.department_name || 'No department' }
		]
	});
}

// =========================================================================
// Masterlist front page (dashboard)
// =========================================================================

function renderHome() {
	if (!internTable || !companyTable || !supervisorTable) return;
	const I = internTable, C = companyTable, S = supervisorTable;
	const txt = (id, v) => { const n = document.getElementById(id); if (n) n.textContent = v; };
	const iOk = I.status === 'ready', cOk = C.status === 'ready', sOk = S.status === 'ready';
	const loadingText = t => t.status === 'error' ? 'Could not load' : 'Loading…';

	const count = status => iOk ? I.rows.filter(i => i.account_status === status).length : 0;
	const ready = count('OK'), pending = count('Pending'), incomplete = count('Incomplete');

	txt('statInterns', iOk ? I.rows.length : '—');
	txt('statInternsSub', iOk ? ready + ' ready, ' + pending + ' pending, ' + incomplete + ' incomplete' : loadingText(I));

	txt('statCompanies', cOk ? C.rows.length : '—');
	txt('statCompaniesSub', cOk
		? C.rows.reduce((a, c) => a + (Number(c.departments) || 0), 0) + ' departments in total'
		: loadingText(C));

	txt('statSupervisors', sOk ? S.rows.length : '—');
	txt('statSupervisorsSub', sOk
		? (n => 'Across ' + n + (n === 1 ? ' company' : ' companies'))(new Set(S.rows.map(s => s.company_name)).size)
		: loadingText(S));

	const needs = pending + incomplete;
	txt('statAttention', iOk ? needs : '—');
	txt('statAttentionSub', iOk
		? (needs ? 'Pending password or incomplete profile' : 'Every intern account is ready')
		: loadingText(I));

	const hi = document.getElementById('homeInterns');
	hi.replaceChildren();
	if (!iOk) hi.appendChild(el('p', 'empty-state', I.status === 'error' ? 'Could not load interns.' : 'Loading…'));
	else if (!I.rows.length) hi.appendChild(el('p', 'empty-state', 'No interns yet. Use “Add interns” to create some.'));
	else {
		I.rows.slice().sort((a, b) => Number(b.intern_id) - Number(a.intern_id)).slice(0, 5).forEach(i => {
			const row = el('div', 'mini-row');
			const left = el('div');
			left.append(el('div', 'mini-title', i.full_name || '—'), el('div', 'mini-sub', (i.email || '') + (i.school_name ? ' · ' + i.school_name : '')));
			row.append(left, statusBadge(i.account_status));
			hi.appendChild(row);
		});
	}

	const hc = document.getElementById('homeCompanies');
	hc.replaceChildren();
	if (!cOk) hc.appendChild(el('p', 'empty-state', C.status === 'error' ? 'Could not load companies.' : 'Loading…'));
	else if (!C.rows.length) hc.appendChild(el('p', 'empty-state', 'No companies yet. Use “Add companies” to create some.'));
	else {
		C.rows.slice().sort((a, b) => (Number(b.departments) || 0) - (Number(a.departments) || 0)).slice(0, 5).forEach(c => {
			const n = Number(c.departments) || 0;
			const row = el('div', 'mini-row');
			const left = el('div');
			left.append(el('div', 'mini-title', c.company_name), el('div', 'mini-sub', c.partnership_status || 'No partnership status'));
			row.append(left, el('span', 'chip', n + (n === 1 ? ' department' : ' departments')));
			hc.appendChild(row);
		});
	}

	const sb = document.getElementById('statusBreakdown');
	sb.replaceChildren();
	if (iOk && I.rows.length) {
		[['OK', ready], ['Pending', pending], ['Incomplete', incomplete]].forEach(([label, n]) => {
			const pct = Math.round((n / I.rows.length) * 100);
			const line = el('div', 'bar-line');
			const head = el('div', 'bar-head');
			head.append(el('span', null, label), el('span', 'muted', n + ' (' + pct + '%)'));
			const bg = el('div', 'progress-bar-bg');
			const fill = el('div', 'progress-bar-fill');
			fill.style.width = pct + '%';
			bg.appendChild(fill);
			line.append(head, bg);
			sb.appendChild(line);
		});
	} else {
		sb.appendChild(el('p', 'empty-state', iOk ? 'No interns yet.' : 'Loading…'));
	}
}

function showMasterlistView(view) {
	if (!['home', 'interns', 'companies', 'supervisors', 'bulk-add'].includes(view)) view = 'home';
	document.querySelectorAll('.ml-view').forEach(v => v.classList.toggle('active', v.id === 'ml-' + view));
	if (view === 'bulk-add') initBulkAddPage();
	setParams({ view: view === 'home' ? null : view });
	window.scrollTo(0, 0);
}

function openBulkAdd(mode) {
	bulkMode = mode;
	showMasterlistView('bulk-add');
}

// =========================================================================
// Interns: details and edit
// =========================================================================

function openInternDetails(r) {
	const dl = el('dl', 'detail-list');
	[
		['Intern ID', '#' + r.intern_id], ['Full name', r.full_name || '—'], ['Email', r.email || '—'],
		['School user ID', r.schooluser_given_id || '—'], ['School', r.school_name || '—'], ['Program', r.program_name || '—'],
		['Birthdate', fmtDay(r.birthdate)], ['Gender', r.gender || '—'], ['City', r.city || '—'],
		['Province/State', r.province_or_state || '—'], ['Postal code', r.postal_code || '—'], ['Country', r.country || '—'],
		['Account status', r.account_status || '—']
	].forEach(([k, v]) => dl.append(el('dt', null, k), el('dd', null, v)));

	const actions = el('div', 'modal-actions');
	actions.append(
		makeBtn('Edit', 'btn-outline', () => editIntern(r)),
		makeBtn('Close', 'btn-primary', () => closeModal('dynModal'))
	);
	const wrap = el('div');
	wrap.append(dl, actions);
	showDyn(r.full_name || 'Intern', wrap);
}

function editIntern(r) {
	// Refresh schools/programs first so the dropdowns are current; fall back
	// to whatever was loaded earlier if the request fails.
	loadLookups().catch(err => {
		console.error(err);
		if (!lookupsLoaded) toast('Could not load schools and programs.', 'error');
	}).then(() => {
		const programOptions = sid => programsOfSchool(sid).map(p => ({ value: p.program_id, label: p.program_name }));
		const fallback = splitName(r.full_name);
		const nz = v => (v === undefined || v === null) ? '' : v;

		const form = formModal({
			title: 'Edit intern',
			columns: 2,
			wide: true,
			fields: [
				{ name: 'first_name', label: 'First name', required: true, value: r.first_name !== undefined ? r.first_name : fallback.first },
				{ name: 'last_name', label: 'Last name', required: true, value: r.last_name !== undefined ? r.last_name : fallback.last },
				{ name: 'email', label: 'Email', required: true, type: 'email', value: nz(r.email), full: true },
				{ name: 'schooluser_given_id', label: 'School user ID', required: true, value: nz(r.schooluser_given_id) },
				{ name: 'birthdate', label: 'Birthdate', type: 'date', value: nz(r.birthdate) },
				{
					name: 'gender', label: 'Gender', type: 'select', placeholder: 'Not set', value: nz(r.gender),
					options: [{ value: 'Male', label: 'Male' }, { value: 'Female', label: 'Female' }]
				},
				{ name: 'city', label: 'City', value: nz(r.city) },
				{ name: 'province_or_state', label: 'Province / State', value: nz(r.province_or_state) },
				{ name: 'postal_code', label: 'Postal code', value: nz(r.postal_code) },
				{ name: 'country', label: 'Country', value: nz(r.country) },
				{
					name: 'school_id', label: 'School', required: true, type: 'select', placeholder: 'Select school', value: nz(r.school_id),
					options: lookups.schools.map(s => ({ value: s.school_id, label: s.school_name }))
				},
				{
					name: 'program_id', label: 'Program', required: true, type: 'select', placeholder: 'Select program', value: nz(r.program_id),
					options: programOptions(r.school_id)
				}
			],
			onSubmit: v => {
				const blank = x => (x === undefined || String(x).trim() === '') ? null : String(x).trim();
				return apiPost(API_INTERNS + '?action=update', {
					intern_id: r.intern_id,
					first_name: v.first_name.trim(),
					last_name: v.last_name.trim(),
					email: v.email.trim(),
					schooluser_given_id: v.schooluser_given_id.trim(),
					birthdate: blank(v.birthdate),
					gender: blank(v.gender),
					city: blank(v.city),
					province_or_state: blank(v.province_or_state),
					postal_code: blank(v.postal_code),
					country: blank(v.country),
					program_id: v.program_id
				}).then(expectSuccess).then(() => { toast('Intern updated.', 'success'); return refreshAll(); });
			}
		});

		form.elements['school_id'].addEventListener('change', e => {
			setSelectOptions(form.elements['program_id'], programOptions(e.target.value), '', 'Select program');
		});
	});
}

// =========================================================================
// Companies: edit, departments and supervisors
// =========================================================================

function editCompany(c) {
	const nz = v => (v === undefined || v === null) ? '' : v;
	const num = { step: 'any' };
	formModal({
		title: 'Edit company',
		columns: 2,
		wide: true,
		fields: [
			{ name: 'company_name', label: 'Company name', required: true, value: nz(c.company_name) },
			{ name: 'company_email', label: 'Email', required: true, type: 'email', value: nz(c.company_email) },
			{ name: 'company_website', label: 'Website', value: nz(c.company_website) },
			{ name: 'company_address', label: 'Address', value: nz(c.company_address) },
			{ name: 'intern_allowance', label: 'Allowance', type: 'number', attrs: num, value: nz(c.intern_allowance) },
			{ name: 'partnership_status', label: 'Partnership status', value: nz(c.partnership_status) },
			{ name: 'max_internslots', label: 'Max intern slots', type: 'number', attrs: num, value: nz(c.max_internslots) },
			{ name: 'revenue_growth', label: 'Revenue growth', type: 'number', attrs: num, value: nz(c.revenue_growth) },
			{ name: 'profit_margins', label: 'Profit margins', type: 'number', attrs: num, value: nz(c.profit_margins) },
			{ name: 'roi', label: 'ROI', type: 'number', attrs: num, value: nz(c.roi) },
			{ name: 'roa', label: 'ROA', type: 'number', attrs: num, value: nz(c.roa) }
		],
		onSubmit: v => {
			const blank = x => (x === undefined || String(x).trim() === '') ? null : String(x).trim();
			const payload = { company_id: c.company_id };
			Object.keys(v).forEach(k => { payload[k] = blank(v[k]); });
			return apiPost(API_COMPANIES + '?action=update', payload)
				.then(expectSuccess)
				.then(() => { toast('Company updated.', 'success'); return refreshAll(); });
		}
	});
}

function refreshDepartmentsAndTables(companyId) {
	return Promise.all([openDepartments(companyId), companyTable.reload(true), supervisorTable.reload(true)]);
}

function openDepartments(companyId) {
	const company = companyTable.rows.find(r => String(r.company_id) === String(companyId));
	const title = (company ? company.company_name : 'Company') + ' — departments';
	const enc = encodeURIComponent(companyId);

	const loading = el('div');
	loading.appendChild(el('p', 'empty-state', 'Loading departments…'));
	showDyn(title, loading, { wide: true });

	return apiGet(API_DEPTS + '?action=get_departments&company_id=' + enc)
		.then(expectSuccess)
		.then(res => {
			const depts = res.data || [];
			return Promise.all(depts.map(d =>
				apiGet(API_DEPTS + '?action=get_supervisors_by_dept&department_id=' + encodeURIComponent(d.department_id))
					.then(expectSuccess)
					.then(r => r.data || [])
			)).then(sups => ({ depts, sups }));
		})
		.then(({ depts, sups }) => {
			// The person may have closed the dialog while this was loading.
			if (document.getElementById('dynModal').hidden) return;

			const body = el('div');
			const head = el('div', 'dept-head');
			head.append(
				el('span', 'muted small', depts.length + (depts.length === 1 ? ' department' : ' departments')),
				makeBtn('+ Add department', 'btn-primary', () => departmentForm(companyId))
			);
			body.appendChild(head);

			if (!depts.length) body.appendChild(el('p', 'empty-state', 'This company has no departments yet.'));
			depts.forEach((d, i) => body.appendChild(buildDepartmentCard(companyId, d, sups[i])));
			showDyn(title, body, { wide: true });
		})
		.catch(err => {
			toast(err.message, 'error');
			closeModal('dynModal');
		});
}

function buildDepartmentCard(companyId, dept, supervisors) {
	const card = el('div', 'dept-card');
	const head = el('div', 'dept-card-head');
	const actions = el('div', 'dept-card-actions');
	actions.append(
		makeBtn('+ Add supervisor', 'btn-primary', () => supervisorForm(companyId, dept)),
		makeBtn('Edit', 'btn-outline', () => departmentForm(companyId, dept))
	);
	head.append(el('strong', null, dept.department_name), actions);
	card.appendChild(head);

	if (!supervisors.length) {
		card.appendChild(el('p', 'empty-state small', 'No supervisors yet.'));
		return card;
	}
	supervisors.forEach(s => {
		const row = el('div', 'mini-row');
		const left = el('div');
		const name = ((s.user_first_name || '') + ' ' + (s.user_last_name || '')).trim();
		const sub = [s.user_email, s.supervisor_jobrole, s.contact_no].filter(Boolean).join(' · ');
		left.append(el('div', 'mini-title', name), el('div', 'mini-sub', sub));
		row.append(left, makeBtn('Edit', 'btn-outline', () => supervisorForm(companyId, dept, s)));
		card.appendChild(row);
	});
	return card;
}

function departmentForm(companyId, dept) {
	formModal({
		modal: 'subModal',
		title: dept ? 'Edit department' : 'Add department',
		submit: dept ? 'Save changes' : 'Add department',
		fields: [{ name: 'department_name', label: 'Department name', required: true, value: dept ? dept.department_name : '', attrs: { placeholder: 'e.g. IT, HR' } }],
		onSubmit: v => apiPost(API_DEPTS + '?action=save_department', {
			department_id: dept ? dept.department_id : null,
			company_id: companyId,
			department_name: v.department_name.trim()
		}).then(expectSuccess).then(() => {
			toast(dept ? 'Department updated.' : 'Department added.', 'success');
			return refreshDepartmentsAndTables(companyId);
		})
	});
}

function supervisorForm(companyId, dept, s) {
	const nz = v => (v === undefined || v === null) ? '' : v;
	formModal({
		modal: 'subModal',
		title: s ? 'Edit supervisor' : 'Add supervisor — ' + dept.department_name,
		submit: s ? 'Save changes' : 'Add supervisor',
		fields: [
			{ name: 'first_name', label: 'First name', required: true, value: s ? nz(s.user_first_name) : '' },
			{ name: 'last_name', label: 'Last name', required: true, value: s ? nz(s.user_last_name) : '' },
			{ name: 'email', label: 'Email', required: true, type: 'email', value: s ? nz(s.user_email) : '' },
			{ name: 'jobrole', label: 'Job role', value: s ? nz(s.supervisor_jobrole) : '' },
			{ name: 'contact_no', label: 'Contact number', value: s ? nz(s.contact_no) : '' }
		],
		onSubmit: v => apiPost(API_DEPTS + '?action=save_supervisor', {
			user_id: s ? s.user_id : null,
			department_id: dept.department_id,
			first_name: v.first_name.trim(),
			last_name: v.last_name.trim(),
			email: v.email.trim(),
			jobrole: v.jobrole.trim(),
			contact_no: v.contact_no.trim()
		}).then(expectSuccess).then(() => {
			toast(s ? 'Supervisor updated.' : 'Supervisor added.', 'success');
			return refreshDepartmentsAndTables(companyId);
		})
	});
}

// =========================================================================
// Bulk Add
// =========================================================================

const GENDERS = ['Male', 'Female'];

const BULK_MODES = {
	interns: {
		title: 'Bulk Add Interns',
		noun: 'intern(s)',
		endpoint: API_INTERNS + '?action=add',
		columns: [
			{ key: 'first_name', label: 'First Name *', type: 'text', required: true },
			{ key: 'last_name', label: 'Last Name *', type: 'text', required: true },
			{ key: 'email', label: 'Email *', type: 'email', required: true },
			{ key: 'school_user_id', label: 'School User ID *', type: 'text', required: true },
			{ key: 'school_name', label: 'School Name *', type: 'school', required: true },
			{ key: 'program_name', label: 'Program Name *', type: 'program', required: true },
			{ key: 'birthdate', label: 'Birthdate', type: 'date' },
			{ key: 'gender', label: 'Gender', type: 'choice', options: GENDERS },
			{ key: 'city', label: 'City', type: 'text' },
			{ key: 'province', label: 'Province/State', type: 'text' },
			{ key: 'postal', label: 'Postal Code', type: 'text' },
			{ key: 'country', label: 'Country', type: 'text' }
		],
		// The interns endpoint expects camelCase keys.
		toPayload(rows) {
			return {
				interns: rows.map(r => {
					const school = findSchoolByName(r.school_name);
					const program = school ? findProgram(school.school_id, r.program_name) : null;
					const gender = GENDERS.find(g => g.toLowerCase() === String(r.gender || '').trim().toLowerCase());
					const t = v => String(v ?? '').trim();
					return {
						firstName: t(r.first_name),
						lastName: t(r.last_name),
						email: t(r.email),
						schoolUserId: t(r.school_user_id),
						// Use the stored spelling when we can match it, so capitalisation never causes a mismatch.
						schoolName: school ? school.school_name : t(r.school_name),
						programName: program ? program.program_name : t(r.program_name),
						birthdate: t(r.birthdate) || null,
						gender: gender || null,
						city: t(r.city) || null,
						province: t(r.province) || null,
						postal: t(r.postal) || null,
						country: t(r.country) || null
					};
				})
			};
		}
	},
	companies: {
		title: 'Bulk Add Companies',
		noun: 'company(ies)',
		endpoint: API_COMPANIES + '?action=add',
		columns: [
			{ key: 'company_name', label: 'Company Name *', type: 'text', required: true },
			{ key: 'company_email', label: 'Email *', type: 'email', required: true },
			{ key: 'company_website', label: 'Website', type: 'text' },
			{ key: 'company_address', label: 'Address', type: 'text' },
			{ key: 'intern_allowance', label: 'Allowance', type: 'number' },
			{ key: 'partnership_status', label: 'Partnership Status', type: 'text' },
			{ key: 'max_internslots', label: 'Max Intern Slots', type: 'number' },
			{ key: 'revenue_growth', label: 'Revenue Growth', type: 'number' },
			{ key: 'profit_margins', label: 'Profit Margins', type: 'number' },
			{ key: 'roi', label: 'ROI', type: 'number' },
			{ key: 'roa', label: 'ROA', type: 'number' }
		],
		toPayload(rows) {
			return {
				companies: rows.map(r => {
					const out = {};
					BULK_MODES.companies.columns.forEach(c => { out[c.key] = String(r[c.key] ?? '').trim(); });
					return out;
				})
			};
		}
	}
};

let bulkMode = 'interns';
let bulkRows = [];
let isTextEditorView = false;

function bulkCfg() { return BULK_MODES[bulkMode]; }
function getBulkColumns() { return bulkCfg().columns; }
function colLabel(c) { return c.label.replace(' *', ''); }
function getBulkHeaderLine() { return getBulkColumns().map(colLabel).join(', '); }

// ---------- CSV helpers (quotes allowed, so commas inside values survive) ----------

function parseDelimitedLine(line, delim) {
	const out = [];
	let field = '';
	let inQuotes = false;
	for (let i = 0; i < line.length; i++) {
		const ch = line[i];
		if (inQuotes) {
			if (ch === '"') {
				if (line[i + 1] === '"') { field += '"'; i++; } else inQuotes = false;
			} else field += ch;
		} else if (ch === '"' && field.trim() === '') {
			inQuotes = true;
			field = '';
		} else if (ch === delim) {
			out.push(field.trim());
			field = '';
		} else field += ch;
	}
	out.push(field.trim());
	return out;
}

function textToMatrix(text) {
	return text.split(/\r?\n/)
		.filter(l => l.trim() !== '')
		.map(l => parseDelimitedLine(l, l.includes('\t') ? '\t' : ','));
}

function csvCell(v) {
	const s = String(v ?? '').replace(/[\r\n]+/g, ' ');
	return /[",]/.test(s) ? '"' + s.replace(/"/g, '""') + '"' : s;
}

function normalizeDate(input) {
	if (input === undefined || input === null) return '';
	input = String(input).trim();
	if (!input) return '';
	if (/^\d{4}-\d{2}-\d{2}$/.test(input)) return input;

	// Excel serial date (e.g. 45291)
	if (!isNaN(input) && Number(input) > 20000) {
		const d = new Date(Math.round((Number(input) - 25569) * 86400000));
		return d.getUTCFullYear() + '-' + String(d.getUTCMonth() + 1).padStart(2, '0') + '-' + String(d.getUTCDate()).padStart(2, '0');
	}

	const d = new Date(input);
	if (!isNaN(d.getTime())) {
		return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
	}
	return '';
}

function normalizeCell(col, value) {
	const v = String(value).trim();
	if (col.type === 'date') return normalizeDate(v);
	if (col.type === 'choice') return col.options.find(o => o.toLowerCase() === v.toLowerCase()) || v;
	return v;
}

function isHeaderRowLine(parts, cols) {
	if (parts.length < cols.length) return false;
	return cols.every((c, idx) => String(parts[idx] || '').replace(' *', '').trim().toLowerCase() === colLabel(c).toLowerCase());
}

function matrixToRows(matrix, forceSkipFirst) {
	const cols = getBulkColumns();
	let data = matrix.filter(r => r.some(c => String(c ?? '').trim() !== ''));
	if (data.length && (forceSkipFirst || isHeaderRowLine(data[0].map(String), cols))) data = data.slice(1);
	return data.map(parts => {
		const r = getEmptyBulkRow();
		cols.forEach((c, idx) => {
			const v = parts[idx];
			if (v === undefined || v === null || String(v).trim() === '') return;
			r[c.key] = normalizeCell(c, v);
		});
		return r;
	}).filter(r => !isRowEmpty(r));
}

// ---------- Mode and row state ----------

function switchBulkMode(mode) {
	if (mode === bulkMode) return;
	if (bulkRows.some(r => !isRowEmpty(r)) && !confirm('Switching tables clears the rows you have entered. Continue?')) return;
	bulkMode = mode;
	setBulkMode(mode);
}

function setBulkMode(mode) {
	bulkMode = mode;
	document.getElementById('bulkModeInterns').className = 'btn btn-sm ' + (mode === 'interns' ? 'btn-primary' : 'btn-outline');
	document.getElementById('bulkModeCompanies').className = 'btn btn-sm ' + (mode === 'companies' ? 'btn-primary' : 'btn-outline');
	document.getElementById('bulkTitle').textContent = bulkCfg().title;
	document.getElementById('bulkHeaderHint').textContent = 'Column order: ' + getBulkHeaderLine();

	const fileInput = document.getElementById('bulkFileInput');
	if (fileInput) fileInput.value = '';

	resetBulkTable();
}

function initBulkAddPage() {
	setBulkMode(bulkMode);
	// Refresh the school/program suggestions, then redraw so the per-row lists pick them up.
	if (bulkMode === 'interns') {
		loadLookups().then(() => {
			if (!bulkRows.some(r => !isRowEmpty(r))) renderBulkTable();
		}).catch(err => console.error('Could not load schools and programs:', err));
	}
}

function getEmptyBulkRow() {
	const r = {};
	getBulkColumns().forEach(c => { r[c.key] = ''; });
	return r;
}

function isRowEmpty(r) {
	if (!r || typeof r !== 'object') return true;
	return !getBulkColumns().some(c => r[c.key] !== undefined && r[c.key] !== null && String(r[c.key]).trim() !== '');
}

function showBulkErrors(messages) {
	const box = document.getElementById('bulkErrorBox');
	if (!box) return;
	box.replaceChildren();
	if (!messages || !messages.length) { box.hidden = true; return; }
	box.appendChild(el('strong', null, 'These rows were not saved:'));
	const ul = el('ul');
	messages.forEach(m => ul.appendChild(el('li', null, m)));
	box.appendChild(ul);
	box.hidden = false;
}

function resetBulkTable() {
	bulkRows = [getEmptyBulkRow()];
	showBulkErrors([]);
	const textarea = document.getElementById('bulkTextarea');
	if (textarea) textarea.value = isTextEditorView ? getBulkHeaderLine() + '\n' : '';
	renderBulkTable();
}

function clearBulkRow(index) {
	if (bulkRows.length > 1) bulkRows.splice(index, 1);
	else bulkRows[0] = getEmptyBulkRow();
	pruneAndEnsureTrailingEmptyRow();
	renderBulkTable();
}

function pruneAndEnsureTrailingEmptyRow() {
	// Remove blank rows except the trailing one and keep the cursor where it was.
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
			const targetTr = tbody.children[Math.min(focusRowIdx, tbody.children.length - 1)];
			if (targetTr && targetTr.children[focusColIdx]) {
				const targetInp = targetTr.children[focusColIdx].querySelector('.bulk-cell-input');
				if (targetInp) targetInp.focus();
			}
		}
		return true;
	}
	return false;
}

function handleBulkCellChange(rIdx, tr) {
	const didReRender = pruneAndEnsureTrailingEmptyRow();
	if (!didReRender) {
		checkRowValidity(rIdx, tr);
		const tbody = document.getElementById('bulkTbody');
		if (tbody) {
			while (tbody.children.length < bulkRows.length) {
				const newIdx = tbody.children.length;
				tbody.appendChild(buildBulkRowElement(bulkRows[newIdx], newIdx, getBulkColumns()));
			}
			[...tbody.children].forEach((rowEl, idx) => {
				rowEl.dataset.rowIndex = String(idx);
				const n = rowEl.querySelector('.bulk-rownum');
				if (n) n.textContent = String(idx + 1);
			});
		}
	}
	updateBulkStatus();
}

function populateProgramDatalist(datalist, schoolName) {
	datalist.replaceChildren();
	const school = findSchoolByName(schoolName);
	if (!school) return;
	programsOfSchool(school.school_id).forEach(p => {
		const opt = document.createElement('option');
		opt.value = p.program_name;
		datalist.appendChild(opt);
	});
}

function updateRowProgramDatalist(tr, row) {
	const progInput = tr.querySelector('input[data-col-key="program_name"]');
	if (!progInput) return;
	const listId = progInput.getAttribute('list');
	const datalist = listId ? document.getElementById(listId) : null;
	if (datalist) populateProgramDatalist(datalist, row.school_name);
}

function buildBulkRowElement(row, rIdx, cols) {
	const tr = document.createElement('tr');
	tr.className = 'bulk-row';
	tr.dataset.rowIndex = String(rIdx);

	// Selection checkbox + row number
	const tdCheck = document.createElement('td');
	tdCheck.className = 'bulk-check-cell';
	const cb = document.createElement('input');
	cb.type = 'checkbox';
	cb.className = 'bulk-row-cb';
	cb.setAttribute('aria-label', 'Select row ' + (rIdx + 1));
	cb.addEventListener('change', () => tr.classList.toggle('bulk-row-selected', cb.checked));
	tdCheck.append(cb, el('span', 'bulk-rownum', String(rIdx + 1)));
	tr.appendChild(tdCheck);

	const onEdit = (c, value) => {
		row[c.key] = value;
		if (row._serverErr) { delete row._serverErr[c.key]; delete row._serverErr['*']; }
		handleBulkCellChange(parseInt(tr.dataset.rowIndex, 10), tr);
	};

	cols.forEach(c => {
		const td = document.createElement('td');
		const val = row[c.key] || '';
		const input = document.createElement('input');
		input.className = 'bulk-cell-input';
		input.dataset.colKey = c.key;
		input.value = val;

		if (c.type === 'date') {
			input.type = 'date';
			input.addEventListener('input', e => onEdit(c, e.target.value));
			td.appendChild(input);
		} else if (c.type === 'school') {
			input.type = 'text';
			input.placeholder = 'Select or type school...';
			input.setAttribute('list', 'bulkSchoolList');
			input.addEventListener('input', e => {
				row[c.key] = e.target.value;
				updateRowProgramDatalist(tr, row);
				onEdit(c, e.target.value);
			});
			td.appendChild(input);
		} else if (c.type === 'program') {
			input.type = 'text';
			input.placeholder = 'Select or type program...';
			const wrap = el('div', 'bulk-cell-wrap');
			const listId = 'dl_prog_' + rIdx + '_' + Math.random().toString(36).slice(2, 8);
			const datalist = document.createElement('datalist');
			datalist.id = listId;
			populateProgramDatalist(datalist, row.school_name);
			input.setAttribute('list', listId);
			input.addEventListener('input', e => onEdit(c, e.target.value));
			wrap.append(input, datalist);
			td.appendChild(wrap);
		} else if (c.type === 'choice') {
			input.type = 'text';
			input.placeholder = 'Select or type...';
			const wrap = el('div', 'bulk-cell-wrap');
			const listId = 'dl_' + c.key + '_' + rIdx + '_' + Math.random().toString(36).slice(2, 8);
			const datalist = document.createElement('datalist');
			datalist.id = listId;
			c.options.forEach(o => {
				const opt = document.createElement('option');
				opt.value = o;
				datalist.appendChild(opt);
			});
			input.setAttribute('list', listId);
			input.addEventListener('input', e => onEdit(c, e.target.value));
			wrap.append(input, datalist);
			td.appendChild(wrap);
		} else {
			input.type = c.type === 'email' ? 'email' : 'text';
			if (c.type === 'number') input.inputMode = 'decimal';
			input.placeholder = c.required ? 'Required' : 'Optional';
			input.addEventListener('input', e => onEdit(c, e.target.value));
			td.appendChild(input);
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

function renderBulkTable() {
	const theadRow = document.getElementById('bulkTheadRow');
	const tbody = document.getElementById('bulkTbody');
	if (!theadRow || !tbody) return;
	const cols = getBulkColumns();

	theadRow.innerHTML = '';
	const thCheck = document.createElement('th');
	thCheck.className = 'bulk-check-cell';
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

	tbody.innerHTML = '';
	bulkRows.forEach((row, rIdx) => tbody.appendChild(buildBulkRowElement(row, rIdx, cols)));
	updateBulkStatus();
}

// ---------- Validation ----------

// Returns { empty, valid, errors: { columnKey: message } }
function validateSingleRow(row) {
	if (isRowEmpty(row)) return { empty: true, valid: true, errors: {} };
	const cols = getBulkColumns();
	const errors = {};

	cols.forEach(c => {
		const val = String(row[c.key] ?? '').trim();

		if (c.required && val === '') { errors[c.key] = 'Required'; return; }
		if (val === '') return;

		if (c.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) errors[c.key] = 'Enter a valid email address';
		if (c.type === 'number' && (isNaN(Number(val)) || !isFinite(Number(val)))) errors[c.key] = 'Enter a number';
		if (c.type === 'date' && !/^\d{4}-\d{2}-\d{2}$/.test(val)) errors[c.key] = 'Use the format YYYY-MM-DD';
		if (c.type === 'choice' && !c.options.some(o => o.toLowerCase() === val.toLowerCase())) errors[c.key] = 'Choose one of: ' + c.options.join(', ');
	});

	// Cross-check school and program against what is stored (skipped if the lists could not be loaded).
	if (bulkMode === 'interns' && lookupsLoaded && lookups.schools.length) {
		const schoolName = String(row.school_name ?? '').trim();
		const progName = String(row.program_name ?? '').trim();
		const school = schoolName ? findSchoolByName(schoolName) : null;
		if (schoolName && !school) errors.school_name = 'No school with this name exists';
		else if (school && progName && !findProgram(school.school_id, progName)) errors.program_name = 'This school has no program with this name';
	}

	return { empty: false, valid: Object.keys(errors).length === 0, errors };
}

function checkRowValidity(rIdx, tr) {
	const row = bulkRows[rIdx];
	if (!row || !tr) return;
	const res = validateSingleRow(row);
	const server = row._serverErr || {};
	const cols = getBulkColumns();

	[...tr.children].slice(1, 1 + cols.length).forEach((td, idx) => {
		const col = cols[idx];
		const inp = td.querySelector('.bulk-cell-input');
		if (!inp) return;
		const msg = (!res.empty && res.errors[col.key]) || server[col.key];
		inp.classList.toggle('bulk-cell-invalid', !!msg);
		inp.title = msg || '';
	});
	tr.classList.toggle('bulk-row-error', !!row._serverErr && Object.keys(row._serverErr).length > 0);
}

function updateBulkStatus() {
	const nonEmpty = bulkRows.filter(r => !isRowEmpty(r));
	const invalidCount = nonEmpty.filter(r => !validateSingleRow(r).valid).length;
	const msgEl = document.getElementById('bulkStatusMsg');
	if (!msgEl) return;

	if (nonEmpty.length === 0) msgEl.textContent = '0 rows entered';
	else if (invalidCount > 0) msgEl.textContent = `${nonEmpty.length} row(s) entered (${invalidCount} row(s) have incomplete or invalid fields)`;
	else msgEl.textContent = `${nonEmpty.length} valid row(s) ready to save`;
}

// ---------- Paste, delete, text editor ----------

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
	const startColIdx = [...startTr.children].indexOf(activeEl.closest('td')) - 1;

	lines.forEach((line, rOffset) => {
		const cells = line.split('\t');
		const targetRowIdx = startRowIdx + rOffset;
		while (targetRowIdx >= bulkRows.length) bulkRows.push(getEmptyBulkRow());
		cells.forEach((cellVal, cOffset) => {
			const targetColIdx = startColIdx + cOffset;
			if (targetColIdx >= 0 && targetColIdx < cols.length) {
				bulkRows[targetRowIdx][cols[targetColIdx].key] = normalizeCell(cols[targetColIdx], cellVal);
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
	if (activeEl && (activeEl.tagName === 'INPUT' && activeEl.type !== 'checkbox' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) return;

	const selectedRows = document.querySelectorAll('.bulk-row-selected');
	if (selectedRows.length > 0) {
		e.preventDefault();
		const indices = [...selectedRows].map(tr => parseInt(tr.dataset.rowIndex, 10)).sort((a, b) => b - a);
		indices.forEach(idx => clearBulkRow(idx));
		toast('Cleared selected row(s).', 'success');
	}
});

function bulkRowsToText() {
	const cols = getBulkColumns();
	const dataLines = bulkRows.filter(r => !isRowEmpty(r))
		.map(r => cols.map(c => csvCell(r[c.key])).join(','));
	return [getBulkHeaderLine(), ...dataLines].join('\n');
}

function showBulkTableView() {
	isTextEditorView = false;
	document.getElementById('bulkTableViewWrap').style.display = 'block';
	document.getElementById('bulkTextViewWrap').style.display = 'none';
	document.getElementById('bulkViewToggleLabel').textContent = 'Text Editor (CSV)';
	renderBulkTable();
}

function toggleBulkTextEditor() {
	if (isTextEditorView) {
		syncBulkFromText();
		showBulkTableView();
		return;
	}
	isTextEditorView = true;
	document.getElementById('bulkTableViewWrap').style.display = 'none';
	document.getElementById('bulkTextViewWrap').style.display = 'block';
	document.getElementById('bulkViewToggleLabel').textContent = 'Switch to Table View';
	document.getElementById('bulkTextarea').value = bulkRowsToText();
}

function syncBulkFromText() {
	const textarea = document.getElementById('bulkTextarea');
	if (!textarea) return;
	const newRows = matrixToRows(textToMatrix(textarea.value), false);
	newRows.push(getEmptyBulkRow());
	bulkRows = newRows;
	updateBulkStatus();
}

function handleBulkFileImport(e) {
	const input = e.target;
	const file = input.files[0];
	if (!file) return;
	const hasHeaders = document.getElementById('bulkHasHeaders').checked;
	const isExcel = /\.(xlsx|xls)$/i.test(file.name);

	const reader = new FileReader();
	reader.onerror = () => { toast('Could not read that file.', 'error'); input.value = ''; };
	reader.onload = evt => {
		let matrix;
		try {
			if (isExcel) {
				if (typeof XLSX === 'undefined') {
					toast('The Excel library failed to load. Refresh the page and try again.', 'error');
					return;
				}
				const wb = XLSX.read(new Uint8Array(evt.target.result), { type: 'array' });
				matrix = XLSX.utils.sheet_to_json(wb.Sheets[wb.SheetNames[0]], { header: 1, defval: '' });
			} else {
				matrix = textToMatrix(String(evt.target.result));
			}
		} catch (err) {
			console.error(err);
			toast('Could not read that file.', 'error');
			return;
		} finally {
			input.value = '';
		}

		const rows = matrixToRows(matrix, hasHeaders);
		if (!rows.length) { toast('No data rows found in that file.', 'error'); return; }

		rows.push(getEmptyBulkRow());
		bulkRows = rows;
		showBulkErrors([]);
		if (isTextEditorView) document.getElementById('bulkTextarea').value = bulkRowsToText();
		else renderBulkTable();
		updateBulkStatus();
		toast(`Imported ${rows.length - 1} row(s).`, 'success');
	};
	if (isExcel) reader.readAsArrayBuffer(file); else reader.readAsText(file);
}

// ---------- Saving ----------

function submitBulkData() {
	if (isTextEditorView) syncBulkFromText();

	const src = []; // table index of each row we send
	bulkRows.forEach((r, i) => { if (!isRowEmpty(r)) src.push(i); });
	if (!src.length) {
		toast('Please enter at least one row before saving.', 'error');
		return;
	}

	// Strict validation: block the whole batch if even one row is invalid or incomplete.
	for (const i of src) {
		if (!validateSingleRow(bulkRows[i]).valid) {
			if (isTextEditorView) showBulkTableView();
			else renderBulkTable();
			toast(`Row #${i + 1} has missing or invalid fields. Fix all highlighted cells before saving.`, 'error');
			return;
		}
	}

	const btn = document.getElementById('bulkSaveBtn');
	btn.disabled = true;
	showBulkErrors([]);

	const cfg = bulkCfg();
	apiPost(cfg.endpoint, cfg.toPayload(src.map(i => bulkRows[i])))
		.then(data => handleBulkResult(data, src))
		.catch(err => toast(err.message || 'Bulk submission failed.', 'error'))
		.finally(() => { btn.disabled = false; });
}

function handleBulkResult(data, src) {
	const cfg = bulkCfg();
	const errors = Array.isArray(data.errors) ? data.errors : [];

	if (data.success && !errors.length) {
		toast(`Saved ${data.inserted ?? src.length} ${cfg.noun}.`, 'success');
		resetBulkTable();
		refreshAll();
		showMasterlistView('home');
		return;
	}

	if (!errors.length) {
		toast(data.message || 'Bulk submission failed.', 'error');
		return;
	}

	// An intern can be saved even though the password email failed; treat that as a warning, not a failed row.
	const isMailWarning = e => bulkMode === 'interns' && /^Email\/token error/i.test(e.message || '');
	const mailWarnings = errors.filter(isMailWarning);
	const hard = errors.filter(e => !isMailWarning(e));
	// When "inserted" is present the server kept the rows that passed; otherwise it rolled everything back.
	const partial = data.inserted !== undefined;
	const saved = partial ? Number(data.inserted) || 0 : 0;

	if (mailWarnings.length) {
		toast(`${mailWarnings.length} intern(s) were saved, but the password email could not be sent. Resend the link manually.`, 'error');
	}

	if (!hard.length && partial) {
		toast(`Saved ${saved} ${cfg.noun}.`, 'success');
		resetBulkTable();
		refreshAll();
		showMasterlistView('home');
		return;
	}

	// Attach server messages to the rows they belong to.
	const failed = new Map(); // table index -> { field: message }
	hard.forEach(e => {
		const tableIdx = src[Number(e.row)];
		if (tableIdx === undefined) return;
		const bucket = failed.get(tableIdx) || {};
		bucket[e.field && e.field !== 'database' ? e.field : '*'] = e.message || 'Could not save this row';
		failed.set(tableIdx, bucket);
	});

	let nextRows;
	if (partial) nextRows = bulkRows.filter((r, i) => failed.has(i));
	else nextRows = bulkRows.filter(r => !isRowEmpty(r));
	const keepIdx = partial ? [...failed.keys()].sort((a, b) => a - b) : src;

	nextRows.forEach((r, n) => { r._serverErr = failed.get(keepIdx[n]) || {}; });
	const messages = nextRows.map((r, n) => {
		const msgs = Object.values(r._serverErr);
		return msgs.length ? `Row ${n + 1}: ${[...new Set(msgs)].join('; ')}` : null;
	}).filter(Boolean);

	nextRows.push(getEmptyBulkRow());
	bulkRows = nextRows;
	if (isTextEditorView) showBulkTableView(); else renderBulkTable();
	showBulkErrors(messages.length ? messages : hard.map(e => e.message || 'Could not save'));

	toast(partial
		? `Saved ${saved} ${cfg.noun}. ${nextRows.length - 1} row(s) still need fixing.`
		: 'Nothing was saved. Fix the highlighted rows and try again.', 'error');
	if (partial && saved > 0) refreshAll();
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
	const p = new URLSearchParams(location.search);
	const tab = p.get('tab');
	if (tab && document.getElementById(tab) && document.getElementById(tab).tagName === 'SECTION') showPage(tab);

	internTable = buildInternTable();
	companyTable = buildCompanyTable();
	supervisorTable = buildSupervisorTable();
	renderHome();

	loadLookups().catch(err => console.error('Could not load schools and programs:', err));
	showMasterlistView(p.get('view') || 'home');

	internTable.reload(true);
	companyTable.reload(true);
	supervisorTable.reload(true);
});
