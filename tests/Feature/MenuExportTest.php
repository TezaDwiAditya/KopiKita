<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MenuExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authenticated_user_can_export_menu_variant_prices_to_excel(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Coffee',
            'slug' => 'coffee-'.uniqid(),
            'is_active' => true,
        ]);
        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Matcha Latte',
            'slug' => 'matcha-latte-'.uniqid(),
            'selling_price' => 25000,
            'cost_price' => 10000,
            'is_active' => true,
            'photo_path' => null,
        ]);

        MenuVariant::query()->create([
            'menu_id' => $menu->id,
            'name' => '500 ml',
            'selling_price' => 39000,
            'cost_price' => 16000,
            'profit_amount' => 23000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($user)
            ->get(route('admin.menus.export.excel'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->assertSee('List Menu dan Harga Varian')
            ->assertSee('Coffee')
            ->assertSee('Matcha Latte')
            ->assertSee('500 ml')
            ->assertSee('Rp 39.000')
            ->assertSee('Rp 16.000')
            ->assertSee('Rp 23.000');
    }
}
