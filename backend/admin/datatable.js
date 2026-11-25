// =========================================================================
// datatable.js - reusable client-side data table
// Search, column filters, multi-column sort, pagination, row menus,
// bulk selection, column toggle, density, CSV export, print, URL syncing,
// and loading / error / empty states.
// =========================================================================

function el(tag, cls, text) {
	const n = document.createElement(tag);
	if (cls) n.className = cls;
	if (text !== undefined && text !== null) n.textContent = text;
	return n;
}

function closeFloating() {
	document.querySelectorAll('.dt-menu').forEach(m => m.remove());
}

document.addEventListener('click', e => {
	if (!e.target.closest('.dt-menu')) closeFloating();
});
window.addEventListener('resize', closeFloating);
window.addEventListener('scroll', closeFloating, true);

function setParams(obj) {
	const p = new URLSearchParams(location.search);
	Object.entries(obj).forEach(([k, v]) => (v ? p.set(k, v) : p.delete(k)));
	const qs = p.toString();
	history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '') + location.hash);
}

// Spreadsheet apps treat cells starting with = + - @ as formulas.
function csvSafe(v) {
	let s = String(v ?? '');
	if (/^[=+\-@]/.test(s)) s = "'" + s;
	return '"' + s.replace(/"/g, '""') + '"';
}

class DataTable {
	constructor(cfg) {
		this.cfg = cfg;
		this.root = document.getElementById(cfg.mount);
		this.rows = [];
		this.status = 'loading';
		this.q = '';
		this.filters = {};
		this.sort = [];
		this.page = 1;
		this.size = 10;
		this.selected = new Set();
		this.hidden = new Set(this.loadPref('cols', []));
		this.density = this.loadPref('density', 'standard');
		this.view = [];
		this.pageRows = [];
		this.readUrl();
		this.buildShell();
		this.render();
	}

	// ---------- preferences & URL ----------
	loadPref(k, d) {
		try {
			const v = localStorage.getItem('dt_' + this.cfg.key + '_' + k);
			return v ? JSON.parse(v) : d;
		} catch (e) { return d; }
	}
	savePref(k, v) {
		try { localStorage.setItem('dt_' + this.cfg.key + '_' + k, JSON.stringify(v)); } catch (e) { /* ignore */ }
	}

	readUrl() {
		const p = new URLSearchParams(location.search), x = this.cfg.prefix;
		this.q = p.get(x + 'q') || '';
		(this.cfg.filters || []).forEach(f => {
			const v = p.get(x + 'f_' + f.key);
			if (v) this.filters[f.key] = v;
		});
		const s = p.get(x + 'sort');
		if (s) {
			this.sort = s.split(',').map(t => {
				const [key, dir] = t.split(':');
				return { key, dir: dir === 'desc' ? 'desc' : 'asc' };
			}).filter(t => this.cfg.columns.some(c => c.key === t.key));
		}
		this.page = parseInt(p.get(x + 'page'), 10) || 1;
		const sz = parseInt(p.get(x + 'size'), 10);
		if ([10, 25, 50, 100].includes(sz)) this.size = sz;
	}

	writeUrl() {
		const p = new URLSearchParams(location.search), x = this.cfg.prefix;
		[...p.keys()].filter(k => k.startsWith(x)).forEach(k => p.delete(k));
		if (this.q) p.set(x + 'q', this.q);
		Object.entries(this.filters).forEach(([k, v]) => { if (v) p.set(x + 'f_' + k, v); });
		if (this.sort.length) p.set(x + 'sort', this.sort.map(s => s.key + ':' + s.dir).join(','));
		if (this.page > 1) p.set(x + 'page', this.page);
		if (this.size !== 10) p.set(x + 'size', this.size);
		const qs = p.toString();
		history.replaceState(null, '', location.pathname + (qs ? '?' + qs : '') + location.hash);
	}

	// ---------- shell ----------
	buildShell() {
		const root = this.root;
		root.innerHTML = '';
		root.classList.add('dt');

		const tb = el('div', 'dt-toolbar');
		this.searchInput = el('input', 'dt-search');
		this.searchInput.type = 'search';
		this.searchInput.placeholder = this.cfg.searchPlaceholder || 'Search…';
		this.searchInput.value = this.q;
		this.searchInput.setAttribute('aria-label', 'Search');
		let t;
		this.searchInput.addEventListener('input', () => {
			clearTimeout(t);
			t = setTimeout(() => { this.q = this.searchInput.value; this.page = 1; this.render(); }, 150);
		});
		this.filterBox = el('div', 'dt-filters');
		const spacer = el('div', 'dt-spacer');

		const colBtn = this.toolBtn('Columns', e => { e.stopPropagation(); this.openColumnsMenu(colBtn); });

		this.densitySel = el('select', 'dt-select');
		this.densitySel.title = 'Row density';
		[['compact', 'Compact'], ['standard', 'Standard'], ['comfortable', 'Comfortable']].forEach(([v, l]) => {
			const o = el('option', null, l); o.value = v; this.densitySel.appendChild(o);
		});
		this.densitySel.value = this.density;
		this.densitySel.addEventListener('change', () => {
			this.density = this.densitySel.value;
			this.savePref('density', this.density);
			this.table.className = 'dt-table dt-' + this.density;
		});

		const exportBtn = this.toolBtn('Export CSV', () => this.exportCsv());
		const printBtn = this.toolBtn('Print / PDF', () => this.printView());
		const refreshBtn = this.toolBtn('Refresh', () => this.reload());

		tb.append(this.searchInput, this.filterBox, spacer, colBtn, this.densitySel, exportBtn, printBtn, refreshBtn);

		this.bulkBar = el('div', 'dt-bulk');
		this.bulkBar.hidden = true;

		const wrap = el('div', 'dt-wrap');
		this.table = el('table', 'dt-table dt-' + this.density);
		this.thead = document.createElement('thead');
		this.tbody = document.createElement('tbody');
		this.table.append(this.thead, this.tbody);
		wrap.appendChild(this.table);

		const foot = el('div', 'dt-footer');
		this.info = el('span', 'dt-info');
		const sizeWrap = el('label', 'dt-size', 'Rows per page ');
		this.sizeSel = el('select', 'dt-select');
		[10, 25, 50, 100].forEach(n => { const o = el('option', null, String(n)); o.value = n; this.sizeSel.appendChild(o); });
		this.sizeSel.value = this.size;
		this.sizeSel.addEventListener('change', () => { this.size = parseInt(this.sizeSel.value, 10); this.page = 1; this.render(); });
		sizeWrap.appendChild(this.sizeSel);
		this.pager = el('div', 'dt-pager');
		foot.append(this.info, sizeWrap, this.pager);

		root.append(tb, this.bulkBar, wrap, foot);
	}

	toolBtn(label, onClick) {
		const b = el('button', 'btn btn-outline btn-sm', label);
		b.type = 'button';
		b.addEventListener('click', onClick);
		return b;
	}

	renderFilters() {
		this.filterBox.innerHTML = '';
		(this.cfg.filters || []).forEach(f => {
			const sel = el('select', 'dt-select');
			sel.setAttribute('aria-label', f.label);
			const all = el('option', null, 'All ' + f.label.toLowerCase());
			all.value = '';
			sel.appendChild(all);
			const vals = [...new Set(this.rows.map(r => String(f.get(r))))].sort((a, b) => a.localeCompare(b));
			const cur = this.filters[f.key];
			if (cur && !vals.includes(cur)) vals.push(cur);
			vals.forEach(v => { const o = el('option', null, v); o.value = v; sel.appendChild(o); });
			sel.value = cur || '';
			sel.addEventListener('change', () => {
				if (sel.value) this.filters[f.key] = sel.value; else delete this.filters[f.key];
				this.page = 1;
				this.render();
			});
			this.filterBox.appendChild(sel);
		});
	}

	// ---------- data ----------
	reload(silent) {
		if (!silent) { this.status = 'loading'; this.render(); }
		return this.cfg.load()
			.then(rows => {
				this.rows = rows;
				this.status = 'ready';
				const ids = new Set(rows.map(r => String(this.cfg.id(r))));
				[...this.selected].forEach(i => { if (!ids.has(String(i))) this.selected.delete(i); });
				this.renderFilters();
			})
			.catch(err => {
				console.error(err);
				this.status = 'error';
			})
			.finally(() => {
				this.render();
				if (this.cfg.onChange) this.cfg.onChange(this);
			});
	}

	searchText(row) {
		return this.cfg.columns
			.filter(c => c.search !== false)
			.map(c => String(c.get(row) ?? ''))
			.join(' ')
			.toLowerCase();
	}

	getView() {
		const q = this.q.trim().toLowerCase();
		let r = this.rows.filter(row => {
			if (q && !this.searchText(row).includes(q)) return false;
			for (const f of this.cfg.filters || []) {
				const v = this.filters[f.key];
				if (v && String(f.get(row)) !== v) return false;
			}
			return true;
		});
		if (this.sort.length) {
			r = r.slice().sort((a, b) => {
				for (const s of this.sort) {
					const c = this.cfg.columns.find(c => c.key === s.key);
					const fn = c.sortVal || c.get;
					const av = fn(a), bv = fn(b);
					const d = (typeof av === 'number' && typeof bv === 'number')
						? av - bv
						: String(av ?? '').localeCompare(String(bv ?? ''), undefined, { numeric: true, sensitivity: 'base' });
					if (d) return s.dir === 'desc' ? -d : d;
				}
				return 0;
			});
		}
		return r;
	}

	visibleCols() { return this.cfg.columns.filter(c => !this.hidden.has(c.key)); }
	hasActiveFilters() { return !!this.q.trim() || Object.keys(this.filters).length > 0; }

	clearFilters() {
		this.q = '';
		this.filters = {};
		this.page = 1;
		this.searchInput.value = '';
		this.renderFilters();
		this.render();
	}

	toggleSort(key, multi) {
		const i = this.sort.findIndex(s => s.key === key);
		if (!multi) {
			const cur = (i >= 0 && this.sort.length === 1) ? this.sort[0] : null;
			this.sort = !cur ? [{ key, dir: 'asc' }] : (cur.dir === 'asc' ? [{ key, dir: 'desc' }] : []);
		} else if (i < 0) {
			this.sort.push({ key, dir: 'asc' });
		} else if (this.sort[i].dir === 'asc') {
			this.sort[i].dir = 'desc';
		} else {
			this.sort.splice(i, 1);
		}
		this.page = 1;
		this.render();
	}

	// ---------- rendering ----------
	render() {
		const view = this.getView();
		const pages = Math.max(1, Math.ceil(view.length / this.size));
		if (this.page > pages) this.page = pages;
		if (this.page < 1) this.page = 1;
		const start = (this.page - 1) * this.size;
		this.view = view;
		this.pageRows = view.slice(start, start + this.size);

		this.renderHead();
		this.renderBody();
		this.renderFooter(pages);
		this.renderBulk();
		this.writeUrl();
	}

	renderHead() {
		const tr = document.createElement('tr');

		const thCheck = el('th', 'dt-check');
		const master = document.createElement('input');
		master.type = 'checkbox';
		master.setAttribute('aria-label', 'Select all rows on this page');
		const sel = this.pageRows.filter(r => this.selected.has(this.cfg.id(r))).length;
		master.checked = this.pageRows.length > 0 && sel === this.pageRows.length;
		master.indeterminate = sel > 0 && sel < this.pageRows.length;
		master.addEventListener('change', () => {
			this.pageRows.forEach(r => master.checked ? this.selected.add(this.cfg.id(r)) : this.selected.delete(this.cfg.id(r)));
			this.render();
		});
		thCheck.appendChild(master);
		tr.appendChild(thCheck);

		this.visibleCols().forEach(c => {
			const th = el('th', (c.sticky ? 'sticky-col ' : '') + (c.num ? 'num' : ''));
			const b = el('button', 'dt-sort');
			b.type = 'button';
			b.title = 'Click to sort. Shift+click to add a secondary sort.';
			b.appendChild(el('span', null, c.label));
			const idx = this.sort.findIndex(s => s.key === c.key);
			let arrow = '↕', cls = 'dt-arrow';
			if (idx >= 0) {
				arrow = this.sort[idx].dir === 'asc' ? '▲' : '▼';
				if (this.sort.length > 1) arrow += (idx + 1);
				cls += ' on';
			}
			b.appendChild(el('span', cls, arrow));
			b.addEventListener('click', e => this.toggleSort(c.key, e.shiftKey));
			th.appendChild(b);
			tr.appendChild(th);
		});

		if (this.cfg.rowActions) tr.appendChild(el('th', 'dt-act', ''));
		this.thead.replaceChildren(tr);
	}

	colSpan() { return this.visibleCols().length + 1 + (this.cfg.rowActions ? 1 : 0); }

	messageRow(node) {
		const tr = document.createElement('tr');
		const td = el('td', 'dt-message');
		td.colSpan = this.colSpan();
		td.appendChild(node);
		tr.appendChild(td);
		return tr;
	}

	renderBody() {
		this.tbody.replaceChildren();

		if (this.status === 'loading') {
			for (let i = 0; i < 5; i++) {
				const tr = document.createElement('tr');
				for (let j = 0; j < this.colSpan(); j++) {
					const td = document.createElement('td');
					td.appendChild(el('div', 'sk'));
					tr.appendChild(td);
				}
				this.tbody.appendChild(tr);
			}
			return;
		}

		if (this.status === 'error') {
			const box = el('div', 'dt-state');
			box.append(el('p', 'dt-state-title', 'Could not load data'), el('p', 'muted', 'Check your connection or the server, then try again.'));
			const b = el('button', 'btn btn-primary btn-sm', 'Try again');
			b.type = 'button';
			b.addEventListener('click', () => this.reload());
			box.appendChild(b);
			this.tbody.appendChild(this.messageRow(box));
			return;
		}

		if (this.view.length === 0) {
			const box = el('div', 'dt-state');
			if (this.hasActiveFilters()) {
				box.append(el('p', 'dt-state-title', 'No records match your search'), el('p', 'muted', 'Try different keywords or remove a filter.'));
				const b = el('button', 'btn btn-outline btn-sm', 'Clear filters');
				b.type = 'button';
				b.addEventListener('click', () => this.clearFilters());
				box.appendChild(b);
			} else {
				box.append(el('p', 'dt-state-title', 'Nothing here yet'));
				if (this.cfg.onAdd) {
					const b = el('button', 'btn btn-primary btn-sm', this.cfg.addLabel || 'Add new');
					b.type = 'button';
					b.addEventListener('click', this.cfg.onAdd);
					box.appendChild(b);
				}
			}
			this.tbody.appendChild(this.messageRow(box));
			return;
		}

		const cols = this.visibleCols();
		this.pageRows.forEach(row => {
			const id = this.cfg.id(row);
			const tr = document.createElement('tr');
			tr.dataset.rowId = String(id);
			if (this.selected.has(id)) tr.className = 'selected';

			const tdCheck = el('td', 'dt-check');
			const cb = document.createElement('input');
			cb.type = 'checkbox';
			cb.checked = this.selected.has(id);
			cb.setAttribute('aria-label', 'Select row');
			cb.addEventListener('change', () => {
				if (cb.checked) this.selected.add(id); else this.selected.delete(id);
				this.render();
			});
			tdCheck.appendChild(cb);
			tr.appendChild(tdCheck);

			cols.forEach(c => {
				const td = el('td', (c.sticky ? 'sticky-col ' : '') + (c.num ? 'num' : ''));
				const content = c.render ? c.render(row) : c.get(row);
				if (content instanceof Node) td.appendChild(content);
				else td.textContent = content ?? '';
				tr.appendChild(td);
			});

			if (this.cfg.rowActions) {
				const td = el('td', 'dt-act');
				const k = el('button', 'dt-kebab', '⋮');
				k.type = 'button';
				k.setAttribute('aria-label', 'Row actions');
				k.addEventListener('click', e => {
					e.stopPropagation();
					this.openMenu(k, this.cfg.rowActions(row));
				});
				td.appendChild(k);
				tr.appendChild(td);
			}
			this.tbody.appendChild(tr);
		});

		if (typeof this.cfg.afterRenderBody === 'function') {
			this.cfg.afterRenderBody(this, this.tbody);
		}
	}

	renderFooter(pages) {
		const n = this.view.length, total = this.rows.length;
		if (this.status !== 'ready' || n === 0) {
			this.info.textContent = this.status === 'ready' ? '0 records' : '';
		} else {
			const a = (this.page - 1) * this.size + 1;
			const b = Math.min(this.page * this.size, n);
			this.info.textContent = `Showing ${a}–${b} of ${n}` + (n !== total ? ` (filtered from ${total})` : '');
		}

		this.pager.replaceChildren();
		if (this.status !== 'ready' || pages <= 1) return;

		const add = (label, page, opts = {}) => {
			const b = el('button', 'dt-page' + (opts.active ? ' active' : ''), label);
			b.type = 'button';
			b.disabled = !!opts.disabled;
			if (opts.active) b.setAttribute('aria-current', 'page');
			b.addEventListener('click', () => { this.page = page; this.render(); });
			this.pager.appendChild(b);
		};
		add('‹ Prev', this.page - 1, { disabled: this.page === 1 });
		const set = new Set([1, pages, this.page - 1, this.page, this.page + 1].filter(p => p >= 1 && p <= pages));
		let last = 0;
		[...set].sort((a, b) => a - b).forEach(p => {
			if (p - last > 1) this.pager.appendChild(el('span', 'dt-gap', '…'));
			add(String(p), p, { active: p === this.page });
			last = p;
		});
		add('Next ›', this.page + 1, { disabled: this.page === pages });
	}

	renderBulk() {
		const n = this.selected.size;
		this.bulkBar.hidden = n === 0;
		if (n === 0) return;
		this.bulkBar.replaceChildren(el('strong', null, n + ' selected'));

		if (this.view.some(r => !this.selected.has(this.cfg.id(r)))) {
			const all = el('button', 'btn btn-outline btn-sm', `Select all ${this.view.length} matching`);
			all.type = 'button';
			all.addEventListener('click', () => { this.view.forEach(r => this.selected.add(this.cfg.id(r))); this.render(); });
			this.bulkBar.appendChild(all);
		}
		(this.cfg.bulkActions || []).forEach(a => {
			const b = el('button', 'btn btn-sm ' + (a.danger ? 'btn-danger' : 'btn-outline'), a.label);
			b.type = 'button';
			b.addEventListener('click', () => a.run([...this.selected]));
			this.bulkBar.appendChild(b);
		});
		const clear = el('button', 'btn btn-sm btn-danger-text', 'Clear selection');
		clear.type = 'button';
		clear.addEventListener('click', () => { this.selected.clear(); this.render(); });
		this.bulkBar.appendChild(clear);
	}

	// ---------- menus ----------
	placeMenu(m, anchor) {
		document.body.appendChild(m);
		const r = anchor.getBoundingClientRect();
		let top = r.bottom + 4;
		if (top + m.offsetHeight > window.innerHeight - 8) top = Math.max(8, r.top - m.offsetHeight - 4);
		m.style.top = top + 'px';
		m.style.left = Math.max(8, r.right - m.offsetWidth) + 'px';
	}

	openMenu(anchor, items) {
		closeFloating();
		const m = el('div', 'dt-menu');
		items.filter(Boolean).forEach(it => {
			const b = el('button', 'dt-menu-item' + (it.danger ? ' danger' : ''), it.label);
			b.type = 'button';
			b.addEventListener('click', () => { closeFloating(); it.run(); });
			m.appendChild(b);
		});
		this.placeMenu(m, anchor);
	}

	openColumnsMenu(anchor) {
		closeFloating();
		const m = el('div', 'dt-menu');
		this.cfg.columns.forEach(c => {
			const l = el('label', 'dt-menu-check');
			const cb = document.createElement('input');
			cb.type = 'checkbox';
			cb.checked = !this.hidden.has(c.key);
			cb.addEventListener('change', () => {
				if (cb.checked) this.hidden.delete(c.key); else this.hidden.add(c.key);
				if (this.hidden.size >= this.cfg.columns.length) { this.hidden.delete(c.key); cb.checked = true; }
				this.savePref('cols', [...this.hidden]);
				this.render();
			});
			l.append(cb, document.createTextNode(' ' + c.label));
			m.appendChild(l);
		});
		this.placeMenu(m, anchor);
	}

	// ---------- export / print ----------
	exportRows() {
		return this.selected.size
			? this.rows.filter(r => this.selected.has(this.cfg.id(r)))
			: this.view;
	}

	exportCsv() {
		const rows = this.exportRows();
		if (!rows.length) { toast('Nothing to export.', 'error'); return; }
		const cols = this.visibleCols().filter(c => c.export !== false);
		const lines = [cols.map(c => csvSafe(c.label)).join(',')]
			.concat(rows.map(r => cols.map(c => csvSafe(c.get(r))).join(',')));
		const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' });
		const a = document.createElement('a');
		a.href = URL.createObjectURL(blob);
		a.download = `${this.cfg.key}-${new Date().toISOString().slice(0, 10)}.csv`;
		document.body.appendChild(a);
		a.click();
		a.remove();
		setTimeout(() => URL.revokeObjectURL(a.href), 1000);
		toast(`Exported ${rows.length} row${rows.length === 1 ? '' : 's'}.`, 'success');
	}

	printView() {
		const rows = this.exportRows();
		if (!rows.length) { toast('Nothing to print.', 'error'); return; }
		const cols = this.visibleCols().filter(c => c.export !== false);
		const esc = s => String(s ?? '').replace(/[&<>"']/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]));
		const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>${esc(this.cfg.key)}</title>
<style>body{font-family:Arial,sans-serif;padding:24px}table{border-collapse:collapse;width:100%;font-size:12px}
th,td{border:1px solid #ccc;padding:6px 8px;text-align:left}th{background:#f3f4f6}</style></head><body>
<h2>${esc(this.cfg.key)} (${rows.length})</h2><table><thead><tr>${cols.map(c => `<th>${esc(c.label)}</th>`).join('')}</tr></thead>
<tbody>${rows.map(r => `<tr>${cols.map(c => `<td>${esc(c.get(r))}</td>`).join('')}</tr>`).join('')}</tbody></table></body></html>`;
		const w = window.open('', '_blank');
		if (!w) { toast('Allow pop-ups to print.', 'error'); return; }
		w.document.write(html);
		w.document.close();
		w.focus();
		w.print();
	}
}
