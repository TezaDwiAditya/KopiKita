<?php

namespace Tests\Feature;

use App\Filament\Pages\KitchenQueue;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Services\KitchenOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KitchenOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_kitchen_order_item_can_move_until_served(): void
    {
        $user = User::factory()->create();
        $item = $this->createKitchenItem($user);

        $service = app(KitchenOrderService::class);

        $service->markPreparing($item);
        $item->refresh();

        $this->assertSame('preparing', $item->kitchen_status);
        $this->assertNotNull($item->preparing_at);

        $service->markReady($item);
        $item->refresh();

        $this->assertSame('ready', $item->kitchen_status);
        $this->assertNotNull($item->ready_at);

        $service->markServed($item);
        $item->refresh();

        $this->assertSame('served', $item->kitchen_status);
        $this->assertNotNull($item->served_at);
    }

    public function test_kitchen_queue_can_mark_all_active_items_ready(): void
    {
        $user = User::factory()->create();
        $pendingItem = $this->createKitchenItem($user, 'pending');
        $preparingItem = $this->createKitchenItem($user, 'preparing');
        $readyItem = $this->createKitchenItem($user, 'ready');
        $servedItem = $this->createKitchenItem($user, 'served');

        $this->actingAs($user);

        Livewire::test(KitchenQueue::class)
            ->call('markAllReady');

        $pendingItem->refresh();
        $preparingItem->refresh();
        $readyItem->refresh();
        $servedItem->refresh();

        $this->assertSame('ready', $pendingItem->kitchen_status);
        $this->assertNotNull($pendingItem->preparing_at);
        $this->assertNotNull($pendingItem->ready_at);
        $this->assertSame('ready', $preparingItem->kitchen_status);
        $this->assertNotNull($preparingItem->ready_at);
        $this->assertSame('ready', $readyItem->kitchen_status);
        $this->assertSame('served', $servedItem->kitchen_status);
    }

    private function createKitchenItem(User $user, string $kitchenStatus = 'pending')
    {
        $category = Category::query()->create([
            'name' => 'Test Food',
            'slug' => 'test-food-'.uniqid(),
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Nasi Goreng',
            'slug' => 'test-nasi-goreng-'.uniqid(),
            'selling_price' => 20000,
            'cost_price' => 8000,
            'is_active' => true,
            'photo_path' => null,
        ]);

        $transaction = Transaction::query()->create([
            'invoice_number' => 'INV-KITCHEN-'.uniqid(),
            'transaction_date' => now(),
            'cashier_id' => $user->id,
            'customer_id' => null,
            'subtotal' => 20000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 20000,
            'status' => 'draft',
            'note' => null,
        ]);

        return $transaction->items()->create([
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => 1,
            'price' => 20000,
            'subtotal' => 20000,
            'note' => 'Tidak pedas',
            'kitchen_status' => $kitchenStatus,
        ]);
    }
}
