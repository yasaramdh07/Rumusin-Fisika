---
category: elektronika-dasar
title: Komponen Elektronika Dasar
slug: komponen-elektronika-dasar
summary: Pelajari komponen dasar elektronika (resistor, kapasitor, LED) dan pahami perhitungan rangkaian seri serta paralel.
sort_order: 1
is_published: true
videos:
  - title: Rangkaian Listrik SMA
    youtube_url: https://www.youtube.com/watch?v=dLalv-D_HM8
    youtube_id: dLalv-D_HM8
    duration_seconds: 600
formulas: |
  **Hukum Ohm**
  $$V = I \times R$$

  **Resistor Seri & Paralel**
  $$R_s = R_1 + R_2 + \dots + R_n$$
  $$\frac{1}{R_p} = \frac{1}{R_1} + \frac{1}{R_2} + \dots + \frac{1}{R_n}$$

  **Kapasitor Seri & Paralel**
  $$\frac{1}{C_s} = \frac{1}{C_1} + \frac{1}{C_2} + \dots + \frac{1}{C_n}$$
  $$C_p = C_1 + C_2 + \dots + C_n$$
---
## Komponen Elektronika Dasar

Setiap rangkaian elektronik tersusun dari berbagai komponen yang mengatur aliran listrik (arus dan tegangan). Berikut komponen utamanya:

| Komponen | Peran | Satuan | Fungsi Utama |
|---|---|---|---|
| **Resistor ($R$)** | Hambatan | Ohm ($\Omega$) | Menghambat arus listrik agar tidak berlebih. |
| **Kapasitor ($C$)** | Penyimpan Muatan | Farad ($\text{F}$) | Menyimpan muatan listrik sementara. |
| **LED / Lampu** | Dioda Cahaya | $\text{Volt} / \text{mA}$ | Menyala saat dilewati arus listrik searah. |

> **Hukum Ohm: $V = I \times R$**. Tegangan ($V$) berbanding lurus dengan Arus ($I$) dan Hambatan ($R$). Kalau $R$ dibesarkan, maka $I$ yang lewat akan makin kecil.

## Rangkaian Seri

Komponen disusun berderet (tanpa cabang). Arus yang mengalir di setiap komponen besarnya sama, tapi tegangannya terbagi-bagi.

- **Resistor Seri** (makin besar):
  $$R_s = R_1 + R_2 + \dots + R_n$$
- **Kapasitor Seri** (makin kecil):
  $$\frac{1}{C_s} = \frac{1}{C_1} + \frac{1}{C_2} + \dots + \frac{1}{C_n}$$

## Rangkaian Paralel

Komponen disusun bercabang. Tegangan di tiap cabang sama besar, tapi arusnya terbagi sesuai nilai hambatan tiap cabang.

- **Resistor Paralel** (makin kecil):
  $$\frac{1}{R_p} = \frac{1}{R_1} + \frac{1}{R_2} + \dots + \frac{1}{R_n}$$
- **Kapasitor Paralel** (makin besar):
  $$C_p = C_1 + C_2 + \dots + C_n$$

> Kebalikan ya! Kalau resistor dipasang paralel nilai hambatannya malah turun, sedangkan kapasitor dipasang paralel kapasitasnya malah nambah!

## Latihan Soal

### Soal 1 (Hukum Ohm)

Sebuah lampu senter memiliki hambatan **$10\ \Omega$** dan dihubungkan ke baterai **$3\ \text{Volt}$**. Berapa arus listrik yang mengalir?

- Diketahui: $R = 10\ \Omega$, $V = 3\ \text{V}$
- Pakai rumus: $I = \dfrac{V}{R}$
- $I = \dfrac{3}{10}$
- **Hasil**: $I = 0{,}3\ \text{Ampere}$

### Soal 2 (Resistor)

Dua buah resistor, masing-masing **$6\ \Omega$** dan **$3\ \Omega$** dirangkai secara paralel. Berapa hambatan penggantinya?

- Diketahui: $R_1 = 6\ \Omega$, $R_2 = 3\ \Omega$
- Pakai rumus: $\dfrac{1}{R_p} = \dfrac{1}{R_1} + \dfrac{1}{R_2}$
- $\dfrac{1}{R_p} = \dfrac{1}{6} + \dfrac{1}{3} = \dfrac{1}{6} + \dfrac{2}{6} = \dfrac{3}{6}$
- $R_p = \dfrac{6}{3}$
- **Hasil**: $R_p = 2\ \Omega$
