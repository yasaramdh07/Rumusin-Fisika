<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Akun dummy hanya untuk environment local. Password dibaca dari SEED_PASSWORD.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('UserSeeder dilewati: akun dummy hanya dibuat di environment local.');

            return;
        }

        $password = config('rumusin.seed_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException(
                'SEED_PASSWORD kosong. Isi SEED_PASSWORD di file .env (lokal, jangan di-commit), lalu jalankan ulang seeder.'
            );
        }

        $accounts = [
            ['Admin Rumusin', 'admin_rumusin', null, User::ROLE_ADMIN],
            ['Dewi Anggraini', 'usersaturumusin', 'user1@rumusin.test', User::ROLE_USER],
            ['Raka Pratama', 'userduarumusin', 'user2@rumusin.test', User::ROLE_USER],
            ['Salsabila Putri', 'usertigarumusin', 'user3@rumusin.test', User::ROLE_USER],
            ['Bima Saputra', 'userempatrumusin', 'user4@rumusin.test', User::ROLE_USER],
            ['Nadia Maharani', 'userlimarumusin', 'user5@rumusin.test', User::ROLE_USER],
        ];

        foreach ($accounts as [$name, $username, $email, $role]) {
            $user = User::firstOrNew(['username' => $username]);
            $user->fill(['name' => $name, 'email' => $email, 'password' => $password]);
            // role dan is_active tidak mass-assignable.
            $user->forceFill(['role' => $role, 'is_active' => true])->save();
        }
    }
}
