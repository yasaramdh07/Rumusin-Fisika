// Konfirmasi hapus dan pratinjau langsung Markdown + KaTeX pada halaman admin.
function initAdminInteractions() {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Yakin ingin menghapus data ini?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

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

document.addEventListener('DOMContentLoaded', initAdminInteractions);
