<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

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
        $deliveryFeeList = [0, 2500, 4000];

        $goodsData = json_decode(File::get(database_path('factories/goodsData.json')), true);
        $goodsCategories = Category::where('division', 'goods')->get()->toArray();
        $randomCategory = $this->faker->randomElement($goodsCategories);

        $selectedGoodsList = $goodsData[$randomCategory['name']];
        $selectedGoods = $this->faker->randomElement($selectedGoodsList);

        return [
            'name' => $selectedGoods['name'],
            'price' => $this->faker->numberBetween(100, 9999) * 10,
            'delivery_fee' => $this->faker->randomElement($deliveryFeeList),
            // 'category_id' => function(){
            //     $goodsCategoryIdList  = Category::where('division', 'goods')->get()->pluck('id')->toArray();
            //     return !empty($goodsCategoryIdList) ? $this->faker->randomElement($goodsCategoryIdList) : null;
            // },
            'category_id' => $randomCategory['id'],
            'content' => $this->faker->text(),
            'img_urls' => json_encode([
                'thumbnail' => $selectedGoods['image'],
                'main' => $selectedGoods['image'],
                'info' => $selectedGoods['info'],
            ]),
            'created_at' => $this->faker->dateTime(),
        ];
    }

    public function withIndex(int $index): static{
        return $this->state(function (array $attributes) use ($index) {
            return [
                'name' => $attributes['name'] . ' No.' . $index,
            ];
        });
    }

}
