const mappingState = {
    data: null,
    currentDetail: null,
    editingOutcomeRow: null,
    editingCriterionRow: null,
    dirty: false,
    period: 'ongoing',
    historyFile: null,
    viewSchoolId: null,
    viewProgramId: null
};

const mappingApiUrl = 'mapping_api.php';

function escapeMappingHtml(value) {
    return String(value == null ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

async function mappingRequest(url, options) {
    const response = await fetch(url, options || { credentials: 'same-origin' });
    let payload;
    try {
        payload = await response.json();
    } catch (error) {
        throw new Error('The mapping service returned an invalid response.');
    }
    if (!response.ok || payload.status !== 'success') {
        throw new Error(payload.message || 'The mapping request failed.');
    }
    return payload.data;
}

async function loadMappingData() {
    mappingState.data = await mappingRequest(mappingApiUrl + '?action=initialize');
    renderStageTwo();
    return mappingState.data;
}

function setMappingMessage(message, isError) {
    let status = document.getElementById('mappingStatus');
    if (!status) {
        status = document.createElement('div');
        status.id = 'mappingStatus';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.style.cssText = 'margin: 0 0 16px; padding: 10px 14px; border-radius: 6px; display: none;';
        const header = document.querySelector('.main-content .page-header');
        if (header && header.parentNode) {
            header.parentNode.insertBefore(status, header.nextSibling);
        }
    }
    status.textContent = message || '';
    status.style.display = message ? 'block' : 'none';
    status.style.backgroundColor = isError ? '#fdecea' : '#e8f5e9';
    status.style.color = isError ? '#b42318' : '#216e39';
}

function showMappingError(error) {
    setMappingMessage(error && error.message ? error.message : 'The mapping request failed.', true);
}

function clearMappingMessage() {
    setMappingMessage('', false);
}

function currentOutcomeVersion(programId) {
    if (!mappingState.data || !programId) return null;
    return mappingState.data.outcome_versions.find(function (version) {
        return !version.is_archived && version.program_id === Number(programId);
    }) || null;
}

function currentAssessmentVersion() {
    if (!mappingState.data) return null;
    return mappingState.data.assessment_versions.find(function (version) {
        return !version.is_archived;
    }) || null;
}

function getSchool(schoolId) {
    return mappingState.data.schools.find(function (school) {
        return school.id === Number(schoolId);
    }) || null;
}

function getProgram(school, programId) {
    if (!school) return null;
    return school.programs.find(function (program) {
        return program.id === Number(programId);
    }) || null;
}

function getProgramLocation(programId) {
    if (!mappingState.data) return null;
    for (const school of mappingState.data.schools) {
        const program = getProgram(school, programId);
        if (program) return { school: school, program: program };
    }
    return null;
}

function ensureMappingView(id, title) {
    let view = document.getElementById(id);
    if (!view) {
        view = document.createElement('div');
        view.id = id;
        view.className = 'mapping-view';
        view.style.display = 'none';
        view.setAttribute('data-title', title);
        const main = document.querySelector('.main-content');
        main.appendChild(view);
    }
    return view;
}

function initializeDynamicMappingViews() {
    ['SOEContent', 'SOMAContent', 'EvalFormContent'].forEach(function (id) {
        const legacyView = document.getElementById(id);
        if (legacyView) legacyView.remove();
    });

    const viewEdit = document.getElementById('viewEditStage');
    if (viewEdit) {
        viewEdit.innerHTML = '';
        viewEdit.style.display = 'none';
    }

    ensureMappingView('dynamicOutcomeContent', 'Course outcomes mapping');
    ensureMappingView('dynamicAssessmentContent', 'Evaluation form');
}

function renderStageTwo() {
    const stage = document.getElementById('stageTwoView');
    if (!stage || !mappingState.data) return;

    const schools = mappingState.data.schools.map(function (school) {
        const programCount = school.programs.length;
        const programSummary = programCount === 1
            ? school.programs[0].name
            : `${programCount} programs`;
        return `
            <div class="landing-box">
                <h2>${escapeMappingHtml(school.name)}</h2>
                <p>${escapeMappingHtml(programSummary)}</p>
                <button type="button" class="view-btn" onclick="showMapping('school_${school.id}')">VIEW OUTCOMES</button>
            </div>
        `;
    }).join('');
    const activeAssessment = currentAssessmentVersion();
    const assessmentLabel = activeAssessment
        ? `Evaluation Form v${activeAssessment.version}`
        : 'No ongoing evaluation form';

    stage.innerHTML = `
        <div class="header-actions">
            <button type="button" class="back-btn-box" onclick="backToStageOne()">&larr; BACK</button>
            <button type="button" id="newMappingBtn" class="add-btn-box" onclick="openNewFileModal()">+ NEW FILE</button>
        </div>
        <div id="selectionGrid" class="card-grid">
            ${schools}
            <div class="landing-box">
                <h2>Industry Partner Evaluation Form</h2>
                <p>${escapeMappingHtml(assessmentLabel)}</p>
                <button type="button" class="view-btn" onclick="showMapping('assessment')">VIEW FORM</button>
            </div>
        </div>
    `;
}

function hideAllStages() {
    ['stageOneView', 'stageTwoView', 'viewEditStage'].forEach(function (id) {
        const element = document.getElementById(id);
        if (element) element.style.display = 'none';
    });
    document.querySelectorAll('.mapping-view').forEach(function (view) {
        view.style.display = 'none';
    });
}

function backToStageOne() {
    hideAllStages();
    const stage = document.getElementById('stageOneView');
    if (stage) stage.style.display = 'block';
    clearMappingMessage();
}

function openStageTwo() {
    hideAllStages();
    if (!mappingState.data) {
        loadMappingData().then(function () {
            document.getElementById('stageTwoView').style.display = 'block';
        }).catch(showMappingError);
        return;
    }
    renderStageTwo();
    document.getElementById('stageTwoView').style.display = 'block';
    clearMappingMessage();
}

function showGrid() {
    openStageTwo();
}

async function showMapping(type, requestedProgramId) {
    hideAllStages();
    clearMappingMessage();
    mappingState.historyFile = null;
    try {
        if (!mappingState.data) await loadMappingData();
        if (type === 'assessment') {
            const view = document.getElementById('dynamicAssessmentContent');
            view.style.display = 'block';
            await renderAssessmentView(view, currentAssessmentVersion() ? currentAssessmentVersion().id : null, false);
            return;
        }
        const schoolId = Number(String(type).replace('school_', ''));
        const school = getSchool(schoolId);
        if (!school) throw new Error('The selected school could not be found.');
        const programId = Number(requestedProgramId) || (school.programs[0] ? school.programs[0].id : 0);
        const version = currentOutcomeVersion(programId);
        const view = document.getElementById('dynamicOutcomeContent');
        view.style.display = 'block';
        await renderOutcomeView(view, school, version ? version.id : null, false, { programId: programId });
    } catch (error) {
        showMappingError(error);
    }
}

function openNewFileModal() {
    const modal = ensureModal('mappingFileModal');
    const programs = mappingState.data.schools.reduce(function (allPrograms, school) {
        return allPrograms.concat(school.programs.map(function (program) {
            return { id: program.id, name: program.name, schoolName: school.name };
        }));
    }, []);
    modal.querySelector('.mapping-modal-body').innerHTML = `
        <h2>New Mapping File</h2>
        <p>Course outcomes are versioned separately for each program. Evaluation forms are shared.</p>
        <label for="newOutcomeProgram">Program:</label>
        <select id="newOutcomeProgram" class="input-box">
            ${programs.map(function (program) {
                return `<option value="${program.id}">${escapeMappingHtml(program.schoolName)} — ${escapeMappingHtml(program.name)}</option>`;
            }).join('')}
        </select>
        <div style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
            <button type="button" class="btn-cancel" onclick="closeMappingModal('mappingFileModal')">Cancel</button>
            <button type="button" class="btn-save" ${programs.length ? '' : 'disabled'} onclick="createMappingFile('outcomes', document.getElementById('newOutcomeProgram').value)">Course Outcomes</button>
            <button type="button" class="btn-submit" onclick="createMappingFile('assessment')">Evaluation Form</button>
        </div>
    `;
    modal.classList.add('active');
}

async function createMappingFile(type, programId) {
    try {
        const requestBody = { action: 'create_file', type: type };
        if (type === 'outcomes') requestBody.program_id = Number(programId);
        const result = await mappingRequest(mappingApiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(requestBody)
        });
        closeMappingModal('mappingFileModal');
        await loadMappingData();
        clearMappingMessage();
        if (type === 'assessment') {
            showMapping('assessment');
        } else {
            const location = getProgramLocation(programId);
            if (location) {
                showMapping('school_' + location.school.id, location.program.id);
            } else {
                setMappingMessage('The outcomes file was created, but its program could not be loaded.', true);
            }
        }
    } catch (error) {
        showMappingError(error);
    }
}

async function fetchOutcomeFile(id) {
    if (!id) return null;
    return mappingRequest(mappingApiUrl + '?action=outcome_file&id=' + encodeURIComponent(id));
}

async function fetchAssessmentFile(id) {
    if (!id) return null;
    return mappingRequest(mappingApiUrl + '?action=assessment_file&id=' + encodeURIComponent(id));
}

async function renderOutcomeView(container, school, fileId, readOnly, options) {
    const settings = options || {};
    let file = null;
    if (fileId) file = await fetchOutcomeFile(fileId);
    const programs = school.programs || [];
    const selectedProgramId = Number(settings.programId) || (programs[0] ? programs[0].id : 0);
    const program = getProgram(school, selectedProgramId);
    const legacyShared = !!file && file.program_id === null;
    const isReadOnly = !!readOnly || legacyShared;
    const mappedStudentOutcomes = file && file.program_so_details
        ? (file.program_so_details[selectedProgramId] || [])
        : [];
    const studentOutcomes = isReadOnly && mappedStudentOutcomes.length
        ? mappedStudentOutcomes
        : (program ? program.student_outcomes : []);
    const activeSoIds = new Set((program ? program.student_outcomes : []).map(function (item) {
        return item.id;
    }));
    const hasStaleMappings = !isReadOnly && mappedStudentOutcomes.some(function (item) {
        return !activeSoIds.has(item.id);
    });
    mappingState.currentDetail = {
        type: 'outcomes',
        id: file ? file.id : null,
        file: file,
        schoolId: school.id,
        programId: selectedProgramId,
        readOnly: isReadOnly,
        legacyShared: legacyShared,
        backToHistory: !!settings.backToHistory,
        containerId: container.id
    };
    mappingState.dirty = false;

    const selectablePrograms = legacyShared
        ? mappingState.data.schools.reduce(function (allPrograms, schoolOption) {
            return allPrograms.concat(schoolOption.programs.map(function (item) {
                return { id: item.id, name: item.name, schoolName: schoolOption.name };
            }));
        }, [])
        : programs.map(function (item) {
            return { id: item.id, name: item.name, schoolName: '' };
        });
    const programSelect = selectablePrograms.length && (!isReadOnly || legacyShared)
        ? `<label for="mappingProgramSelect">Program:</label>
           <select id="mappingProgramSelect" class="input-box" onchange="changeMappingProgram(this.value)">
               ${selectablePrograms.map(function (item) {
                   const label = item.schoolName ? `${item.schoolName} — ${item.name}` : item.name;
                   return `<option value="${item.id}" ${item.id === selectedProgramId ? 'selected' : ''}>${escapeMappingHtml(label)}</option>`;
               }).join('')}
           </select>`
        : '';
    const title = file ? `Course Outcomes Mapping v${file.version}` : 'Course Outcomes Mapping';
    const lockLabel = !file
        ? '<span class="view-only-badge">NO FILE</span>'
        : legacyShared
            ? '<span class="view-only-badge">LEGACY SHARED — READ ONLY</span>'
            : isReadOnly ? '<span class="view-only-badge">READ ONLY</span>' : '<span class="view-only-badge">DRAFT</span>';
    const noFile = !file
        ? `<p>No ongoing outcomes file exists for this program.</p>
           ${selectedProgramId ? `<button type="button" class="add-btn-box" onclick="createMappingFile('outcomes', ${selectedProgramId})">+ CREATE FILE FOR THIS PROGRAM</button>` : ''}`
        : '';
    const legacyNotice = legacyShared
        ? `<p class="mapping-empty-message">This older shared file is read-only. Create a new program file to start with an empty mapping.</p>
           ${!readOnly ? `<button type="button" class="add-btn-box" onclick="createMappingFile('outcomes', ${selectedProgramId})">CREATE NEW PROGRAM FILE</button>` : ''}`
        : '';
    const noStudentOutcomes = studentOutcomes.length === 0
        ? `<p class="mapping-empty-message">${file
            ? 'No Student Outcomes are configured for this program yet. Course outcomes can be saved, but there are no SOs to map.'
            : 'No Student Outcomes are configured for this program yet. Add the program’s Student Outcomes before mapping course outcomes.'}</p>`
        : '';

    container.innerHTML = `
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">${escapeMappingHtml(school.name)} — ${escapeMappingHtml(program ? program.name : '')} — ${escapeMappingHtml(title)}</div>
                ${lockLabel}
            </div>
            <div class="view-header-actions" style="padding: 0 20px; gap: 10px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="back-btn-box" onclick="${settings.backToHistory ? 'backToViewEditFiles()' : 'showGrid()'}">&larr; ${settings.backToHistory ? 'BACK TO FILES' : 'BACK TO MENU'}</button>
                ${!isReadOnly && file ? '<button type="button" class="add-btn-box" style="background-color:#28a745" onclick="openOutcomeModal()">+ ADD COURSE OUTCOME</button>' : ''}
                ${programSelect}
            </div>
            <div class="evaluation-container">
                ${noFile}
                ${legacyNotice}
                ${noStudentOutcomes}
                ${hasStaleMappings
                    ? '<p class="mapping-empty-message">This draft contains links to an older Student Outcomes version. Select the current outcomes and save to replace those links.</p>'
                    : ''}
                <div class="table-responsive">
                    <table class="excel-mapping-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="criteria-col">Course Learning Outcomes</th>
                                <th colspan="${Math.max(studentOutcomes.length, 1)}" class="so-header">Student Outcomes (SO)</th>
                            </tr>
                            <tr>
                                ${studentOutcomes.length
                                    ? studentOutcomes.map(function (item, index) {
                                        return `<th title="${escapeMappingHtml(item.text)}">SO${index + 1}</th>`;
                                    }).join('')
                                    : '<th>Not configured</th>'}
                            </tr>
                        </thead>
                        <tbody id="mappingOutcomeRows">
                            ${file && program ? renderOutcomeRows(file, program, studentOutcomes, isReadOnly) : ''}
                        </tbody>
                    </table>
                </div>
            </div>
            ${!isReadOnly && file ? `
                <div class="panel-footer-actions">
                    <span class="mapping-version-note">This version belongs to ${escapeMappingHtml(program ? program.name : 'this program')} only.</span>
                    <button type="button" class="btn-cancel" onclick="cancelMappingChanges()">CANCEL CHANGES</button>
                    <button type="button" class="btn-save" onclick="saveMapping()">SAVE CHANGES</button>
                    <button type="button" class="btn-submit" onclick="submitMapping()">SUBMIT</button>
                </div>` : ''}
        </div>
    `;
}

function renderOutcomeRows(file, program, studentOutcomes, readOnly) {
    if (!file.outcomes.length) {
        return `<tr data-empty-row><td class="criteria-text" colspan="${studentOutcomes.length + 1}">
            ${readOnly ? 'No course outcomes in this version.' : 'No course outcomes yet. Use “Add Course Outcome” to begin.'}
        </td></tr>`;
    }
    return file.outcomes.map(function (outcome) {
        const mappedIds = outcome.program_so_ids[program.id] || [];
        const actions = readOnly ? '' : `
            <div class="row-actions" style="display:flex; gap:5px; margin-left:10px;">
                <button type="button" onclick="openOutcomeModal(true, this)" style="color:#29429c;font-size:10px;font-weight:bold;">EDIT</button>
                <button type="button" onclick="deleteRow(this)" style="color:#e74c3c;font-size:10px;font-weight:bold;">DEL</button>
            </div>`;
        const checkboxes = studentOutcomes.length
            ? studentOutcomes.map(function (studentOutcome) {
                const checked = mappedIds.indexOf(studentOutcome.id) !== -1 ? 'checked' : '';
                const disabled = readOnly ? 'disabled' : '';
                return `<td class="cell-center"><input type="checkbox" data-so-id="${studentOutcome.id}" ${checked} ${disabled}></td>`;
            }).join('')
            : '<td class="cell-center">—</td>';
        return `
            <tr data-outcome-row data-id="${outcome.id}" ${readOnly ? '' : 'onchange="markMappingDirty()"'}>
                <td class="criteria-text">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                        <span class="criteria-text-content">${escapeMappingHtml(outcome.text)}</span>
                        ${actions}
                    </div>
                </td>
                ${checkboxes}
            </tr>`;
    }).join('');
}

async function changeMappingProgram(programId) {
    if (!mappingState.currentDetail || mappingState.currentDetail.type !== 'outcomes') return;
    if (mappingState.dirty && !confirm('Discard unsaved outcome changes for this program?')) {
        const select = document.getElementById('mappingProgramSelect');
        if (select) select.value = String(mappingState.currentDetail.programId);
        return;
    }
    const detail = mappingState.currentDetail;
    const location = getProgramLocation(programId);
    const school = detail.legacyShared && location ? location.school : getSchool(detail.schoolId);
    try {
        const container = document.getElementById(detail.containerId);
        const version = detail.legacyShared ? detail.file : currentOutcomeVersion(programId);
        await renderOutcomeView(
            container,
            school,
            version ? version.id : null,
            detail.legacyShared,
            { programId: Number(programId), backToHistory: detail.backToHistory }
        );
    } catch (error) {
        showMappingError(error);
    }
}

function markMappingDirty() {
    mappingState.dirty = true;
}

function openOutcomeModal(isEdit, button) {
    const modal = document.getElementById('outcomeModal');
    if (!modal) return;
    mappingState.editingOutcomeRow = isEdit && button ? button.closest('[data-outcome-row]') : null;
    const title = modal.querySelector('#modalTitle');
    const input = modal.querySelector('#outcomeDesc');
    if (!input) return;
    if (title) title.textContent = mappingState.editingOutcomeRow ? 'Edit Course Outcome' : 'Add Course Outcome';
    input.value = mappingState.editingOutcomeRow
        ? mappingState.editingOutcomeRow.querySelector('.criteria-text-content').textContent.trim()
        : '';
    modal.classList.add('active');
    input.focus();
}

function closeOutcomeModal() {
    const modal = document.getElementById('outcomeModal');
    if (modal) modal.classList.remove('active');
}

function saveOutcome() {
    const modal = document.getElementById('outcomeModal');
    const input = modal ? modal.querySelector('#outcomeDesc') : null;
    const text = input ? input.value.trim() : '';
    if (!text) {
        alert('Please enter a description.');
        return;
    }

    if (mappingState.editingOutcomeRow) {
        mappingState.editingOutcomeRow.querySelector('.criteria-text-content').textContent = text;
    } else {
        const program = getProgram(getSchool(mappingState.currentDetail.schoolId), mappingState.currentDetail.programId);
        const checkboxes = program ? program.student_outcomes.map(function (item) {
            return `<td class="cell-center"><input type="checkbox" data-so-id="${item.id}" onchange="markMappingDirty()"></td>`;
        }).join('') : '<td class="cell-center">—</td>';
        const row = document.createElement('tr');
        row.setAttribute('data-outcome-row', '');
        row.setAttribute('data-id', '');
        row.setAttribute('onchange', 'markMappingDirty()');
        row.innerHTML = `
            <td class="criteria-text">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <span class="criteria-text-content">${escapeMappingHtml(text)}</span>
                    <div class="row-actions" style="display:flex;gap:5px;margin-left:10px;">
                        <button type="button" onclick="openOutcomeModal(true,this)" style="color:#29429c;font-size:10px;font-weight:bold;">EDIT</button>
                        <button type="button" onclick="deleteRow(this)" style="color:#e74c3c;font-size:10px;font-weight:bold;">DEL</button>
                    </div>
                </div>
            </td>${checkboxes}`;
        const tbody = document.getElementById('mappingOutcomeRows');
        if (tbody) {
            const emptyRow = tbody.querySelector('[data-empty-row]');
            if (emptyRow) emptyRow.remove();
            tbody.appendChild(row);
        }
    }
    mappingState.dirty = true;
    closeOutcomeModal();
}

function deleteRow(button) {
    const row = button.closest('[data-outcome-row], [data-criterion-row]');
    if (!row || !confirm('Are you sure you want to delete this item?')) return;
    row.remove();
    mappingState.dirty = true;
}

async function saveMapping() {
    const detail = mappingState.currentDetail;
    if (!detail || detail.type !== 'outcomes' || !detail.id || detail.readOnly) {
        showMappingError(new Error('Create an outcomes file before saving.'));
        return;
    }
    const rows = Array.from(document.querySelectorAll(`#${detail.containerId} [data-outcome-row]`));
    const outcomes = rows.map(function (row) {
        return {
            id: row.getAttribute('data-id') ? Number(row.getAttribute('data-id')) : null,
            text: row.querySelector('.criteria-text-content').textContent.trim(),
            so_ids: Array.from(row.querySelectorAll('input[data-so-id]:checked')).map(function (input) {
                return Number(input.getAttribute('data-so-id'));
            })
        };
    });
    try {
        await mappingRequest(mappingApiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'save_outcomes',
                id: detail.id,
                program_id: detail.programId,
                outcomes: outcomes
            })
        });
        await loadMappingData();
        clearMappingMessage();
        const school = getSchool(detail.schoolId);
        const container = document.getElementById(detail.containerId);
        await renderOutcomeView(container, school, detail.id, false, {
            programId: detail.programId,
            backToHistory: detail.backToHistory
        });
        setMappingMessage('Course outcomes and Student Outcome selections saved for this program.', false);
    } catch (error) {
        showMappingError(error);
    }
}

