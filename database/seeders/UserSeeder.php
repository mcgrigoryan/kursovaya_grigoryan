<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'login' => 'manager',
                'password' => 'manager123',
                'role' => 'manager',
                'full_name' => 'Петрова Мария Сергеевна',
            ],
            [
                'login' => 'accountant',
                'password' => 'accountant123',
                'role' => 'accountant',
                'full_name' => 'Сидоров Алексей Викторович',
            ],
            [
                'login' => 'director',
                'password' => 'director123',
                'role' => 'director',
                'full_name' => 'Иванов Иван Иванович',
            ],
        ];

        foreach ($users as $data) {
            User::create($data);
        }
    }
}
