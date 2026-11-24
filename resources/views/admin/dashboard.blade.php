@extends('layouts.admin')

@section('title', 'Dasbor Admin')
@section('heading', 'Dasbor Admin')

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <a class="rf-btn rf-btn--primary" href="{{ route('admin.lessons.create') }}"><x-icon name="plus" /> Tambah materi</a>
        <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.categories.create') }}"><x-icon name="plus" /> Tambah kategori</a>
    </div>
@endsection

@section('content')
<div class="rf-admin-summary">
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Ringkasan jumlah data di Rumusin</caption>
            <thead>
                <tr>
                    <th scope="col">Bagian</th>
                    <th scope="col" class="rf-table__num">Jumlah</th>
                    <th scope="col" class="rf-table__actions"><span class="visually-hidden">Kelola</span></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pengguna terdaftar</td>
                    <td class="rf-table__num"><strong>{{ $userCount }}</strong></td>
                    <td class="rf-table__actions"><a class="rf-btn rf-btn--tertiary" href="{{ route('admin.users.index') }}">Lihat pengguna</a></td>
                </tr>
                <tr>
                    <td>Materi</td>
                    <td class="rf-table__num"><strong>{{ $lessonCount }}</strong></td>
                    <td class="rf-table__actions"><a class="rf-btn rf-btn--tertiary" href="{{ route('admin.lessons.index') }}">Kelola materi</a></td>
                </tr>
                <tr>
                    <td>Soal bank try out</td>
                    <td class="rf-table__num"><strong>{{ $questionCount }}</strong></td>
                    <td class="rf-table__actions"><a class="rf-btn rf-btn--tertiary" href="{{ url('/admin/soal') }}">Kelola soal</a></td>
                </tr>
                <tr>
                    <td>Percobaan try out selesai</td>
                    <td class="rf-table__num"><strong>{{ $attemptCount }}</strong></td>
                    <td class="rf-table__actions"><a class="rf-btn rf-btn--tertiary" href="{{ url('/admin/tryout') }}">Kelola try out</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