async function cancelMappingChanges() {
    const detail = mappingState.currentDetail;
    if (!detail) return;
    if (mappingState.dirty && !confirm('Discard your unsaved changes?')) return;
    try {
        const school = getSchool(detail.schoolId);
        const container = document.getElementById(detail.containerId);
        await renderOutcomeView(container, school, detail.id, false, {
            programId: detail.programId,
            backToHistory: detail.backToHistory
        });
        clearMappingMessage();
    } catch (error) {
        showMappingError(error);
    }
}

async function submitMapping() {
    const detail = mappingState.currentDetail;
    if (!detail || detail.type !== 'outcomes' || !detail.id || detail.readOnly) return;
    if (mappingState.dirty) {
        alert('Save your changes before submitting this version.');
        return;
    }
    if (!confirm('Submit this program’s outcomes version? It will become read-only for this program.')) return;
    try {
        await mappingRequest(mappingApiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'submit_outcomes',
                id: detail.id,
                program_id: detail.programId
            })
        });
        await loadMappingData();
        if (detail.backToHistory) {
            renderViewEditFiles();
            const stage = document.getElementById('viewEditStage');
            if (stage) stage.style.display = 'block';
        } else {
            showGrid();
        }
        setMappingMessage('This program’s outcomes version was submitted and archived.', false);
    } catch (error) {
        showMappingError(error);
    }
}

