<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Sokha Kim', 'Vanna Sok', 'Sophea Touch', 'Sreyneang Chum', 'Rathana Meas', 'Dara Vann', 'Sovannarith Pha', 'Chantha Roeun', 'Veasna Lay', 'Malen Srey', 'Kunthea Nguon', 'Socheat Khen', 'Pov Chhun', 'Sambath Nuth', 'Seyha Ros']),
            'phone' => '+855 ' . $this->faker->numberBetween(10, 99) . ' ' . $this->faker->numberBetween(100, 999) . ' ' . $this->faker->numberBetween(100, 999),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->randomElement(['St. 278, Phnom Penh', 'Siem Reap Province', 'Battambang City', 'Sihanoukville', 'St. 184, Daun Penh, Phnom Penh', 'Preah Vihear Province', 'Kampot Province', 'Koh Kong Island']),
            'status' => $this->faker->randomElement(['active', 'inactive']),
        ];
    }
}
