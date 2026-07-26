<?php

namespace App\Console\Commands;

use App\Models\CustomerPackage;
use Illuminate\Console\Command;

/**
 * Settle lapsed plans in the database.
 *
 * Nothing depends on this for correctness — CustomerPackage::isUsable() checks
 * expires_at live, so a lapsed plan is already refused and already reads as
 * expired in the panel. What this fixes is the stored column: without it,
 * every plan ever sold keeps status='active' forever, and any raw SQL
 * reporting overcounts active plans indefinitely.
 */
class ExpireCustomerPackages extends Command
{
    protected $signature = 'packages:expire';

    protected $description = 'Mark prepaid plans whose validity window has passed as expired';

    public function handle(): int
    {
        // Only ACTIVE rows: a pending plan never started, and cancelled ones
        // are already terminal.
        $count = CustomerPackage::query()
            ->where('status', CustomerPackage::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => CustomerPackage::STATUS_EXPIRED]);

        $this->info("Expired {$count} lapsed plan(s).");

        return self::SUCCESS;
    }
}
