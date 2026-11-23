@extends('layouts.admin')

@section('title', 'Kelola Paket Try Out')
@section('heading', 'Paket Try Out')

@section('actions')
    <a class="rf-btn rf-btn--primary" href="{{ route('admin.tryouts.create') }}">
        <x-icon name="plus" /> Tambah paket try out
    </a>
@endsection

@section('content')
@if ($tryouts->isEmpty())
    <div class="rf-empty">Belum ada paket try out. Buat paket baru dan pilih minimal {{ $minActiveQuestions }} soal untuk mengaktifkannya.</div>
@else
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Daftar paket try out</caption>
            <thead>
                <tr>
                    <th scope="col">Judul paket</th>
                    <th scope="col">Materi</th>
                    <th scope="col" class="rf-table__num">Durasi</th>
                    <th scope="col" class="rf-table__num">Soal</th>
                    <th scope="col">Jadwal</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="rf-table__actions">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tryouts as $tryout)
                    <tr>
                        <td>
                            <strong>{{ $tryout->title }}</strong>
                            <div class="rf-muted rf-small"><code>{{ $tryout->slug }}</code></div>
                        </td>
                        <td>{{ $tryout->lesson?->title ?? 'Gabungan' }}</td>
                        <td class="rf-table__num">{{ $tryout->duration_minutes }} menit</td>
                        <td class="rf-table__num">{{ $tryout->questions_count }}</td>
                        <td class="rf-small">
                            @if ($tryout->starts_at || $tryout->ends_at)
                                {{ $tryout->starts_at?->format('d M Y H:i') ?? 'Sekarang' }}
                                –
                                {{ $tryout->ends_at?->format('d M Y H:i') ?? 'Seterusnya' }}
                            @else
                                Tanpa batas jadwal
                            @endif
                        </td>
                        <td>
                            @if ($tryout->is_active)
                                <span class="rf-tag rf-tag--ok"><x-icon name="check" /> Aktif</span>
                            @else
                                <span class="rf-tag">Nonaktif</span>
                            @endif
                        </td>
                        <td class="rf-table__actions">
                            <div class="rf-admin-actions">
                                @if (! $tryout->is_active)
                                    <form method="POST" action="{{ route('admin.tryouts.activate', $tryout) }}">
                                        @csrf
                                        <button type="submit" class="rf-btn rf-btn--secondary">Jadikan aktif</button>
                                    </form>
                                @endif
                                <a class="rf-btn rf-btn--tertiary" href="{{ route('admin.tryouts.edit', $tryout) }}">Ubah & soal</a>
                                <form method="POST" action="{{ route('admin.tryouts.destroy', $tryout) }}" data-confirm="Hapus paket try out '{{ $tryout->title }}'?">
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

    @include('admin.partials.pagination', ['paginator' => $tryouts])
@endif
@endsection
