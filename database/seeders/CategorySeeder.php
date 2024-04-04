<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        $categories = [
            [
                'division' => 'goods',
                'names' => [
                    '사육세트', '사육장', '온/습도계', '바닥재', '조화', '채집통', '소켓/바닥히팅', '칼슘/영양제', '장식', 'UVB', '물/먹이그릇', '사료/슈퍼푸드', '은신처', '생먹이', '보조용품'
                ],
            ],
            [
                'division' => 'posts',
                'names' => [
                    '홈', '잡담&꿀팁', '분양'
                ],
            ],
        ];

        $subPostCategories = [
            [   
                'parentName' => '홈',
                'names' => ['home', 'rules'],
            ],
            [   
                'parentName' => '잡담&꿀팁',
                'names' => ['사육', '핫딜', '주의점', '자유'],
            ],
            [   
                'parentName' => '분양',
                'names' =>  ['분양'],
            ],
        ];

        foreach ($categories as $category) {
            foreach ($category['names'] as $name) {
                Category::create([
                    'name' => $name,
                    'division' => $category['division'],
                ]);
            }
        }

        foreach ($subPostCategories as $category) {
            $parent = Category::where('name', $category['parentName'])->first();
            foreach ($category['names'] as $name) {
                Category::create([
                    'name' => $name,
                    'division' => 'subPosts',
                    'parent_id' => $parent->id,
                ]);
            }
        }


    }
}
