@extends('layouts.admin')

@section('title', $isEdit ? 'Ubah Kategori' : 'Tambah Kategori')
@section('heading', $isEdit ? 'Ubah Kategori' : 'Tambah Kategori')

@section('actions')
    <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.categories.index') }}">Kembali ke daftar</a>
@endsection

@section('content')
<div class="rf-slab rf-admin-form-slab">
    <form class="rf-admin-form" method="POST" action="{{ $isEdit ? route('admin.categories.update', $category) : route('admin.categories.store') }}" novalidate>
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="rf-field">
            <label class="rf-label" for="name">Nama kategori</label>
            <input
                class="rf-input @error('name') is-invalid @enderror"
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $category->name) }}"
                required
                autofocus
                @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
            >
            <span class="rf-hint">Slug dibuat otomatis dari nama kategori.</span>
            @error('name')
                <span class="rf-error" id="name-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-field">
            <label class="rf-label" for="description">Deskripsi singkat</label>
            <textarea
                class="rf-textarea @error('description') is-invalid @enderror"
                id="description"
                name="description"
                rows="3"
                @error('description') aria-invalid="true" aria-describedby="description-error" @enderror
            >{{ old('description', $category->description) }}</textarea>
            @error('description')
                <span class="rf-error" id="description-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-field rf-admin-form__short">
            <label class="rf-label" for="sort_order">Urutan tampil</label>
            <input
                class="rf-input @error('sort_order') is-invalid @enderror"
                id="sort_order"
                name="sort_order"
                type="number"
                min="0"
                value="{{ old('sort_order', $category->sort_order) }}"
                required
                @error('sort_order') aria-invalid="true" aria-describedby="sort-order-error" @enderror
            >
            @error('sort_order')
                <span class="rf-error" id="sort-order-error"><x-icon name="alert-circle" /> {{ $message }}</span>
            @enderror
        </div>

        <div class="rf-admin-form__actions">
            <button type="submit" class="rf-btn rf-btn--primary">
                {{ $isEdit ? 'Simpan perubahan' : 'Simpan kategori' }}
            </button>
            <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.categories.index') }}">Batal</a>
        </div>
    </form>
</div>
@endsection
