@extends('layouts.admin')

@section('title', 'Dasbor Admin')
@section('heading', 'Dasbor Admin')

@section('content')
<div class="rf-slab">
    <p class="rf-prose mb-4">Ringkasan data saat ini. Pengelolaan kategori, materi, soal, try out, dan diskusi dilengkapi di prompt admin.</p>
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Ringkasan jumlah data</caption>
            <thead>
                <tr>
                    <th scope="col">Data</th>
                    <th scope="col" class="rf-table__num">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Pengguna terdaftar</td><td class="rf-table__num">{{ $userCount }}</td></tr>
                <tr><td>Materi</td><td class="rf-table__num">{{ $lessonCount }}</td></tr>
                <tr><td>Soal</td><td class="rf-table__num">{{ $questionCount }}</td></tr>
                <tr><td>Percobaan try out selesai</td><td class="rf-table__num">{{ $attemptCount }}</td></tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
