@extends('layouts.admin')

@section('title', 'Pengguna Terdaftar')
@section('heading', 'Pengguna')

@section('content')
@if ($users->isEmpty())
    <div class="rf-empty">Belum ada pengguna terdaftar.</div>
@else
    <div class="rf-table-wrap">
        <table class="rf-table">
            <caption class="visually-hidden">Daftar pengguna terdaftar di Rumusin</caption>
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Peran</th>
                    <th scope="col" class="rf-table__num">Percobaan selesai</th>
                    <th scope="col" class="rf-table__num">Skor terbaik</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td><code>{{ $user->username }}</code></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if ($user->isAdmin())
                                <span class="rf-tag"><x-icon name="user" /> Admin</span>
                            @else
                                <span class="rf-tag">Pelajar</span>
                            @endif
                        </td>
                        <td class="rf-table__num">{{ $user->finished_attempts_count }}</td>
                        <td class="rf-table__num">{{ $user->best_score ?? '—' }}</td>
                        <td>
                            @if ($user->is_active)
                                <span class="rf-tag rf-tag--ok"><x-icon name="check" /> Aktif</span>
                            @else
                                <span class="rf-tag">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination', ['paginator' => $users])
@endif
@endsection
