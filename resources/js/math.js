// Render rumus KaTeX: mendukung elemen [data-tex] maupun blok konten [data-math-root] (auto-render $...$ dan $$...$$).
async function renderMath(root = document) {
    const texNodes = root.querySelectorAll('[data-tex]');
    const mathRoots = root.querySelectorAll('[data-math-root]');

    if (texNodes.length === 0 && mathRoots.length === 0) return;

    const [{ default: katex }, { default: renderMathInElement }] = await Promise.all([
        import('katex'),
        import('katex/dist/contrib/auto-render.mjs'),
        import('katex/dist/katex.min.css'),
    ]);

    texNodes.forEach((node) => {
        katex.render(node.dataset.tex, node, {
            displayMode: node.dataset.display === 'block',
            throwOnError: false,
        });
    });

    mathRoots.forEach((container) => {
        renderMathInElement(container, {
            delimiters: [
                { left: '$$', right: '$$', display: true },
                { left: '$', right: '$', display: false },
            ],
            throwOnError: false,
        });
    });
}

window.rfRenderMath = renderMath;
document.addEventListener('DOMContentLoaded', () => renderMath());
