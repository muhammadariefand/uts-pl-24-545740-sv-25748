<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $transactions = $request->user()
            ->transactions()
            ->latest('transaction_date')
            ->latest()
            ->get();

        return response()->json([
            'data' => $transactions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
        ]);

        $transaction = $request->user()
            ->transactions()
            ->create($validated);

        return response()->json([
            'message' => 'Transaksi berhasil dibuat.',
            'data' => $transaction,
        ], 201);
    }

    public function show(Request $request, Transaction $transaction): JsonResponse
    {
        $this->ensureOwner($request, $transaction);

        return response()->json([
            'data' => $transaction,
        ]);
    }

    public function update(
        Request $request,
        Transaction $transaction
    ): JsonResponse {
        $this->ensureOwner($request, $transaction);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaksi berhasil diubah.',
            'data' => $transaction->fresh(),
        ]);
    }

    public function destroy(
        Request $request,
        Transaction $transaction
    ): JsonResponse {
        $this->ensureOwner($request, $transaction);

        $transaction->delete();

        return response()->json([
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    private function ensureOwner(
        Request $request,
        Transaction $transaction
    ): void {
        abort_unless(
            $transaction->user_id === $request->user()->id,
            404
        );
    }
}