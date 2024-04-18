<?php

namespace Database\Factories;

use App\Models\CageSerialCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CageSerialCode>
 */
class CageSerialCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   
        $serialCode = 'CAGE-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(5));
        $confirmed = CageSerialCode::where('serial_code', $serialCode)->exists(); // exists()는 존재하면 true, 아니면 false를 반환합니다.
        while($confirmed){
            $serialCode = 'CAGE-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(5));
            $confirmed = CageSerialCode::where('serial_code', $serialCode)->exists();
        }
        
        $ipBase = '172.21.2.';
        $ipEnd = $this->faker->unique()->numberBetween(2, 254);
        $location = $ipBase.$ipEnd;

        $confirmIp = CageSerialCode::where('location', $location)->exists();
        while($confirmIp){
            $confirmIp = CageSerialCode::where('location', $location)->exists();
        }

        return [
            'size'          => $this->faker->randomElement(['small', 'medium', 'large']),
            'serial_code'   => $serialCode,
            'location'      => $location,
        ];
    }
}
