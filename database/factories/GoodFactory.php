<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Good>
 */
class GoodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * 
     */

    

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'price' => $this->faker->numberBetween(100, 9999) * 10,
            'category_id' => function(){
                $goodsCategoryIdList  = Category::where('division', 'goods')->get()->pluck('id')->toArray();
                return !empty($goodsCategoryIdList) ? $this->faker->randomElement($goodsCategoryIdList) : null;
            },
            'content' => $this->faker->text(),
            'img_urls' => json_encode([
                'thumbnail' => 'https://via.placeholder.com/184X288',
                'main' => 'https://via.placeholder.com/320',
                'info' => 'https://shop-phinf.pstatic.net/20231009_281/1696845331449a2zv9_PNG/%EB%A6%AC%ED%8B%80%ED%8F%AC%EB%A0%88%EC%8A%A4%ED%8A%B8-1-%EC%B5%9C%EC%A2%85-%EC%88%98%EC%A0%95.png?type=w860',
            ]),
            'created_at' => $this->faker->dateTime(),
        ];
    }
}