async function renderAssessmentView(container, fileId, readOnly, options) {
    const settings = options || {};
    const file = fileId ? await fetchAssessmentFile(fileId) : null;
    mappingState.currentDetail = {
        type: 'assessment',
        id: file ? file.id : null,
        file: file,
        readOnly: !!readOnly,
        containerId: container.id
    };
    mappingState.dirty = false;
    const title = file ? `Evaluation Form v${file.version}` : 'Industry Partner Evaluation Form';
    container.innerHTML = `
        <div class="panel">
            <div class="panel-header">
                <div class="panel-title">${escapeMappingHtml(title)}</div>
                ${readOnly ? '<span class="view-only-badge">READ ONLY</span>' : '<span class="view-only-badge">DRAFT</span>'}
            </div>
            <div class="view-header-actions" style="padding:0 20px;gap:10px;flex-wrap:wrap;">
                <button type="button" class="back-btn-box" onclick="${settings.backToHistory ? 'backToViewEditFiles()' : 'showGrid()'}">&larr; BACK</button>
                ${!readOnly && file ? '<button type="button" class="add-btn-box" style="background-color:#28a745" onclick="openEvalModal()">+ ADD EVALUATION CRITERIA</button>' : ''}
            </div>
            <div class="evaluation-container">
                ${!file ? '<p>No ongoing evaluation form exists. Use <strong>+ NEW FILE</strong> to create one.</p>' : ''}
                <div class="table-responsive">
                    <table class="excel-mapping-table" id="mappingEvalTable">
                        <thead><tr><th>Evaluation Item / Criteria</th><th>Response / Weight</th><th>Notes / Actions</th></tr></thead>
                        <tbody id="mappingEvaluationRows">${file ? renderCriteriaRows(file, readOnly) : ''}</tbody>
                    </table>
                </div>
            </div>
            ${!readOnly && file ? `
                <div class="panel-footer-actions">
                    <button type="button" class="btn-cancel" onclick="cancelAssessmentChanges()">CANCEL</button>
                    <button type="button" class="btn-save" onclick="saveFormToDB()">SAVE FORM STRUCTURE</button>
                    <button type="button" class="btn-save" style="background-color:#29429c;color:white;margin-right:10px;" onclick="openPreview()">PREVIEW FINAL LOOK</button>
                    <button type="button" class="btn-submit" onclick="submitAssessment()">SUBMIT</button>
                </div>` : ''}
        </div>
    `;
}

