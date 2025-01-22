<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Administrator;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // 创建超级管理员
        Administrator::create([
            'username' => 'admin',
            'password' => Hash::make('admin888'),
            'name' => '超级管理员',
            'role' => 'super_admin',
            'status' => true
        ]);

        // 创建普通管理员
        Administrator::create([
            'username' => 'manager',
            'password' => Hash::make('manager888'),
            'name' => '管理员',
            'role' => 'admin',
            'status' => true
        ]);
    }
} 