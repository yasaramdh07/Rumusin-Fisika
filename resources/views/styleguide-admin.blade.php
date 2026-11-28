@extends('layouts.admin')

@section('title', 'Contoh layout admin')
@section('heading', 'Contoh layout admin')

@section('actions')
    <a href="#" class="rf-btn rf-btn--primary"><x-icon name="plus" /> Tambah soal</a>
@endsection

@section('content')
    <div class="rf-alert rf-alert--info mb-5">
        <x-icon name="info" />
        <div class="rf-alert__body"><span class="rf-alert__kind">Info</span> Ini halaman contoh untuk memeriksa kepala halaman, bottom bar admin (termasuk panel "Lainnya"), dan tabel admin.</div>
    </div>
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Daftar soal contoh</caption>
            <thead><tr><th scope="col">Soal</th><th scope="col">Tingkat</th><th scope="col" class="rf-table__num">Dipakai</th><th scope="col"><span class="visually-hidden">Aksi</span></th></tr></thead>
            <tbody>
                <tr><td>Jarak tempuh mobil 72 km/jam selama 20 menit</td><td>1</td><td class="rf-table__num">1 paket</td><td class="rf-table__actions"><a href="#" class="rf-btn rf-btn--tertiary">Ubah</a></td></tr>
                <tr><td>Tinggi maksimum bola dilempar ke atas</td><td>2</td><td class="rf-table__num">1 paket</td><td class="rf-table__actions"><a href="#" class="rf-btn rf-btn--tertiary">Ubah</a></td></tr>
            </tbody>
        </table>
    </div>
@endsection