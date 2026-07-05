<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@kopikita.test'],
            [
                'name' => 'Admin Kopi Kita',
                'password' => Hash::make('password'),
            ],
        );

        $categories = collect([
            'Coffee',
            'Non Coffee',
            'Tea',
            'Snack',
            'Dessert',
        ])->mapWithKeys(function (string $name) {
            $category = Category::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                ],
            );

            return [$name => $category];
        });

        $ingredients = collect([
            ['name' => 'Espresso', 'unit' => 'gr', 'price' => 180, 'minimum_stock' => 500, 'current_stock' => 5000],
            ['name' => 'Air', 'unit' => 'ml', 'price' => 1, 'minimum_stock' => 5000, 'current_stock' => 50000],
            ['name' => 'Susu', 'unit' => 'ml', 'price' => 25, 'minimum_stock' => 3000, 'current_stock' => 20000],
            ['name' => 'Gula Aren', 'unit' => 'ml', 'price' => 35, 'minimum_stock' => 1000, 'current_stock' => 8000],
            ['name' => 'Cokelat Bubuk', 'unit' => 'gr', 'price' => 150, 'minimum_stock' => 500, 'current_stock' => 4000],
            ['name' => 'Teh', 'unit' => 'gr', 'price' => 90, 'minimum_stock' => 300, 'current_stock' => 3000],
            ['name' => 'Roti', 'unit' => 'pcs', 'price' => 3500, 'minimum_stock' => 10, 'current_stock' => 60],
            ['name' => 'Keju', 'unit' => 'gr', 'price' => 120, 'minimum_stock' => 500, 'current_stock' => 5000],
        ])->mapWithKeys(function (array $data) use ($admin) {
            $ingredient = Ingredient::query()->updateOrCreate(
                ['name' => $data['name']],
                [
                    'unit' => $data['unit'],
                    'price' => $data['price'],
                    'minimum_stock' => $data['minimum_stock'],
                    'current_stock' => $data['current_stock'],
                ],
            );

            IngredientStock::query()->updateOrCreate(
                [
                    'ingredient_id' => $ingredient->id,
                    'type' => 'initial',
                    'reference_type' => self::class,
                    'reference_id' => $ingredient->id,
                ],
                [
                    'user_id' => $admin->id,
                    'quantity' => $data['current_stock'],
                    'stock_before' => 0,
                    'stock_after' => $data['current_stock'],
                    'note' => 'Stok awal dari seeder.',
                ],
            );

            return [$data['name'] => $ingredient];
        });

        collect([
            [
                'name' => 'Americano',
                'category' => 'Coffee',
                'selling_price' => 18000,
                'cost_price' => 5000,
                'recipe' => [
                    'Espresso' => 18,
                    'Air' => 150,
                ],
            ],
            [
                'name' => 'Cafe Latte',
                'category' => 'Coffee',
                'selling_price' => 25000,
                'cost_price' => 8500,
                'recipe' => [
                    'Espresso' => 18,
                    'Susu' => 150,
                ],
            ],
            [
                'name' => 'Kopi Susu Gula Aren',
                'category' => 'Coffee',
                'selling_price' => 23000,
                'cost_price' => 9000,
                'recipe' => [
                    'Espresso' => 18,
                    'Susu' => 120,
                    'Gula Aren' => 30,
                ],
                'variants' => [
                    ['name' => '250 ml', 'selling_price' => 23000, 'cost_price' => 9000, 'recipe_multiplier' => 1, 'sort_order' => 1],
                    ['name' => '500 ml', 'selling_price' => 43000, 'cost_price' => 17000, 'recipe_multiplier' => 2, 'sort_order' => 2],
                    ['name' => '1 Liter', 'selling_price' => 79000, 'cost_price' => 32000, 'recipe_multiplier' => 4, 'sort_order' => 3],
                ],
            ],
            [
                'name' => 'Chocolate',
                'category' => 'Non Coffee',
                'selling_price' => 22000,
                'cost_price' => 8000,
                'recipe' => [
                    'Cokelat Bubuk' => 30,
                    'Susu' => 150,
                ],
            ],
            [
                'name' => 'Lemon Tea',
                'category' => 'Tea',
                'selling_price' => 15000,
                'cost_price' => 4000,
                'recipe' => [
                    'Teh' => 10,
                    'Air' => 180,
                ],
            ],
            [
                'name' => 'Cheese Toast',
                'category' => 'Snack',
                'selling_price' => 20000,
                'cost_price' => 9000,
                'recipe' => [
                    'Roti' => 2,
                    'Keju' => 30,
                ],
            ],
        ])->each(function (array $data) use ($categories, $ingredients): void {
            $menu = Menu::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $categories->get($data['category'])->id,
                    'name' => $data['name'],
                    'selling_price' => $data['selling_price'],
                    'cost_price' => $data['cost_price'],
                    'is_active' => true,
                    'photo_path' => null,
                ],
            );

            $recipe = Recipe::query()->updateOrCreate(
                ['menu_id' => $menu->id],
                [
                    'name' => 'Resep '.$data['name'],
                    'is_active' => true,
                ],
            );

            $syncData = collect($data['recipe'])->mapWithKeys(function (int $quantity, string $ingredientName) use ($ingredients) {
                return [
                    $ingredients->get($ingredientName)->id => [
                        'quantity' => $quantity,
                    ],
                ];
            })->all();

            $recipe->ingredients()->sync($syncData);

            $variants = $data['variants'] ?? [
                [
                    'name' => 'Regular',
                    'selling_price' => $data['selling_price'],
                    'cost_price' => $data['cost_price'],
                    'recipe_multiplier' => 1,
                    'sort_order' => 0,
                ],
            ];

            foreach ($variants as $variant) {
                $menu->variants()->updateOrCreate(
                    ['name' => $variant['name']],
                    [
                        'selling_price' => $variant['selling_price'],
                        'cost_price' => $variant['cost_price'],
                        'is_active' => true,
                        'sort_order' => $variant['sort_order'],
                    ],
                );
            }
        });

        Customer::query()->updateOrCreate(
            ['phone_number' => '081234567890'],
            [
                'name' => 'Customer Umum',
                'note' => 'Customer default untuk transaksi walk-in.',
            ],
        );

        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Coffee Kita',
                'address' => 'Jl. Kopi Kita No. 1',
                'phone_number' => '081234567890',
                'logo_path' => null,
                'tax_percentage' => 10,
                'receipt_footer' => 'Terima kasih sudah berkunjung.',
            ],
        );
    }
}