function renderCriteriaRows(file, readOnly) {
    if (!file.criteria.length) {
        return `<tr data-empty-row><td colspan="3" class="criteria-text">${readOnly ? 'No criteria in this version.' : 'No evaluation criteria yet. Add a criterion to begin.'}</td></tr>`;
    }
    return file.criteria.map(function (item) {
        const response = item.response_type === 'text'
            ? '<textarea class="input-box" rows="2" disabled placeholder="Text response"></textarea>'
            : item.response_type === 'rating'
                ? '<span class="likert-scale">1 &nbsp; 2 &nbsp; 3 &nbsp; 4 &nbsp; 5</span>'
                : '<label><input type="radio" disabled> YES</label> <label><input type="radio" disabled> NO</label>';
        const weightLabel = item.weight == null ? '' : `<br><small>Weight: ${escapeMappingHtml(item.weight)}%</small>`;
        const helpText = item.help_text ? `<br><small>${escapeMappingHtml(item.help_text)}</small>` : '';
        const actions = readOnly ? '' : `
            <div class="row-actions" style="display:flex;gap:5px;">
                <button type="button" onclick="openEvalModal(true,this)" style="color:#29429c;font-size:10px;font-weight:bold;">EDIT</button>
                <button type="button" onclick="deleteRow(this)" style="color:#e74c3c;font-size:10px;font-weight:bold;">DEL</button>
            </div>`;
        return `
            <tr data-criterion-row data-id="${item.id}">
                <td class="criteria-text">
                    <strong class="eval-question">${escapeMappingHtml(item.question)}</strong>${helpText}
                </td>
                <td class="cell-center">${response}${weightLabel}
                    <input class="eval-response-type" type="hidden" value="${escapeMappingHtml(item.response_type)}">
                    <input class="eval-weight" type="hidden" value="${item.weight == null ? '' : escapeMappingHtml(item.weight)}">
                </td>
                <td>
                    <span class="eval-help" hidden>${escapeMappingHtml(item.help_text)}</span>
                    ${actions}
                </td>
            </tr>`;
    }).join('');
}

