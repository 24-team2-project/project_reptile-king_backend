<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\CageSerialCode;
use App\Models\Good;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);


        
        // CageSerialCode::factory(50)->create();   // 케이지 시리얼 코드 생성
        // $this->call(RoleSeeder::class);          // 역할 생성
        // $this->call(CategorySeeder::class);      // 카테고리 생성
        // $this->call(UserSeeder::class);          // 관리자 계정 생성

        foreach (range(1, 100) as $index) { // 상품 생성
            Good::factory()->withIndex($index)->create([
                'content' => null,
            ]);
        }

    }
}
