// Render rumus KaTeX untuk elemen ber-atribut data-tex. KaTeX dimuat hanya bila dibutuhkan.
async function renderMath(root = document) {
    const nodes = root.querySelectorAll('[data-tex]');
    if (nodes.length === 0) return;

    const [{ default: katex }] = await Promise.all([
        import('katex'),
        import('katex/dist/katex.min.css'),
    ]);

    nodes.forEach((node) => {
        katex.render(node.dataset.tex, node, {
            displayMode: node.dataset.display === 'block',
            throwOnError: false,
        });
    });
}

window.rfRenderMath = renderMath;
document.addEventListener('DOMContentLoaded', () => renderMath());