function ensureModal(id) {
    let modal = document.getElementById(id);
    if (!modal) {
        modal = document.createElement('div');
        modal.id = id;
        modal.className = 'modal-overlay';
        modal.innerHTML = '<div class="task-modal mapping-modal-body"></div>';
        document.body.appendChild(modal);
    }
    return modal;
}

function closeMappingModal(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
}

function openEvalModal(isEdit, button) {
    const modal = ensureModal('mappingCriteriaModal');
    mappingState.editingCriterionRow = isEdit && button ? button.closest('[data-criterion-row]') : null;
    const row = mappingState.editingCriterionRow;
    const question = row ? row.querySelector('.eval-question').textContent.trim() : '';
    const helpText = row ? row.querySelector('.eval-help').textContent.trim() : '';
    const responseType = row ? row.querySelector('.eval-response-type').value : 'yes_no';
    const weight = row ? row.querySelector('.eval-weight').value : '';
    modal.querySelector('.mapping-modal-body').innerHTML = `
        <span class="modal-close" onclick="closeMappingModal('mappingCriteriaModal')">&times;</span>
        <h2>${row ? 'Edit Evaluation Criterion' : 'Add Evaluation Criterion'}</h2>
        <div class="form-row"><label for="criterionQuestion">Question / Criterion:</label>
            <textarea id="criterionQuestion" class="input-box" rows="3" maxlength="10000">${escapeMappingHtml(question)}</textarea>
        </div>
        <div class="form-row"><label for="criterionHelpText">Prompt / Help Text:</label>
            <textarea id="criterionHelpText" class="input-box" rows="3" maxlength="10000">${escapeMappingHtml(helpText)}</textarea>
        </div>
        <div class="form-row"><label for="criterionResponseType">Response Type:</label>
            <select id="criterionResponseType" class="input-box">
                <option value="yes_no" ${responseType === 'yes_no' ? 'selected' : ''}>Yes / No</option>
                <option value="rating" ${responseType === 'rating' ? 'selected' : ''}>1–5 Rating</option>
                <option value="text" ${responseType === 'text' ? 'selected' : ''}>Text Response</option>
            </select>
        </div>
        <div class="form-row"><label for="criterionWeight">Weight (optional, 0–100):</label>
            <input id="criterionWeight" class="input-box" type="number" min="0" max="100" step="0.01" value="${escapeMappingHtml(weight)}">
        </div>
        <div style="display:flex;justify-content:flex-end;gap:10px;">
            <button type="button" class="btn-cancel" onclick="closeMappingModal('mappingCriteriaModal')">Cancel</button>
            <button type="button" class="btn-submit" onclick="saveEvaluationCriterion()">Save Criterion</button>
        </div>
    `;
    modal.classList.add('active');
}

