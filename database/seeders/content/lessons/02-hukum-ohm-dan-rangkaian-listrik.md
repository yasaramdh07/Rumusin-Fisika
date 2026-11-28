---
category: listrik
title: Hukum Ohm dan Rangkaian Listrik
slug: hukum-ohm-dan-rangkaian-listrik
summary: Pelajari listrik dasar dari konsep Hukum Ohm, daya listrik, dan rangkaian seri-paralel sampai aplikasi perhitungan rumah tangga.
sort_order: 1
is_published: true
videos:
  - title: Hukum Ohm dan Daya Listrik
    youtube_url: https://www.youtube.com/watch?v=WQjGeCGuC1o
    youtube_id: WQjGeCGuC1o
    duration_seconds: 600
formulas: |
  **Hukum Ohm**
  $$V = I \times R$$
  $$I = \frac{V}{R} \qquad R = \frac{V}{I}$$

  **Daya Listrik**
  $$P = V \times I = I^2 \times R = \frac{V^2}{R}$$

  **Rangkaian Seri & Paralel**
  $$R_s = R_1 + R_2 + \dots$$
  $$\frac{1}{R_p} = \frac{1}{R_1} + \frac{1}{R_2} + \dots$$
---
## Konsep Dasar Listrik

Listrik adalah aliran elektron dalam konduktor. Kita akan pelajari hubungan antara tegangan, arus, hambatan, dan daya listrik.

### Besaran Utama

| Simbol | Nama Besaran | Satuan |
|---|---|---|
| $V$ | Tegangan | Volt ($\text{V}$) |
| $I$ | Arus | Ampere ($\text{A}$) |
| $R$ | Hambatan | Ohm ($\Omega$) |
| $P$ | Daya | Watt ($\text{W}$) |

> **Hukum Ohm** adalah dasar: $V = I \times R$. Semua rumus lain dibangun dari sini!

## Hukum Ohm

Hukum Ohm menyatakan bahwa **tegangan berbanding lurus dengan arus**, dan **berbanding terbalik dengan hambatan**.

### Rumus Lengkap

**Utama** (Tegangan = Arus $\times$ Hambatan):

$$V = I \times R$$

Turunannya:

- **Arus**: $I = \dfrac{V}{R}$
- **Hambatan**: $R = \dfrac{V}{I}$

> Contoh: Lampu $220\ \text{V}$ dengan hambatan $100\ \Omega \rightarrow \text{arus} = \dfrac{220}{100} = 2{,}2\ \text{A}$.

## Daya Listrik

Daya adalah laju energi yang dihasilkan atau diubah per satuan waktu. Semakin besar daya, semakin besar energi yang dipakai.

### Rumus Daya (3 Bentuk)

Pilih yang sesuai data yang diketahui:

$$P = V \times I = I^2 \times R = \frac{V^2}{R}$$

- **Tegangan $\times$ Arus**: $P = V \times I$
- **Arus kuadrat $\times$ $R$**: $P = I^2 \times R$
- **Tegangan kuadrat / $R$**: $P = \dfrac{V^2}{R}$

> AC $220\ \text{V}$ rumah tangga: Kipas $50\ \text{W}$, TV $100\ \text{W}$, Kulkas $150\ \text{W}$, AC $800\ \text{W}$!

## Rangkaian Seri & Paralel

### Rangkaian Seri

- **Hambatan total** (hambatan dijumlahkan):
  $$R_s = R_1 + R_2 + R_3 + \dots$$
- **Arus sama** (arus mengalir sama di semua resistor):
  $$I = I_1 = I_2 = I_3$$

### Rangkaian Paralel

- **Hambatan total** (hambatan bersusunan paralel):
  $$\frac{1}{R_p} = \frac{1}{R_1} + \frac{1}{R_2} + \frac{1}{R_3} + \dots$$
- **Tegangan sama** (tegangan sama di semua resistor):
  $$V = V_1 = V_2 = V_3$$

> **Seri:** Lampu kulkas (jika 1 mati, semua mati). **Paralel:** Stop kontak rumah (satu mati, yang lain nyala).
