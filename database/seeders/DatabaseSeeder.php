<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            AdminSeeder::class,
        ]);

        if (app()->environment('local')) {
            // 只在本地环境生成测试数据
            \App\Models\User::factory(50)->create();
            \App\Models\Agent::factory(20)->create();

            // 创建一些查询记录
            \App\Models\User::all()->each(function ($user) {
                $user->queries()->create([
                    'amount' => 99,
                    'payment_status' => 'paid',
                    'payment_type' => $this->faker->randomElement(['wechat', 'alipay', 'auth_code']),
                    'report_content' => [
                        'basic_info' => [
                            'name' => $user->name,
                            'phone' => $user->phone,
                            'id_card' => $this->faker->numerify('##################')
                        ],
                        'credit_score' => $this->faker->numberBetween(350, 950),
                        'risk_items' => [],
                        'loan_history' => []
                    ]
                ]);
            });
        }
    }
} 