function saveEvaluationCriterion() {
    const modal = document.getElementById('mappingCriteriaModal');
    const question = modal.querySelector('#criterionQuestion').value.trim();
    if (!question) {
        alert('Please enter a question or criterion.');
        return;
    }
    const helpText = modal.querySelector('#criterionHelpText').value.trim();
    const responseType = modal.querySelector('#criterionResponseType').value;
    const weight = modal.querySelector('#criterionWeight').value;
    const row = mappingState.editingCriterionRow;
    if (row) {
        row.querySelector('.eval-question').textContent = question;
        row.querySelector('.eval-help').textContent = helpText;
        row.querySelector('.eval-response-type').value = responseType;
        row.querySelector('.eval-weight').value = weight;
        const questionCell = row.querySelector('.criteria-text');
        questionCell.innerHTML = `<strong class="eval-question">${escapeMappingHtml(question)}</strong>${helpText ? `<br><small>${escapeMappingHtml(helpText)}</small>` : ''}`;
        const responseCell = row.children[1];
        const weightLabel = weight === '' ? '' : `<br><small>Weight: ${escapeMappingHtml(weight)}%</small>`;
        responseCell.innerHTML = renderResponseInput(responseType) + weightLabel
            + `<input class="eval-response-type" type="hidden" value="${escapeMappingHtml(responseType)}">
               <input class="eval-weight" type="hidden" value="${escapeMappingHtml(weight)}">`;
        row.querySelector('.eval-help').textContent = helpText;
        row.querySelector('.eval-help').hidden = true;
    } else {
        const tbody = document.getElementById('mappingEvaluationRows');
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-criterion-row', '');
        newRow.setAttribute('data-id', '');
        newRow.innerHTML = `
            <td class="criteria-text"><strong class="eval-question">${escapeMappingHtml(question)}</strong>
                ${helpText ? `<br><small>${escapeMappingHtml(helpText)}</small>` : ''}
            </td>
            <td class="cell-center">${renderResponseInput(responseType)}
                ${weight === '' ? '' : `<br><small>Weight: ${escapeMappingHtml(weight)}%</small>`}
                <input class="eval-response-type" type="hidden" value="${escapeMappingHtml(responseType)}">
                <input class="eval-weight" type="hidden" value="${escapeMappingHtml(weight)}">
            </td>
            <td><span class="eval-help" hidden>${escapeMappingHtml(helpText)}</span>
                <div class="row-actions" style="display:flex;gap:5px;">
                    <button type="button" onclick="openEvalModal(true,this)" style="color:#29429c;font-size:10px;font-weight:bold;">EDIT</button>
                    <button type="button" onclick="deleteRow(this)" style="color:#e74c3c;font-size:10px;font-weight:bold;">DEL</button>
                </div>
            </td>`;
        if (tbody) {
            const emptyRow = tbody.querySelector('[data-empty-row]');
            if (emptyRow) emptyRow.remove();
            tbody.appendChild(newRow);
        }
    }
    mappingState.dirty = true;
    closeMappingModal('mappingCriteriaModal');
}

function renderResponseInput(type) {
    if (type === 'text') return '<textarea class="input-box" rows="2" disabled placeholder="Text response"></textarea>';
    if (type === 'rating') return '<span class="likert-scale">1 &nbsp; 2 &nbsp; 3 &nbsp; 4 &nbsp; 5</span>';
    return '<label><input type="radio" disabled> YES</label> <label><input type="radio" disabled> NO</label>';
}

