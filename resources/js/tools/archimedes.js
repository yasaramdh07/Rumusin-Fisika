// Modul 5: Simulasi & Kalkulator Hukum Archimedes (Gaya Apung & Massa Jenis) — dari legacy/page/rumus-archimedes.

export function computeGayaApung({ fa, rho, v }) {
    const g = 9.8;
    const blanks = [Number.isNaN(fa), Number.isNaN(rho), Number.isNaN(v)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rFa = fa;
    let rRho = rho;
    let rV = v;
    if (Number.isNaN(fa)) rFa = rho * g * v;
    else if (Number.isNaN(rho)) rRho = fa / (g * v);
    else rV = fa / (rho * g);

    return {
        fa: rFa,
        rho: rRho,
        v: rV,
        faText: `${rFa.toFixed(2)} N`,
        rhoText: `${rRho.toFixed(2)} kg/m³`,
        vText: `${rV.toFixed(4)} m³`,
    };
}

export function computeMassaJenis({ rho, m, v }) {
    const blanks = [Number.isNaN(rho), Number.isNaN(m), Number.isNaN(v)].filter(Boolean).length;
    if (blanks !== 1) {
        return { error: 'Kosongkan tepat 1 nilai yang ingin dicari.' };
    }

    let rRho = rho;
    let rM = m;
    let rV = v;
    if (Number.isNaN(rho)) rRho = m / v;
    else if (Number.isNaN(m)) rM = rho * v;
    else rV = m / rho;

    return {
        rho: rRho,
        m: rM,
        v: rV,
        rhoText: `${rRho.toFixed(2)} kg/m³`,
        mText: `${rM.toFixed(2)} kg`,
        vText: `${rV.toFixed(4)} m³`,
    };
}

export function mountArchimedesTool(container) {
    container.innerHTML = `
        <div class="rf-tool" data-tool-archimedes>
            <div class="rf-tool__block">
                <div class="rf-tool__head">
                    <h3 class="h4 mb-0">Kalkulator Hukum Archimedes</h3>
                    <div class="rf-chips" role="group" aria-label="Mode kalkulator Archimedes">
                        <button type="button" class="rf-chip is-active" data-a-tab="fa" aria-pressed="true"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Gaya Apung (Fa = ρ · g · V)</button>
                        <button type="button" class="rf-chip" data-a-tab="rho" aria-pressed="false"><svg class="rf-icon rf-chip__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 6 9 17l-5-5"/></svg> Massa Jenis (ρ = m / V)</button>
                    </div>
                </div>

                <div data-a-panel="fa">
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fa-fa">Gaya Apung Fa (N)</label>
                            <input class="rf-input rf-num" id="rf-fa-fa" type="number" step="any" data-fa-fa>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fa-rho">ρ Fluida (kg/m³)</label>
                            <input class="rf-input rf-num" id="rf-fa-rho" type="number" step="any" data-fa-rho>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-fa-v">Volume Tercelup V (m³)</label>
                            <input class="rf-input rf-num" id="rf-fa-v" type="number" step="any" data-fa-v>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-fa-calc>Hitung Gaya Apung</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-fa-res>Kosongkan satu nilai, lalu klik Hitung Gaya Apung (g = 9.8 m/s²).</div>
                    </div>
                </div>

                <div data-a-panel="rho" hidden>
                    <div class="rf-tool__grid-3">
                        <div class="rf-field">
                            <label class="rf-label" for="rf-rho-rho">Massa Jenis ρ (kg/m³)</label>
                            <input class="rf-input rf-num" id="rf-rho-rho" type="number" step="any" data-rho-rho>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-rho-m">Massa m (kg)</label>
                            <input class="rf-input rf-num" id="rf-rho-m" type="number" step="any" data-rho-m>
                        </div>
                        <div class="rf-field">
                            <label class="rf-label" for="rf-rho-v">Volume V (m³)</label>
                            <input class="rf-input rf-num" id="rf-rho-v" type="number" step="any" data-rho-v>
                        </div>
                    </div>
                    <div class="rf-tool__actions mt-3">
                        <button type="button" class="rf-btn rf-btn--secondary" data-rho-calc>Hitung Massa Jenis</button>
                    </div>
                    <div class="rf-sheet mt-4">
                        <h4 class="rf-sheet__title">Hasil di Lembar Rumus</h4>
                        <div class="rf-sheet__paper rf-num" data-rho-res>Kosongkan satu nilai, lalu klik Hitung Massa Jenis.</div>
                    </div>
                </div>
            </div>
        </div>
    `;

    container.querySelectorAll('[data-a-tab]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const key = tab.getAttribute('data-a-tab');
            container.querySelectorAll('[data-a-tab]').forEach((other) => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            container.querySelectorAll('[data-a-panel]').forEach((p) => {
                if (p.getAttribute('data-a-panel') === key) p.removeAttribute('hidden');
                else p.setAttribute('hidden', 'hidden');
            });
        });
    });

    container.querySelector('[data-fa-calc]')?.addEventListener('click', () => {
        const res = computeGayaApung({
            fa: parseFloat(container.querySelector('[data-fa-fa]')?.value || ''),
            rho: parseFloat(container.querySelector('[data-fa-rho]')?.value || ''),
            v: parseFloat(container.querySelector('[data-fa-v]')?.value || ''),
        });
        const box = container.querySelector('[data-fa-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Fa</span><strong>${res.faText}</strong></div><div><span>ρ Fluida</span><strong>${res.rhoText}</strong></div><div><span>Volume (V)</span><strong>${res.vText}</strong></div></div>`;
    });

    container.querySelector('[data-rho-calc]')?.addEventListener('click', () => {
        const res = computeMassaJenis({
            rho: parseFloat(container.querySelector('[data-rho-rho]')?.value || ''),
            m: parseFloat(container.querySelector('[data-rho-m]')?.value || ''),
            v: parseFloat(container.querySelector('[data-rho-v]')?.value || ''),
        });
        const box = container.querySelector('[data-rho-res]');
        if (!box) return;
        if (res.error) {
            box.innerHTML = `<span class="rf-error">${res.error}</span>`;
            return;
        }
        box.innerHTML = `<div class="rf-calc-outcomes"><div><span>Massa Jenis (ρ)</span><strong>${res.rhoText}</strong></div><div><span>Massa (m)</span><strong>${res.mText}</strong></div><div><span>Volume (V)</span><strong>${res.vText}</strong></div></div>`;
    });
}
