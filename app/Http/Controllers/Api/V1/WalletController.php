<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WalletTransactionResource;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $customer = $request->user();
        $txs = $customer->walletTransactions()
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => [
                'balance' => (float) $customer->wallet_balance,
                'currency' => 'SAR',
                'transactions' => WalletTransactionResource::collection($txs),
            ],
        ]);
    }

    public function topUp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:5000'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = $request->user();

        $tx = $customer->walletTransactions()->create([
            'kind' => WalletTransaction::KIND_TOP_UP,
            'amount' => (float) $data['amount'],
            'note' => $data['note'] ?? null,
        ]);

        return response()->json([
            'data' => [
                'balance' => (float) $customer->fresh()->wallet_balance,
                'currency' => 'SAR',
                'transaction' => new WalletTransactionResource($tx),
            ],
        ], 201);
    }
}