async function saveFormToDB() {
    const detail = mappingState.currentDetail;
    if (!detail || detail.type !== 'assessment' || !detail.id) {
        showMappingError(new Error('Create an evaluation form before saving.'));
        return;
    }
    const rows = Array.from(document.querySelectorAll(`#${detail.containerId} [data-criterion-row]`));
    const criteria = rows.map(function (row) {
        return {
            id: row.getAttribute('data-id') ? Number(row.getAttribute('data-id')) : null,
            question: row.querySelector('.eval-question').textContent.trim(),
            help_text: row.querySelector('.eval-help').textContent.trim(),
            response_type: row.querySelector('.eval-response-type').value,
            weight: row.querySelector('.eval-weight').value
        };
    });
    try {
        await mappingRequest(mappingApiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'save_assessment', id: detail.id, criteria: criteria })
        });
        await loadMappingData();
        await renderAssessmentView(document.getElementById(detail.containerId), detail.id, false, { backToHistory: detail.containerId === 'viewEditDetail' });
        clearMappingMessage();
        setMappingMessage('Evaluation form saved.', false);
    } catch (error) {
        showMappingError(error);
    }
}

async function cancelAssessmentChanges() {
    const detail = mappingState.currentDetail;
    if (!detail) return;
    if (mappingState.dirty && !confirm('Discard your unsaved changes?')) return;
    try {
        await renderAssessmentView(document.getElementById(detail.containerId), detail.id, false, { backToHistory: detail.containerId === 'viewEditDetail' });
        clearMappingMessage();
    } catch (error) {
        showMappingError(error);
    }
}

function openPreview() {
    const detail = mappingState.currentDetail;
    if (!detail || detail.type !== 'assessment') return;
    const rows = Array.from(document.querySelectorAll(`#${detail.containerId} [data-criterion-row]`));
    const modal = ensureModal('mappingPreviewModal');
    modal.querySelector('.mapping-modal-body').innerHTML = `
        <span class="modal-close" onclick="closeMappingModal('mappingPreviewModal')">&times;</span>
        <h2>Evaluation Form Preview</h2>
        <div class="table-responsive">
            <table class="excel-mapping-table">
                <thead><tr><th>Evaluation Item / Criteria</th><th>Response</th><th>Notes</th></tr></thead>
                <tbody>${rows.map(function (row) {
                    return `<tr>
                        <td>${escapeMappingHtml(row.querySelector('.eval-question').textContent)}
                            <br><small>${escapeMappingHtml(row.querySelector('.eval-help').textContent)}</small></td>
                        <td>${escapeMappingHtml(row.querySelector('.eval-response-type').value)}
                            ${row.querySelector('.eval-weight').value ? ` (${escapeMappingHtml(row.querySelector('.eval-weight').value)}%)` : ''}
                        </td>
                        <td><textarea class="input-box" rows="2" disabled></textarea></td>
                    </tr>`;
                }).join('')}</tbody>
            </table>
        </div>
        <div style="display:flex;justify-content:flex-end;">
            <button type="button" class="btn-cancel" onclick="closeMappingModal('mappingPreviewModal')">Close</button>
        </div>
    `;
    modal.classList.add('active');
}

async function submitAssessment() {
    const detail = mappingState.currentDetail;
    if (!detail || detail.type !== 'assessment' || !detail.id) return;
    if (mappingState.dirty) {
        alert('Save your changes before submitting this form.');
        return;
    }
    if (!confirm('Submit this evaluation form? It will become read-only.')) return;
    try {
        await mappingRequest(mappingApiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'submit_assessment', id: detail.id })
        });
        await loadMappingData();
        showGrid();
        setMappingMessage('Evaluation form submitted and archived.', false);
    } catch (error) {
        showMappingError(error);
    }
}

async function openViewEdit(periodLabel) {
    hideAllStages();
    mappingState.historyFile = null;
    mappingState.period = String(periodLabel).toLowerCase().indexOf('previous') !== -1 ? 'previous' : 'ongoing';
    mappingState.viewSchoolId = null;
    mappingState.viewProgramId = null;
    const stage = document.getElementById('viewEditStage');
    if (!stage) return;
    stage.style.display = 'block';
    try {
        if (!mappingState.data) await loadMappingData();
        renderViewEditFiles();
        clearMappingMessage();
    } catch (error) {
        showMappingError(error);
    }
}

function renderViewEditFiles() {
    const stage = document.getElementById('viewEditStage');
    const archived = mappingState.period === 'previous';
    const label = archived ? 'PREVIOUS YEARS — READ ONLY' : 'ONGOING — EDITABLE';
    const outcomes = (mappingState.data ? mappingState.data.outcome_versions : []).filter(function (item) {
        return item.is_archived === archived && item.program_id !== null;
    });
    const assessments = (mappingState.data ? mappingState.data.assessment_versions : []).filter(function (item) {
        return item.is_archived === archived;
    });
    const school = mappingState.viewSchoolId
        ? getSchool(mappingState.viewSchoolId)
        : null;
    const program = school && mappingState.viewProgramId
        ? getProgram(school, mappingState.viewProgramId)
        : null;
    const cards = renderProgramHistoryCards(outcomes, assessments, archived, school, program);
    const hierarchyNavigation = school
        ? `<div id="viewEditHierarchyNavigation" class="header-actions">
               <button type="button" class="back-btn-box" onclick="${program ? 'backToViewEditProgramList()' : 'backToViewEditSchoolList()'}">&larr; BACK TO ${program ? 'PROGRAMS' : 'SCHOOLS'}</button>
               <span>${escapeMappingHtml(school.name)}${program ? ` — ${escapeMappingHtml(program.name)}` : ''}</span>
           </div>`
        : '';

    stage.innerHTML = `
        <div class="header-actions">
            <button type="button" class="back-btn-box" onclick="backToStageOne()">&larr; BACK</button>
            <span class="view-only-badge">${label}</span>
        </div>
        ${hierarchyNavigation}
        <div id="viewEditFiles" class="card-grid">
            ${cards || `<p class="mapping-empty-message">No ${archived ? 'previous' : 'ongoing'} files are available.</p>`}
        </div>
        <div id="viewEditDetail" class="mapping-view" style="display:none;"></div>
    `;
}

