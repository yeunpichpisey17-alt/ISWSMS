<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Smart Axiata Co., Ltd.',
                'Lucky Supermarket',
                'Acleda Bank Plc',
                'Wing (Cambodia) Ltd.',
                'Cambodia Mobile Telecommunication',
                'Metro Mall Phnom Penh',
                'AEON Mall',
                'Phnom Penh City Mart',
                'Bayon Enterprises',
                'Angkor Electronics'
            ]),
            'phone' => '+855 ' . $this->faker->numberBetween(10, 99) . ' ' . $this->faker->numberBetween(100, 999) . ' ' . $this->faker->numberBetween(100, 999),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->randomElement([
                'St. 102, Phnom Penh',
                'Siem Reap Airport Road',
                'Battambang Market',
                'Sihanouk Blvd',
                'Russian Confederation Blvd, Phnom Penh',
                'National Road 6, Siem Reap'
            ]),
            'contact_person' => $this->faker->randomElement(['Sokha Kim', 'Vanna Sok', 'Sophea Touch']),

        ];
    }
}
