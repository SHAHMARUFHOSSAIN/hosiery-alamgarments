<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillStoreTest extends TestCase
{
    public function test_bill_store_creates_record(): void
    {
        $user = User::first();
        $this->assertNotNull($user, 'No user found in database');

        $response = $this->actingAs($user)->post(route('bills.store'), [
            'customer_id' => 1,
            'bill_no' => 'TEST-FEATURE-001',
            'bill_amount' => 300.00,
            'discount' => 0,
            'payment_type' => 'cash',
            'payment_amount' => 300.00,
        ]);

        $response->assertRedirect(route('bills.index'));
        $response->assertSessionHas('success');

        $bill = Bill::where('bill_no', 'TEST-FEATURE-001')->first();
        $this->assertNotNull($bill, 'Bill was NOT created in database');
    }
}