function renderProgramHistoryCards(outcomes, assessments, archived, school, program) {
    if (program) {
        const versions = outcomes.filter(function (item) {
            return item.program_id === program.id;
        });
        if (!versions.length) {
            return `<p class="mapping-empty-message">No ${archived ? 'previous' : 'ongoing'} versions are available for this program.</p>`;
        }
        return versions.map(function (item) {
            return `
                <div class="landing-box">
                    <h2>Version ${item.version}</h2>
                    <p>${item.item_count} ${item.item_count === 1 ? 'outcome' : 'outcomes'} · ${escapeMappingHtml(item.created_at || '')}</p>
                    <button type="button" class="view-btn" onclick="openVersionDetails('outcomes',${item.id},${archived})">OPEN ${archived ? 'VERSION' : 'DRAFT'}</button>
                </div>`;
        }).join('');
    }

    if (school) {
        return school.programs.map(function (item) {
            const versionCount = outcomes.filter(function (outcome) {
                return outcome.program_id === item.id;
            }).length;
            return `
                <div class="landing-box">
                    <h2>${escapeMappingHtml(item.name)}</h2>
                    <p>${versionCount} ${versionCount === 1 ? 'version' : 'versions'}</p>
                    <button type="button" class="view-btn" onclick="openViewEditProgram(${item.id})">VIEW VERSIONS</button>
                </div>`;
        }).join('');
    }

    const schools = mappingState.data.schools.map(function (item) {
        const programIds = new Set(item.programs.map(function (programItem) {
            return programItem.id;
        }));
        const versionCount = outcomes.filter(function (outcome) {
            return programIds.has(outcome.program_id);
        }).length;
        return `
            <div class="landing-box">
                <h2>${escapeMappingHtml(item.name)}</h2>
                <p>${item.programs.length} ${item.programs.length === 1 ? 'program' : 'programs'} · ${versionCount} ${versionCount === 1 ? 'version' : 'versions'}</p>
                <button type="button" class="view-btn" onclick="openViewEditSchool(${item.id})">VIEW PROGRAMS</button>
            </div>`;
    });
    const assessmentCards = assessments.map(function (item) {
        return `
            <div class="landing-box">
                <h2>Evaluation Form v${item.version}</h2>
                <p>${item.item_count} criteria · ${escapeMappingHtml(item.created_at || '')}</p>
                <button type="button" class="view-btn" onclick="openVersionDetails('assessment',${item.id},${archived})">OPEN ${archived ? 'VERSION' : 'DRAFT'}</button>
            </div>`;
    });
    return schools.concat(assessmentCards).join('');
}

function openViewEditSchool(schoolId) {
    mappingState.viewSchoolId = Number(schoolId);
    mappingState.viewProgramId = null;
    renderViewEditFiles();
}

function openViewEditProgram(programId) {
    mappingState.viewProgramId = Number(programId);
    renderViewEditFiles();
}

function backToViewEditSchoolList() {
    mappingState.viewSchoolId = null;
    mappingState.viewProgramId = null;
    renderViewEditFiles();
}

function backToViewEditProgramList() {
    mappingState.viewProgramId = null;
    renderViewEditFiles();
}

async function openVersionDetails(type, id, readOnly) {
    const list = document.getElementById('viewEditFiles');
    const detail = document.getElementById('viewEditDetail');
    const hierarchyNavigation = document.getElementById('viewEditHierarchyNavigation');
    if (list) list.style.display = 'none';
    if (detail) detail.style.display = 'block';
    if (hierarchyNavigation) hierarchyNavigation.style.display = 'none';
    try {
        mappingState.historyFile = { type: type, id: Number(id), readOnly: !!readOnly };
        if (type === 'assessment') {
            await renderAssessmentView(detail, id, readOnly, { backToHistory: true });
            return;
        }
        const data = await fetchOutcomeFile(id);
        let location = null;
        if (data.program_id !== null) {
            location = getProgramLocation(data.program_id);
            if (!location) throw new Error('The program for this outcomes version could not be found.');
        } else {
            const firstSchool = mappingState.data.schools[0];
            const firstProgram = firstSchool && firstSchool.programs[0];
            if (!firstProgram) throw new Error('No programs are configured.');
            location = { school: firstSchool, program: firstProgram };
        }
        await renderOutcomeView(detail, location.school, id, readOnly, {
            programId: location.program.id,
            backToHistory: true
        });
    } catch (error) {
        showMappingError(error);
    }
}

function backToViewEditFiles() {
    if (mappingState.dirty && !confirm('Discard your unsaved changes?')) return;
    mappingState.currentDetail = null;
    const detail = document.getElementById('viewEditDetail');
    const files = document.getElementById('viewEditFiles');
    const hierarchyNavigation = document.getElementById('viewEditHierarchyNavigation');
    if (detail) {
        detail.style.display = 'none';
        detail.innerHTML = '';
    }
    if (files) files.style.display = '';
    if (hierarchyNavigation) hierarchyNavigation.style.display = '';
    mappingState.dirty = false;
    mappingState.historyFile = null;
}

function backToViewEditSchools() {
    backToViewEditFiles();
}

function showReadOnlySchool() {
    backToViewEditFiles();
}

function initializeMappingPage() {
    initializeDynamicMappingViews();
    const modal = document.getElementById('outcomeModal');
    if (modal) {
        const saveButton = modal.querySelector('#saveOutcomeBtn');
        if (saveButton) saveButton.onclick = saveOutcome;
    }
    loadMappingData().catch(showMappingError);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMappingPage);
} else {
    initializeMappingPage();
}
