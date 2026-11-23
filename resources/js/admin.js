// Konfirmasi hapus dan pratinjau langsung Markdown + KaTeX pada halaman admin (materi dan soal).
function initAdminInteractions() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Yakin ingin menghapus data ini?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    initLessonPreview();
    initQuestionPreview();
}

function initLessonPreview() {
    const lessonForm = document.querySelector('[data-lesson-form]');
    const previewTrigger = document.querySelector('[data-preview-trigger]');
    const previewBody = document.querySelector('[data-preview-body]');
    const previewFormulas = document.querySelector('[data-preview-formulas]');

    if (!lessonForm || !previewTrigger || !previewBody || !previewFormulas) return;

    const previewUrl = lessonForm.getAttribute('data-preview-url');
    const csrfInput = lessonForm.querySelector('input[name="_token"]');
    const bodyInput = lessonForm.querySelector('textarea[name="body"]');
    const formulasInput = lessonForm.querySelector('textarea[name="formulas"]');

    previewTrigger.addEventListener('click', async () => {
        if (!previewUrl || !csrfInput) return;

        previewTrigger.setAttribute('disabled', 'disabled');

        try {
            const response = await window.fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfInput.value,
                },
                body: JSON.stringify({
                    body: bodyInput ? bodyInput.value : '',
                    formulas: formulasInput ? formulasInput.value : '',
                }),
            });

            if (!response.ok) {
                throw new Error('Gagal memuat pratinjau');
            }

            const data = await response.json();
            previewBody.innerHTML = data.body_html || '<p class="rf-muted mb-0">Isi materi masih kosong.</p>';
            previewFormulas.innerHTML = data.formulas_html || '<p class="rf-muted mb-0">Lembar rumus masih kosong.</p>';

            if (typeof window.rfRenderMath === 'function') {
                await window.rfRenderMath(lessonForm);
            }
        } catch {
            if (typeof window.rfToast === 'function') {
                window.rfToast('Pratinjau belum bisa dimuat. Periksa koneksi lalu coba lagi.', 'error');
            }
        } finally {
            previewTrigger.removeAttribute('disabled');
        }
    });
}

function initQuestionPreview() {
    const questionForm = document.querySelector('[data-question-form]');
    const trigger = document.querySelector('[data-question-preview-trigger]');

    if (!questionForm || !trigger) return;

    const previewUrl = questionForm.getAttribute('data-preview-url');
    const csrfInput = questionForm.querySelector('input[name="_token"]');
    const bodyNode = questionForm.querySelector('[data-q-preview-body]');
    const optANode = questionForm.querySelector('[data-q-preview-option-a]');
    const optBNode = questionForm.querySelector('[data-q-preview-option-b]');
    const optCNode = questionForm.querySelector('[data-q-preview-option-c]');
    const optDNode = questionForm.querySelector('[data-q-preview-option-d]');
    const expNode = questionForm.querySelector('[data-q-preview-explanation]');

    trigger.addEventListener('click', async () => {
        if (!previewUrl || !csrfInput) return;

        trigger.setAttribute('disabled', 'disabled');

        try {
            const payload = {
                body: questionForm.querySelector('[name="body"]')?.value || '',
                option_a: questionForm.querySelector('[name="option_a"]')?.value || '',
                option_b: questionForm.querySelector('[name="option_b"]')?.value || '',
                option_c: questionForm.querySelector('[name="option_c"]')?.value || '',
                option_d: questionForm.querySelector('[name="option_d"]')?.value || '',
                explanation: questionForm.querySelector('[name="explanation"]')?.value || '',
            };

            const response = await window.fetch(previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfInput.value,
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                throw new Error('Gagal memuat pratinjau soal');
            }

            const data = await response.json();
            if (bodyNode) bodyNode.innerHTML = data.body_html || '<p class="rf-muted mb-0">Soal masih kosong.</p>';
            if (optANode) optANode.innerHTML = data.option_a_html || '';
            if (optBNode) optBNode.innerHTML = data.option_b_html || '';
            if (optCNode) optCNode.innerHTML = data.option_c_html || '';
            if (optDNode) optDNode.innerHTML = data.option_d_html || '';
            if (expNode) expNode.innerHTML = data.explanation_html || '<p class="rf-muted mb-0">Pembahasan masih kosong.</p>';

            if (typeof window.rfRenderMath === 'function') {
                await window.rfRenderMath(questionForm);
            }
        } catch {
            if (typeof window.rfToast === 'function') {
                window.rfToast('Pratinjau soal belum bisa dimuat. Periksa koneksi lalu coba lagi.', 'error');
            }
        } finally {
            trigger.removeAttribute('disabled');
        }
    });
}

document.addEventListener('DOMContentLoaded', initAdminInteractions);
