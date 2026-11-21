<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * 10 soal GLB/GLBB. Semua hitungan sudah dicek ulang (g = 10 m/s^2).
     * Perlu direview Ilyasa sebelum dipakai siswa.
     */
    public function run(): void
    {
        $lesson = Lesson::where('slug', 'gerak-lurus-glb-glbb')->firstOrFail();

        // [tingkat, soal, [A, B, C, D], kunci, pembahasan]
        $items = [
            [1,
                'Sebuah mobil melaju dengan kecepatan tetap 72 km/jam. Jarak yang ditempuh mobil selama 20 menit adalah ...',
                ['1,44 km', '12 km', '24 km', '72 km'], 'c',
                "1. Gerak dengan kecepatan tetap adalah GLB, jadi \$s = v\\,t\$.\n2. Samakan satuan: \$72\\ \\text{km/jam}\$ dan \$t = 20\\ \\text{menit} = \\tfrac{1}{3}\\ \\text{jam}\$.\n3. \$s = 72 \\times \\tfrac{1}{3} = 24\\ \\text{km}\$.\n\nJawaban: **24 km** (C). Cek lewat satuan SI: \$20\\ \\text{m/s} \\times 1200\\ \\text{s} = 24\\,000\\ \\text{m}\$."],
            [1,
                'Pada gerak lurus berubah beraturan dengan percepatan tetap positif dan benda mulai dari diam, grafik kecepatan terhadap waktu ($v$–$t$) berbentuk ...',
                ['garis lurus mendatar', 'garis lurus miring naik dari titik awal', 'kurva parabola', 'garis lurus vertikal'], 'b',
                "1. Rumusnya \$v_t = v_0 + a\\,t\$ dengan \$v_0 = 0\$, jadi \$v_t = a\\,t\$.\n2. Ini hubungan linear: kecepatan naik sebanding dengan waktu.\n3. Grafik linear melalui titik asal adalah garis lurus miring naik, dan kemiringannya sama dengan percepatan \$a\$.\n\nGaris mendatar adalah GLB (A). Parabola adalah bentuk grafik \$s\$–\$t\$ pada GLBB (C)."],
            [1,
                'Sebuah bus berangkat pukul 07.00 dan tiba pukul 10.00 setelah menempuh 180 km. Kecepatan rata-rata bus adalah ...',
                ['60 km/jam', '45 km/jam', '50 km/jam', '90 km/jam'], 'a',
                "1. Waktu tempuh \$t = 10.00 - 07.00 = 3\\ \\text{jam}\$.\n2. \$v = \\dfrac{s}{t} = \\dfrac{180}{3}\$.\n3. \$v = 60\\ \\text{km/jam}\$.\n\nJawaban: **60 km/jam** (A)."],
            [1,
                'Sebuah sepeda motor mulai bergerak dari keadaan diam dengan percepatan tetap $2\\ \\text{m/s}^2$. Kecepatan motor setelah 6 sekon adalah ...',
                ['3 m/s', '8 m/s', '16 m/s', '12 m/s'], 'd',
                "1. Diketahui \$v_0 = 0\$, \$a = 2\\ \\text{m/s}^2\$, \$t = 6\\ \\text{s}\$.\n2. Pakai \$v_t = v_0 + a\\,t\$.\n3. \$v_t = 0 + 2 \\times 6 = 12\\ \\text{m/s}\$.\n\nJawaban: **12 m/s** (D)."],
            [2,
                'Sebuah mobil bergerak dengan kecepatan awal $10\\ \\text{m/s}$ dan percepatan tetap $2\\ \\text{m/s}^2$. Jarak yang ditempuh mobil selama 5 sekon adalah ...',
                ['75 m', '60 m', '100 m', '65 m'], 'a',
                "1. Diketahui \$v_0 = 10\\ \\text{m/s}\$, \$a = 2\\ \\text{m/s}^2\$, \$t = 5\\ \\text{s}\$. Jarak ditanya, jadi pakai \$s = v_0 t + \\tfrac{1}{2} a t^2\$.\n2. \$s = 10 \\times 5 + \\tfrac{1}{2} \\times 2 \\times 5^2\$.\n3. \$s = 50 + 25 = 75\\ \\text{m}\$.\n\nJawaban: **75 m** (A)."],
            [2,
                'Sebuah mobil bergerak dengan kecepatan $20\\ \\text{m/s}$, lalu direm dengan perlambatan tetap $4\\ \\text{m/s}^2$ hingga berhenti. Jarak yang ditempuh mobil sejak mulai direm sampai berhenti adalah ...',
                ['40 m', '80 m', '50 m', '100 m'], 'c',
                "1. Diketahui \$v_0 = 20\\ \\text{m/s}\$, \$v_t = 0\$, \$a = -4\\ \\text{m/s}^2\$. Waktu tidak ditanya, jadi pakai \$v_t^2 = v_0^2 + 2 a s\$.\n2. \$0 = 20^2 + 2(-4)\\,s\$, sehingga \$0 = 400 - 8s\$.\n3. \$s = \\dfrac{400}{8} = 50\\ \\text{m}\$.\n\nJawaban: **50 m** (C). Tanda negatif pada \$a\$ penting karena benda diperlambat."],
            [2,
                'Kecepatan sebuah benda bertambah dari $4\\ \\text{m/s}$ menjadi $16\\ \\text{m/s}$ dalam waktu 6 sekon secara beraturan. Percepatan benda adalah ...',
                ['1 m/s²', '2 m/s²', '3 m/s²', '4 m/s²'], 'b',
                "1. Diketahui \$v_0 = 4\\ \\text{m/s}\$, \$v_t = 16\\ \\text{m/s}\$, \$t = 6\\ \\text{s}\$.\n2. Dari \$v_t = v_0 + a\\,t\$ diperoleh \$a = \\dfrac{v_t - v_0}{t}\$.\n3. \$a = \\dfrac{16 - 4}{6} = \\dfrac{12}{6} = 2\\ \\text{m/s}^2\$.\n\nJawaban: **2 m/s²** (B)."],
            [3,
                'Mobil A bergerak dengan kecepatan tetap $20\\ \\text{m/s}$ dan mengejar mobil B yang berada $200\\ \\text{m}$ di depannya. Mobil B bergerak searah dengan kecepatan tetap $10\\ \\text{m/s}$. Mobil A menyusul mobil B setelah ...',
                ['20 s', '10 s', '15 s', '40 s'], 'a',
                "1. Kedua mobil bergerak GLB. Saat mobil A menyusul, jarak tempuh A sama dengan jarak tempuh B ditambah 200 m.\n2. \$20\\,t = 200 + 10\\,t\$.\n3. \$10\\,t = 200\$, sehingga \$t = 20\\ \\text{s}\$.\n\nJawaban: **20 s** (A). Cara cepat: kecepatan relatif \$20 - 10 = 10\\ \\text{m/s}\$, jadi jarak 200 m terlampaui dalam \$200/10 = 20\\ \\text{s}\$. Cek: A menempuh 400 m, B menempuh 200 m, selisihnya 200 m."],
            [2,
                'Sebuah bola dilempar vertikal ke atas dengan kecepatan awal $20\\ \\text{m/s}$. Jika $g = 10\\ \\text{m/s}^2$, tinggi maksimum yang dicapai bola adalah ...',
                ['10 m', '40 m', '20 m', '80 m'], 'c',
                "1. Di titik tertinggi kecepatan bola nol: \$v_t = 0\$. Percepatannya \$a = -g = -10\\ \\text{m/s}^2\$.\n2. Pakai \$v_t^2 = v_0^2 + 2 a h\$: \$0 = 20^2 - 2(10)\\,h\$.\n3. \$h = \\dfrac{400}{20} = 20\\ \\text{m}\$.\n\nJawaban: **20 m** (C). Cek lewat waktu naik \$t = v_0/g = 2\\ \\text{s}\$: \$h = 20 \\times 2 - \\tfrac{1}{2} \\times 10 \\times 2^2 = 40 - 20 = 20\\ \\text{m}\$."],
            [3,
                'Sebuah batu dijatuhkan tanpa kecepatan awal dari ketinggian $80\\ \\text{m}$. Jika $g = 10\\ \\text{m/s}^2$ dan hambatan udara diabaikan, waktu yang dibutuhkan batu sampai di tanah adalah ...',
                ['2 s', '4 s', '8 s', '16 s'], 'b',
                "1. Jatuh bebas dari diam: \$h = \\tfrac{1}{2} g\\,t^2\$.\n2. \$80 = \\tfrac{1}{2} \\times 10 \\times t^2 = 5\\,t^2\$, sehingga \$t^2 = 16\$.\n3. \$t = 4\\ \\text{s}\$.\n\nJawaban: **4 s** (B). Cek: kecepatan saat tiba \$10 \\times 4 = 40\\ \\text{m/s}\$, dan \$v_t^2 = 2 g h = 2 \\times 10 \\times 80 = 1600\$, yaitu \$40^2\$."],
        ];

        foreach ($items as [$difficulty, $body, $options, $correct, $explanation]) {
            Question::updateOrCreate(
                ['body' => $body],
                [
                    'lesson_id' => $lesson->id,
                    'option_a' => $options[0],
                    'option_b' => $options[1],
                    'option_c' => $options[2],
                    'option_d' => $options[3],
                    'correct_option' => $correct,
                    'explanation' => $explanation,
                    'difficulty' => $difficulty,
                ],
            );
        }
    }
}
