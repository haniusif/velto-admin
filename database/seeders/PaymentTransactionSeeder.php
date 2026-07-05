<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Auto-generated data snapshot for `payment_transactions` (7 rows).
 * Regenerated from the live `velto_admin` database.
 */
class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('payment_transactions')->truncate();

        $rows = [
            [
                'id' => 2,
                'customer_id' => 10,
                'appointment_id' => null,
                'gateway' => 'arb',
                'purpose' => 'wallet_topup',
                'action' => 'purchase',
                'status' => 'pending',
                'amount' => '50.00',
                'currency' => 'SAR',
                'track_id' => 'TU-10-0HJYWJSY0V',
                'payment_id' => '600202618465982103',
                'trans_id' => null,
                'ref' => null,
                'result_code' => null,
                'error_code' => null,
                'error_text' => null,
                'response_payload' => null,
                'created_at' => '2026-07-03 08:40:34',
                'updated_at' => '2026-07-03 08:40:35',
            ],
            [
                'id' => 3,
                'customer_id' => 10,
                'appointment_id' => null,
                'gateway' => 'arb',
                'purpose' => 'wallet_topup',
                'action' => 'purchase',
                'status' => 'failed',
                'amount' => '50.00',
                'currency' => 'SAR',
                'track_id' => 'TU-10-5PAGZIEAPH',
                'payment_id' => '600202618465887633',
                'trans_id' => null,
                'ref' => null,
                'result_code' => 'CANCELED',
                'error_code' => null,
                'error_text' => null,
                'response_payload' => '{"amt": "50.0", "ref": "", "date": "", "udf1": "wallet_topup", "udf2": "", "udf3": "", "udf4": "", "udf5": "", "udf6": "", "udf7": "", "udf8": "", "udf9": "", "udf10": "", "result": "CANCELED", "trackId": "TU-10-5PAGZIEAPH", "paymentId": 600202618465887633, "actionCode": "1", "paymentTimestamp": "2026-07-03T11:44:29.720Z"}',
                'created_at' => '2026-07-03 08:43:44',
                'updated_at' => '2026-07-03 08:44:29',
            ],
            [
                'id' => 4,
                'customer_id' => 10,
                'appointment_id' => 7,
                'gateway' => 'arb',
                'purpose' => 'booking',
                'action' => 'purchase',
                'status' => 'captured',
                'amount' => '30.00',
                'currency' => 'SAR',
                'track_id' => '7',
                'payment_id' => '600202618434232548',
                'trans_id' => 'TRNC7',
                'ref' => 'REFC7',
                'result_code' => 'CAPTURED',
                'error_code' => null,
                'error_text' => null,
                'response_payload' => '{"amt": "30.00", "ref": "REFC7", "result": "CAPTURED", "trackId": "7", "transId": "TRNC7", "paymentId": "600202618434232548"}',
                'created_at' => '2026-07-03 08:47:44',
                'updated_at' => '2026-07-03 08:47:45',
            ],
            [
                'id' => 5,
                'customer_id' => 10,
                'appointment_id' => 8,
                'gateway' => 'arb',
                'purpose' => 'booking',
                'action' => 'purchase',
                'status' => 'failed',
                'amount' => '20.00',
                'currency' => 'SAR',
                'track_id' => '8',
                'payment_id' => '600202618434256727',
                'trans_id' => null,
                'ref' => null,
                'result_code' => 'NOT CAPTURED',
                'error_code' => null,
                'error_text' => null,
                'response_payload' => '{"amt": "20.00", "result": "NOT CAPTURED", "trackId": "8", "paymentId": "600202618434256727"}',
                'created_at' => '2026-07-03 08:48:33',
                'updated_at' => '2026-07-03 08:48:34',
            ],
            [
                'id' => 6,
                'customer_id' => 10,
                'appointment_id' => null,
                'gateway' => 'arb',
                'purpose' => 'wallet_topup',
                'action' => 'purchase',
                'status' => 'captured',
                'amount' => '75.00',
                'currency' => 'SAR',
                'track_id' => 'TU-10-CFHKCSBPFW',
                'payment_id' => '600202618434257586',
                'trans_id' => 'TRTU',
                'ref' => 'RFTU',
                'result_code' => 'CAPTURED',
                'error_code' => null,
                'error_text' => null,
                'response_payload' => '{"amt": "75.00", "ref": "RFTU", "result": "CAPTURED", "trackId": "TU-10-CFHKCSBPFW", "transId": "TRTU", "paymentId": "600202618434257586"}',
                'created_at' => '2026-07-03 08:48:34',
                'updated_at' => '2026-07-03 08:48:35',
            ],
            [
                'id' => 7,
                'customer_id' => 10,
                'appointment_id' => 9,
                'gateway' => 'arb',
                'purpose' => 'booking',
                'action' => 'purchase',
                'status' => 'pending',
                'amount' => '40.00',
                'currency' => 'SAR',
                'track_id' => '9',
                'payment_id' => '600202618434374198',
                'trans_id' => null,
                'ref' => null,
                'result_code' => null,
                'error_code' => null,
                'error_text' => null,
                'response_payload' => null,
                'created_at' => '2026-07-03 08:52:27',
                'updated_at' => '2026-07-03 08:52:28',
            ],
            [
                'id' => 8,
                'customer_id' => 10,
                'appointment_id' => 10,
                'gateway' => 'arb',
                'purpose' => 'booking',
                'action' => 'purchase',
                'status' => 'pending',
                'amount' => '20.00',
                'currency' => 'SAR',
                'track_id' => '10',
                'payment_id' => '600202618463415732',
                'trans_id' => null,
                'ref' => null,
                'result_code' => null,
                'error_code' => null,
                'error_text' => null,
                'response_payload' => null,
                'created_at' => '2026-07-03 10:06:07',
                'updated_at' => '2026-07-03 10:06:08',
            ],
        ];

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('payment_transactions')->insert($chunk);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
