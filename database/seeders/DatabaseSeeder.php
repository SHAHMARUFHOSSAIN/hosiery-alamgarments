<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Due;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $c1 = Customer::create(['name' => 'John Smith', 'mobile' => '555-0101', 'created_by' => $user->id]);
        $c2 = Customer::create(['name' => 'Sarah Johnson', 'mobile' => '555-0102', 'created_by' => $user->id]);
        $c3 = Customer::create(['name' => 'Mike Brown', 'mobile' => '555-0103', 'created_by' => $admin->id]);

        $b1 = Bill::create(['bill_no' => 'BILL-001', 'customer_id' => $c1->id, 'bill_amount' => 500, 'discount' => 0, 'user_id' => $user->id]);
        Payment::create(['bill_id' => $b1->id, 'payment_type' => 'due', 'amount' => 0, 'due_date' => now()->addDays(7)]);
        Due::create(['customer_id' => $c1->id, 'bill_id' => $b1->id, 'amount' => 500, 'due_date' => now()->addDays(7), 'status' => 'pending', 'created_by' => $user->id]);

        $b2 = Bill::create(['bill_no' => 'BILL-002', 'customer_id' => $c2->id, 'bill_amount' => 300, 'discount' => 50, 'user_id' => $user->id]);
        Payment::create(['bill_id' => $b2->id, 'payment_type' => 'due', 'amount' => 100, 'due_date' => now()->addDays(14)]);
        Due::create(['customer_id' => $c2->id, 'bill_id' => $b2->id, 'amount' => 150, 'due_date' => now()->addDays(14), 'status' => 'pending', 'created_by' => $user->id]);

        $b3 = Bill::create(['bill_no' => 'BILL-003', 'customer_id' => $c3->id, 'bill_amount' => 200, 'discount' => 0, 'user_id' => $admin->id]);
        Payment::create(['bill_id' => $b3->id, 'payment_type' => 'due', 'amount' => 0, 'due_date' => now()->addDays(3)]);
        Due::create(['customer_id' => $c3->id, 'bill_id' => $b3->id, 'amount' => 200, 'due_date' => now()->addDays(3), 'status' => 'pending', 'created_by' => $admin->id]);

        $b4 = Bill::create(['bill_no' => 'BILL-004', 'customer_id' => $c1->id, 'bill_amount' => 100, 'discount' => 0, 'user_id' => $user->id]);
        Payment::create(['bill_id' => $b4->id, 'payment_type' => 'cash', 'amount' => 100]);
        Due::create(['customer_id' => $c1->id, 'bill_id' => $b4->id, 'amount' => 100, 'due_date' => now()->subDays(5), 'status' => 'paid', 'created_by' => $user->id]);
    }
}