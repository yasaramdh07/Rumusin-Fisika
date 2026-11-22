@extends('layouts.admin')

@section('title', 'Kelola Kategori')
@section('heading', 'Kategori')

@section('actions')
    <a class="rf-btn rf-btn--primary" href="{{ route('admin.categories.create') }}">
        <x-icon name="plus" /> Tambah kategori
    </a>
@endsection

@section('content')
@if ($categories->isEmpty())
    <div class="rf-empty">Belum ada kategori. Tambahkan kategori pertama untuk mulai menulis materi.</div>
@else
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Daftar kategori fisika</caption>
            <thead>
                <tr>
                    <th scope="col" class="rf-table__num">Urutan</th>
                    <th scope="col">Nama</th>
                    <th scope="col">Slug</th>
                    <th scope="col" class="rf-table__num">Materi</th>
                    <th scope="col" class="rf-table__actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td class="rf-table__num">{{ $category->sort_order }}</td>
                        <td>
                            <strong>{{ $category->name }}</strong>
                            @if ($category->description)
                                <div class="rf-muted rf-small">{{ $category->description }}</div>
                            @endif
                        </td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td class="rf-table__num">{{ $category->lessons_count }}</td>
                        <td class="rf-table__actions">
                            <div class="rf-admin-actions">
                                <form method="POST" action="{{ route('admin.categories.up', $category) }}">
                                    @csrf
                                    <button type="submit" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Naikkan urutan {{ $category->name }}" title="Naikkan urutan">
                                        <x-icon name="arrow-up" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.down', $category) }}">
                                    @csrf
                                    <button type="submit" class="rf-btn rf-btn--tertiary rf-btn--icon" aria-label="Turunkan urutan {{ $category->name }}" title="Turunkan urutan">
                                        <x-icon name="arrow-down" />
                                    </button>
                                </form>
                                <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.categories.edit', $category) }}">Ubah</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Hapus kategori {{ $category->name }}? Semua materi di dalamnya ikut terhapus.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rf-btn rf-btn--tertiary"><x-icon name="trash" /> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $categories])
@endif
@endsection
