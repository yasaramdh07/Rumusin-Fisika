<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class PhysicsCalculatorTest extends TestCase
{
    /**
     * Uji 15 kalkulator (3 kasus uji per kalkulator = 45 kasus uji) di resources/js/tools/
     * dan pastikan hasilnya identik dengan rumus versi lama (legacy/).
     */
    public function test_all_fifteen_calculators_match_legacy_results_across_three_cases_each(): void
    {
        $script = <<<'JS'
import assert from 'node:assert/strict';
import { computeGLB, computeGLBB } from './resources/js/tools/gerak.js';
import { computeOhmListrik, computeDayaListrik, computeTagihanRumahan } from './resources/js/tools/listrik.js';
import { computeFMA, computeBerat, computeResultanGaya } from './resources/js/tools/gaya.js';
import { computeEnergiKinetik, computeEnergiPotensial, computeUsaha } from './resources/js/tools/energi.js';
import { computeGayaApung, computeMassaJenis } from './resources/js/tools/archimedes.js';
import { computeOhmElektronika, computeResistorPengganti } from './resources/js/tools/elektronika.js';

// 1. GLB (3 kasus)
assert.equal(computeGLB({ s: NaN, v: 15, t: 8 }).sText, '120.000 m');
assert.equal(computeGLB({ s: 250, v: NaN, t: 10 }).vText, '25.000 m/s');
assert.equal(computeGLB({ s: 180, v: 12, t: NaN }).tText, '15.000 s');

// 2. GLBB (3 kasus)
const glbb1 = computeGLBB({ v0: 0, a: 4, t: 5, sIn: NaN });
assert.equal(glbb1.vFinalText, '20.000 m/s');
assert.equal(glbb1.sFinalText, '50.000 m');
assert.equal(glbb1.v2Text, '20.000 m/s');

const glbb2 = computeGLBB({ v0: 10, a: 2, t: 6, sIn: NaN });
assert.equal(glbb2.vFinalText, '22.000 m/s');
assert.equal(glbb2.sFinalText, '96.000 m');
assert.equal(glbb2.v2Text, '22.000 m/s');

const glbb3 = computeGLBB({ v0: 30, a: -3, t: 4, sIn: 96 });
assert.equal(glbb3.vFinalText, '18.000 m/s');
assert.equal(glbb3.sFinalText, '96.000 m');
assert.equal(glbb3.v2Text, '18.000 m/s');

// 3. Hukum Ohm Listrik (3 kasus)
assert.equal(computeOhmListrik({ v: NaN, i: 2.2, r: 100 }).vText, '220.0 V');
assert.equal(computeOhmListrik({ v: 12, i: NaN, r: 4 }).iText, '3.000 A');
assert.equal(computeOhmListrik({ v: 220, i: 0.5, r: NaN }).rText, '440.0 Ω');

// 4. Daya Listrik (3 kasus)
assert.equal(computeDayaListrik({ v: 220, i: 2, r: NaN }).kwText, '0.44 kW');
assert.equal(computeDayaListrik({ v: NaN, i: 3, r: 50 }).kwText, '0.45 kW');
assert.equal(computeDayaListrik({ v: 220, i: NaN, r: 100 }).kwText, '0.48 kW');

// 5. Tagihan Listrik Rumahan (3 kasus)
const rum1 = computeTagihanRumahan({
    alatList: [{ nama: 'Lampu', watt: 100, jam: 10 }],
    tarifKwh: 1447,
    biayaTetap: 12500,
});
assert.equal(rum1.totalKwhText, '30.0 kWh');
assert.ok(Math.abs(rum1.grandTotal - 62060.1) < 1e-6);

const rum2 = computeTagihanRumahan({
    alatList: [
        { nama: 'Kipas', watt: 50, jam: 8 },
        { nama: 'AC', watt: 400, jam: 5 },
    ],
    tarifKwh: 1447,
    biayaTetap: 12500,
});
assert.equal(rum2.totalKwhText, '72.0 kWh');
assert.ok(Math.abs(rum2.grandTotal - 129519.24) < 1e-6);

const rum3 = computeTagihanRumahan({
    alatList: [
        { nama: 'Kipas Angin', watt: 50, jam: 8 },
        { nama: 'TV LED 32"', watt: 40, jam: 4 },
        { nama: 'Kulkas 1 Pintu', watt: 100, jam: 24 },
        { nama: 'Lampu LED', watt: 10, jam: 6 },
        { nama: 'AC 1/2 PK', watt: 400, jam: 8 },
    ],
    tarifKwh: 1447,
    biayaTetap: 12500,
});
assert.equal(rum3.totalKwhText, '186.6 kWh');
assert.ok(Math.abs(rum3.grandTotal - 313586.322) < 1e-6);

// 6. Hukum II Newton F = m * a (3 kasus)
assert.equal(computeFMA({ f: NaN, m: 12, a: 3.5 }).fText, '42.000 N');
assert.equal(computeFMA({ f: 100, m: NaN, a: 4 }).mText, '25.000 kg');
assert.equal(computeFMA({ f: 45, m: 9, a: NaN }).aText, '5.000 m/s²');

// 7. Gaya Berat w = m * g (3 kasus)
assert.equal(computeBerat({ m: 10, g: 9.8 }).wText, '98.000 N');
assert.equal(computeBerat({ m: 5.5, g: 10 }).wText, '55.000 N');
assert.equal(computeBerat({ m: 60, g: NaN }).wText, '588.000 N');

// 8. Resultan Gaya (3 kasus)
const res1 = computeResultanGaya({ f1: 40, f2: 25, m: 13 });
assert.equal(res1.fResText, '65.000 N');
assert.equal(res1.aText, '5.000 m/s²');

const res2 = computeResultanGaya({ f1: 50, f2: -20, m: 6 });
assert.equal(res2.fResText, '30.000 N');
assert.equal(res2.aText, '5.000 m/s²');

const res3 = computeResultanGaya({ f1: 15, f2: -15, m: NaN });
assert.equal(res3.fResText, '0.000 N');
assert.equal(res3.aText, null);

// 9. Energi Kinetik (3 kasus)
assert.equal(computeEnergiKinetik({ ek: NaN, m: 4, v: 5 }).ekText, '50.000 J');
assert.equal(computeEnergiKinetik({ ek: 100, m: NaN, v: 10 }).mText, '2.000 kg');
assert.equal(computeEnergiKinetik({ ek: 72, m: 4, v: NaN }).vText, '6.000 m/s');

// 10. Energi Potensial (3 kasus)
assert.equal(computeEnergiPotensial({ ep: NaN, m: 5, h: 10 }).epText, '490.000 J');
assert.equal(computeEnergiPotensial({ ep: 196, m: NaN, h: 10 }).mText, '2.000 kg');
assert.equal(computeEnergiPotensial({ ep: 294, m: 6, h: NaN }).hText, '5.000 m');

// 11. Usaha (3 kasus)
assert.equal(computeUsaha({ w: NaN, f: 25, s: 8 }).wText, '200.000 J');
assert.equal(computeUsaha({ w: 360, f: NaN, s: 12 }).fText, '30.000 N');
assert.equal(computeUsaha({ w: 150, f: 15, s: NaN }).sText, '10.000 m');

// 12. Gaya Apung Archimedes (3 kasus)
assert.equal(computeGayaApung({ fa: NaN, rho: 1000, v: 0.02 }).faText, '196.00 N');
assert.equal(computeGayaApung({ fa: 98, rho: NaN, v: 0.01 }).rhoText, '1000.00 kg/m³');
assert.equal(computeGayaApung({ fa: 490, rho: 1000, v: NaN }).vText, '0.0500 m³');

// 13. Massa Jenis (3 kasus)
assert.equal(computeMassaJenis({ rho: NaN, m: 500, v: 0.5 }).rhoText, '1000.00 kg/m³');
assert.equal(computeMassaJenis({ rho: 800, m: NaN, v: 0.25 }).mText, '200.00 kg');
assert.equal(computeMassaJenis({ rho: 2700, m: 2700, v: NaN }).vText, '1.0000 m³');

// 14. Hukum Ohm Elektronika (3 kasus)
assert.equal(computeOhmElektronika({ v: NaN, i: 0.5, r: 24 }).vText, '12.00 V');
assert.equal(computeOhmElektronika({ v: 9, i: NaN, r: 3 }).iText, '3.00 A');
assert.equal(computeOhmElektronika({ v: 5, i: 0.02, r: NaN }).rText, '250.00 Ω');

// 15. Resistor Pengganti Seri & Paralel (3 kasus)
const rp1 = computeResistorPengganti({ r1: 100, r2: 100 });
assert.equal(rp1.rsText, '200.00 Ω');
assert.equal(rp1.rpText, '50.00 Ω');

const rp2 = computeResistorPengganti({ r1: 60, r2: 30 });
assert.equal(rp2.rsText, '90.00 Ω');
assert.equal(rp2.rpText, '20.00 Ω');

const rp3 = computeResistorPengganti({ r1: 10, r2: 40 });
assert.equal(rp3.rsText, '50.00 Ω');
assert.equal(rp3.rpText, '8.00 Ω');

console.log('ALL_45_CASES_PASSED');
JS;

        $process = new Process(
            ['node', '--input-type=module', '-e', $script],
            dirname(__DIR__, 2)
        );
        $process->run();

        $this->assertTrue(
            $process->isSuccessful(),
            'Pengujian kalkulator JS gagal: '.$process->getErrorOutput().' '.$process->getOutput()
        );
        $this->assertStringContainsString('ALL_45_CASES_PASSED', $process->getOutput());
    }
}
