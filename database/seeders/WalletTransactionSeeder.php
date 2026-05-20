<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;

class WalletTransactionSeeder extends Seeder
{
    public function run(): void
    {
        Customer::query()->take(5)->get()->each(function (Customer $c) {
            // Reset to a known starting balance/state for the demo customers.
            $c->walletTransactions()->delete();
            $c->update(['wallet_balance' => 0]);

            $rows = [
                ['kind' => 'top_up',  'amount' =>  200.00, 'note' => 'Initial top-up', 'days' => 14],
                ['kind' => 'booking', 'amount' =>  -99.00, 'note' => 'Express wash',   'days' => 10],
                ['kind' => 'top_up',  'amount' =>  100.00, 'note' => 'Top-up',         'days' =>  6],
                ['kind' => 'refund',  'amount' =>   25.00, 'note' => 'Late arrival',   'days' =>  3],
                ['kind' => 'booking', 'amount' =>  -49.00, 'note' => 'Express wash',   'days' =>  1],
            ];

            foreach ($rows as $r) {
                $tx = $c->walletTransactions()->create([
                    'kind' => $r['kind'],
                    'amount' => $r['amount'],
                    'note' => $r['note'],
                ]);
                $tx->update(['created_at' => now()->subDays($r['days'])]);
            }
        });
    }
}
