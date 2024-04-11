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
                    '사육세트', '사육장', '온/습도계', '바닥재', '조화', '채집통', '소켓/바닥히팅', '칼슘/영양제', '유목', '스팟/히팅','장식', 'UVB', '물/먹이그릇', '사료/슈퍼푸드', '은신처', '생먹이', '보조용품'
                ],
            ],
            [
                'division' => 'posts',
                'names' => [
                    '홈', '잡담&꿀팁', '분양'
                ],
            ],
        ];

        $goodsCategoriesImageUrls = [
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%82%AC%EC%9C%A1%EC%84%B8%ED%8A%B8.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%82%AC%EC%9C%A1%EC%9E%A5.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%98%A8%EC%8A%B5%EB%8F%84%EA%B3%84.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EB%B0%94%EB%8B%A5%EC%9E%AC.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%A1%B0%ED%99%94.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%B1%84%EC%A7%91%ED%86%B5.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%86%8C%EC%BC%93%EB%B0%94%EB%8B%A5%ED%9E%88%ED%8C%85.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%B9%BC%EC%8A%98%EC%98%81%EC%96%91%EC%A0%9C.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%9C%A0%EB%AA%A9.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%8A%A4%ED%8C%9F%ED%9E%88%ED%8C%85.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%9E%A5%EC%8B%9D.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.UVB.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EB%AC%BC%EB%A8%B9%EC%9D%B4%EA%B7%B8%EB%A6%87.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%82%AC%EB%A3%8C%EC%8A%88%ED%8D%BC%ED%91%B8%EB%93%9C.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%9D%80%EC%8B%A0%EC%B2%98.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EC%83%9D%EB%A8%B9%EC%9D%B4.png',
            'https://capstone-project-pachungking.s3.ap-northeast-2.amazonaws.com/images/categories/image_.%EB%B3%B4%EC%A1%B0%EC%9A%A9%ED%92%88.png'
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
            
            if($category['division'] === 'goods') {
                foreach ($category['names'] as $index => $name) {
                    Category::create([
                        'name' => $name,
                        'division' => $category['division'],
                        'img_url' => $goodsCategoriesImageUrls[$index],
                    ]);
                }
            }
            else{
                foreach ($category['names'] as $name) {
                    Category::create([
                        'name' => $name,
                        'division' => $category['division'],
                    ]);
                }
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
