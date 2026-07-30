<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_receive_purchase_order_adds_ingredient_stock(): void
    {
        $user = User::factory()->create();

        $ingredient = Ingredient::query()->create([
            'name' => 'Test Milk',
            'unit' => 'ml',
            'price' => 10,
            'minimum_stock' => 100,
            'current_stock' => 200,
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'po_number' => 'PO-TEST-001',
            'order_date' => today(),
            'created_by' => $user->id,
            'supplier_name' => 'Test Supplier',
            'discount' => 1000,
            'shipping_cost' => 2000,
            'status' => 'ordered',
        ]);

        $purchaseOrder->items()->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 50,
            'unit_price' => 20,
        ]);

        $purchaseOrder->recalculateTotals();

        $this->actingAs($user);

        app(PurchaseOrderService::class)->receive($purchaseOrder);

        $purchaseOrder->refresh();
        $ingredient->refresh();

        $this->assertSame('received', $purchaseOrder->status);
        $this->assertNotNull($purchaseOrder->received_at);
        $this->assertSame(250, $ingredient->current_stock);
        $this->assertSame(20, $ingredient->price);
        $this->assertSame(1000, $purchaseOrder->subtotal);
        $this->assertSame(2000, $purchaseOrder->grand_total);
        $this->assertSame(1, $purchaseOrder->stockMovements()->where('type', 'purchase')->count());
    }
}
