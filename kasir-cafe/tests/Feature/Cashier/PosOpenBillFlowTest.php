<?php

namespace Tests\Feature\Cashier;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosOpenBillFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_opens_new_draft_after_holding_current_order(): void
    {
        $this->withoutMiddleware();

        $user = $this->createUser();
        $product = $this->createProduct();
        $sale = $this->createSaleWithLine($user, $product, 'DRAFT');

        $this->actingAs($user)->post(route('cashier.pos.hold'), [
            'sale_id' => $sale->id,
            'table_no' => 'A1',
            'customer_name' => 'Customer Lama',
        ])->assertRedirect(route('cashier.pos'));

        $sale->refresh();
        $this->assertSame('OPEN', $sale->status);

        $this->withViewErrors([]);

        $this->actingAs($user)
            ->get(route('cashier.pos'))
            ->assertOk()
            ->assertViewHas('sale', fn (Sale $activeSale) => $activeSale->status === 'DRAFT'
                && $activeSale->id !== $sale->id);

        $this->assertSame(1, Sale::where('status', 'OPEN')->count());
        $this->assertSame(1, Sale::where('status', 'DRAFT')->count());
    }

    public function test_open_bill_can_still_be_opened_explicitly_and_shows_new_order_button(): void
    {
        $this->withoutMiddleware();

        $user = $this->createUser();
        $product = $this->createProduct();
        $openSale = $this->createSaleWithLine($user, $product, 'OPEN');

        $this->withViewErrors([]);

        $this->actingAs($user)
            ->get(route('cashier.pos', ['sale_id' => $openSale->id]))
            ->assertOk()
            ->assertViewHas('sale', fn (Sale $activeSale) => $activeSale->id === $openSale->id
                && $activeSale->status === 'OPEN')
            ->assertSee('Pesanan Baru');
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'Cashier Test',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
        ]);
    }

    private function createProduct(): Product
    {
        return Product::create([
            'name' => 'Es Kopi',
            'price_default' => 10000,
            'menu_type' => Product::TYPE_DRINK,
            'is_active' => true,
        ]);
    }

    private function createSaleWithLine(User $user, Product $product, string $status): Sale
    {
        $sale = Sale::create([
            'receipt_no' => 'NT/17/05/2026/' . str_pad((string) (Sale::count() + 1), 6, '0', STR_PAD_LEFT),
            'status' => $status,
            'cashier_id' => $user->id,
            'total' => 10000,
        ]);

        SaleLine::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 10000,
        ]);

        return $sale;
    }
}
