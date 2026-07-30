<?php

namespace App\Services\Account;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Deletes a customer account and everything personal attached to it.
 *
 * Required by App Store Guideline 5.1.1(v): an app that lets people create an
 * account must let them delete it from inside the app. Deactivating is not
 * enough, so this is a real delete.
 *
 * What goes: the customer row, and by cascade their vehicles, saved addresses,
 * appointments, wallet transactions and balance, packages, devices, push
 * notifications, reviews and promo redemptions. Their API tokens are revoked
 * first so any other signed-in device stops working immediately.
 *
 * What stays: payment_transactions, detached by the migration that made
 * customer_id nullable. Those rows carry no identity once detached — amount,
 * currency, gateway reference, status — and are the financial record.
 */
class DeleteCustomerAccount
{
    /**
     * @return int The id of the account that was deleted, for logging.
     */
    public function __invoke(Customer $customer): int
    {
        $id = $customer->id;

        // Outside the transaction: revoking access should hold even if the
        // delete below fails, because the customer has asked to be gone.
        $customer->tokens()->delete();

        DB::transaction(function () use ($customer) {
            // Release any promo codes still held by pending appointments so the
            // codes do not stay spent against an account that no longer exists.
            foreach ($customer->appointments()->whereNotNull('promo_code_id')->get() as $appointment) {
                $appointment->releasePromoCode();
            }

            $customer->delete();
        });

        Log::info('Customer account deleted at the customer request', ['customer_id' => $id]);

        return $id;
    }
}
