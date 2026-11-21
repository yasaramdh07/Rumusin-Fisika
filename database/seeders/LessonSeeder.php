<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        $gerak = Category::where('slug', 'gerak')->firstOrFail();

        $lesson = Lesson::updateOrCreate(
            ['slug' => 'gerak-lurus-glb-glbb'],
            [
                'category_id' => $gerak->id,
                'title' => 'Gerak Lurus (GLB dan GLBB)',
                'summary' => 'Dari kecepatan konstan sampai percepatan konstan: cara membaca soal gerak lurus, memilih rumus yang tepat, dan mengecek hasilnya.',
                'body' => $this->body(),
                'formulas' => $this->formulas(),
                'is_published' => true,
                'sort_order' => 1,
            ],
        );

        $videos = [
            ['Dasar gerak: jarak, perpindahan, kelajuan, kecepatan', 'jNQXAC9IVRw', 600],
            ['Gerak lurus beraturan (GLB)', '9bZkp7q19f0', 780],
            ['Gerak lurus berubah beraturan (GLBB)', 'dQw4w9WgXcQ', 900],
        ];

        $lesson->videos()->delete();
        foreach ($videos as $i => [$title, $id, $seconds]) {
            $lesson->videos()->create([
                'position' => $i + 1,
                'title' => $title,
                'youtube_url' => 'https://www.youtube.com/watch?v='.$id,
                'youtube_id' => $id,
                'duration_seconds' => $seconds,
            ]);
        }

        // Kerangka materi lain: belum terbit.
        $drafts = [
            ['listrik', 'Hukum Ohm dan Rangkaian Listrik', 'Hubungan tegangan, arus, dan hambatan pada rangkaian seri dan paralel.'],
            ['gaya-newton', 'Hukum Newton tentang Gerak', 'Tiga hukum Newton dan cara menggambar diagram gaya.'],
            ['energi', 'Usaha dan Energi', 'Usaha, energi kinetik, energi potensial, dan kekekalan energi mekanik.'],
            ['archimedes', 'Hukum Archimedes', 'Gaya apung dan kapan benda terapung, melayang, atau tenggelam.'],
            ['elektronika-dasar', 'Komponen Elektronika Dasar', 'Resistor, kapasitor, dioda, dan transistor: fungsi dan simbolnya.'],
        ];

        foreach ($drafts as [$categorySlug, $title, $summary]) {
            $category = Category::where('slug', $categorySlug)->firstOrFail();
            Lesson::updateOrCreate(
                ['slug' => str($title)->slug()->toString()],
                [
                    'category_id' => $category->id,
                    'title' => $title,
                    'summary' => $summary,
                    'body' => 'Materi ini masih disusun.',
                    'formulas' => null,
                    'is_published' => false,
                    'sort_order' => 1,
                ],
            );
        }
    }

    private function body(): string
    {
        return <<<'MD'
## Mulai dari dua pertanyaan

Setiap soal gerak lurus sebenarnya menanyakan hal yang sama: **seberapa jauh**, **seberapa cepat**, atau **berapa lama**. Sebelum memilih rumus, tulis dulu apa yang diketahui dan apa yang ditanya.

Dua besaran sering tertukar:

- **Jarak** adalah panjang lintasan yang ditempuh. Besarnya selalu positif.
- **Perpindahan** adalah selisih posisi akhir dan posisi awal, lengkap dengan arah.

Kalau kamu berlari satu putaran penuh di lintasan 400 m, jaraknya 400 m, tetapi perpindahannya nol karena kamu kembali ke titik awal.

## Kelajuan dan kecepatan

Kelajuan rata-rata adalah jarak total dibagi waktu total. Kecepatan rata-rata adalah perpindahan dibagi waktu.

$$v = \frac{s}{t}$$

Satuan SI-nya meter per sekon ($\text{m/s}$). Untuk mengubah dari $\text{km/jam}$ ke $\text{m/s}$, bagi dengan 3,6. Jadi $72\ \text{km/jam} = 20\ \text{m/s}$.

## Gerak lurus beraturan (GLB)

GLB berarti kecepatan **konstan**, percepatannya nol. Jarak yang ditempuh bertambah rata setiap detik.

$$s = v \cdot t$$

Grafik $s$–$t$ berupa garis lurus miring, dan grafik $v$–$t$ berupa garis lurus mendatar. Luas di bawah grafik $v$–$t$ sama dengan jarak yang ditempuh.

**Contoh.** Sepeda motor melaju 15 m/s selama 40 s. Jaraknya $s = 15 \times 40 = 600\ \text{m}$.

## Gerak lurus berubah beraturan (GLBB)

GLBB berarti kecepatan berubah dengan **percepatan konstan** $a$. Kalau $a$ searah gerak, benda dipercepat. Kalau berlawanan arah, benda diperlambat (nilai $a$ negatif).

$$v_t = v_0 + a\,t$$

$$s = v_0\,t + \tfrac{1}{2}\,a\,t^2$$

$$v_t^2 = v_0^2 + 2\,a\,s$$

Dengan $v_0$ kecepatan awal, $v_t$ kecepatan akhir, $a$ percepatan, $t$ waktu, dan $s$ jarak.

### Memilih rumus

Lihat besaran mana yang **tidak** ada di soal:

| Tidak ada di soal | Pakai |
|---|---|
| jarak $s$ | $v_t = v_0 + a t$ |
| kecepatan akhir $v_t$ | $s = v_0 t + \tfrac{1}{2} a t^2$ |
| waktu $t$ | $v_t^2 = v_0^2 + 2 a s$ |

**Contoh.** Mobil bergerak dari diam dengan $a = 3\ \text{m/s}^2$ selama 5 s. Kecepatan akhirnya $v_t = 0 + 3 \times 5 = 15\ \text{m/s}$, dan jaraknya $s = \tfrac{1}{2} \times 3 \times 5^2 = 37{,}5\ \text{m}$.

**Contoh pengereman.** Mobil 20 m/s direm hingga berhenti dengan perlambatan 5 m/s². Waktu tidak ditanya, jadi pakai $v_t^2 = v_0^2 + 2 a s$:
$0 = 20^2 + 2(-5)s$, sehingga $s = 40\ \text{m}$.

## Gerak vertikal

Benda yang dilempar atau dijatuhkan hanya dipengaruhi gravitasi, jadi geraknya GLBB dengan $a = g \approx 10\ \text{m/s}^2$ menuju bawah. Pada soal di sini kita pakai $g = 10\ \text{m/s}^2$.

- Jatuh bebas dari diam: $h = \tfrac{1}{2} g t^2$.
- Dilempar ke atas dengan $v_0$: tinggi maksimum $h_{maks} = \dfrac{v_0^2}{2g}$, dicapai saat kecepatan sesaat nol, yaitu pada $t = \dfrac{v_0}{g}$.

## Kesalahan yang sering muncul

1. Lupa menyamakan satuan, misalnya km/jam dan menit dicampur dengan meter dan sekon.
2. Memakai rumus GLBB untuk soal yang kecepatannya konstan, atau sebaliknya.
3. Tanda percepatan salah pada soal pengereman: perlambatan berarti $a$ negatif.
4. Menganggap benda di puncak lemparan vertikal tidak punya percepatan. Kecepatannya nol sesaat, percepatannya tetap $g$.

Sudah paham? Tonton video bertahap di samping, lalu coba try out GLB dan GLBB.
MD;
    }

    private function formulas(): string
    {
        return <<<'MD'
**GLB** (kecepatan konstan)

$$s = v\,t$$

**GLBB** (percepatan konstan)

$$v_t = v_0 + a\,t$$

$$s = v_0\,t + \tfrac{1}{2}\,a\,t^2$$

$$v_t^2 = v_0^2 + 2\,a\,s$$

**Gerak vertikal** ($g = 10\ \text{m/s}^2$)

$$h = \tfrac{1}{2}\,g\,t^2 \qquad h_{maks} = \frac{v_0^2}{2g}$$

**Satuan**: $1\ \text{km/jam} = \dfrac{1}{3{,}6}\ \text{m/s}$
MD;
    }
}
