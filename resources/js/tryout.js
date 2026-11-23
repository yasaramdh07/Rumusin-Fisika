// Interaksi halaman pengerjaan try out (/tryout/percobaan/{attempt}):
// pemilihan opsi jawaban (clay kuning + centang), pembaruan peta soal, dan timer tampilan.
function initTryoutAttempt() {
    const container = document.querySelector('[data-tryout-attempt]');
    if (!container) return;

    const progressNode = container.querySelector('[data-tryout-progress]');
    const questionBlocks = container.querySelectorAll('[data-question-block]');

    function updateProgressAndMap() {
        let answeredCount = 0;

        questionBlocks.forEach((block) => {
            const number = block.getAttribute('data-question-number');
            const checkedInput = block.querySelector('[data-option-input]:checked');
            const mapDot = container.querySelector(`[data-map-dot="${number}"]`);

            block.querySelectorAll('[data-option-tile]').forEach((tile) => {
                const input = tile.querySelector('[data-option-input]');
                tile.classList.toggle('is-selected', Boolean(input && input.checked));
            });

            if (checkedInput) {
                answeredCount += 1;
                mapDot?.classList.add('is-answered');
            } else {
                mapDot?.classList.remove('is-answered');
            }
        });

        if (progressNode) {
            progressNode.textContent = String(answeredCount);
        }
    }

    container.querySelectorAll('[data-option-input]').forEach((input) => {
        input.addEventListener('change', updateProgressAndMap);
    });

    updateProgressAndMap();

    // Timer tampilan (penilaian batas waktu sebenarnya tetap dihitung di server).
    const timerBox = container.querySelector('[data-tryout-timer]');
    const clockNode = container.querySelector('[data-tryout-clock]');
    if (!timerBox || !clockNode) return;

    let remaining = parseInt(timerBox.getAttribute('data-remaining-seconds') || '0', 10);
    if (Number.isNaN(remaining) || remaining < 0) {
        remaining = 0;
    }

    let warned = false;

    const formatClock = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    };

    const tick = () => {
        clockNode.textContent = formatClock(remaining);
        timerBox.classList.toggle('is-expired', remaining === 0);

        if (remaining === 0 && !warned) {
            warned = true;
            if (typeof window.rfToast === 'function') {
                window.rfToast('Waktu habis. Segera kirim jawabanmu agar tetap dinilai.', 'info');
            }
            return;
        }

        if (remaining > 0) {
            remaining -= 1;
        }
    };

    tick();
    window.setInterval(tick, 1000);
}

document.addEventListener('DOMContentLoaded', initTryoutAttempt);
