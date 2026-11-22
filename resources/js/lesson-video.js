// Pergantian tahapan video pada halaman materi (/materi/{slug}).
function initLessonVideo() {
    const player = document.querySelector('[data-video-player]');
    const titleNode = document.querySelector('[data-video-current-title]');
    const stepButtons = document.querySelectorAll('[data-video-step]');

    if (!player || stepButtons.length === 0) return;

    stepButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const youtubeId = btn.dataset.youtubeId;
            const stepTitle = btn.dataset.videoTitle || '';
            const stepPosition = btn.dataset.videoPosition || '';

            if (!youtubeId) return;

            stepButtons.forEach((other) => {
                other.classList.remove('is-active');
                other.removeAttribute('aria-current');
            });

            btn.classList.add('is-active');
            btn.setAttribute('aria-current', 'step');

            const nextSrc = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(youtubeId)}?rel=0`;
            if (player.getAttribute('src') !== nextSrc) {
                player.setAttribute('src', nextSrc);
            }
            player.setAttribute('title', `Video tahapan ${stepPosition}: ${stepTitle}`);

            if (titleNode) {
                titleNode.textContent = `Tahapan ${stepPosition}: ${stepTitle}`;
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initLessonVideo);
