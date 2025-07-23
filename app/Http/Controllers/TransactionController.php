<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Point;
use App\Helpers\SignatureHelper;
use Carbon\Carbon;

class TransactionController extends Controller
{
   public function store(Request $request)
    {
        // if (!SignatureHelper::isValid($request)) {
        //     return response()->json(['message' => 'Invalid signature'], 401);
        // }

        // Convert ISO 8601 to MySQL datetime format
        $body = $request->only(['user_id', 'amount', 'description', 'transacted_at']);
        $body['transacted_at'] = date('Y-m-d H:i:s', strtotime($body['transacted_at']));

        if ($body['amount'] <= 0) {
            return response()->json(['message' => 'Amount must be greater than 0'], 422);
        }

        $transaction = Transaction::create($body);
        $points = floor($body['amount'] / 1000);

        Point::create([
            'transaction_id' => $transaction->id,
            'points' => $points
        ]);

        return response()->json([
            'message' => 'Transaction created',
            'points' => $points
        ], 201);
    }

    /**
     * Get transactions list with points.
     */
    public function index(Request $request)
    {
        $query = Transaction::with('point');

        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('transacted_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        $transactions = $query->orderBy('transacted_at', 'desc')
                              ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $transactions->map(function ($tx) {
                return [
                    'user_id' => $tx->user_id,
                    'amount' => $tx->amount,
                    'points' => $tx->point->points ?? 0,
                    'description' => $tx->description,
                    'transacted_at' => $tx->transacted_at,
                ];
            }),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ]
        ]);
    }

    /**
     * Seed 1000 dummy transactions.
     */
    public function seed()
    {
        for ($i = 0; $i < 1000; $i++) {
            $amount = rand(10000, 500000);
            $transaction = Transaction::create([
                'user_id' => 1,
                'amount' => $amount,
                'description' => 'Dummy transaction #' . $i,
                'transacted_at' => Carbon::now()->subDays(rand(0, 365)),
            ]);

            Point::create([
                'transaction_id' => $transaction->id,
                'points' => floor($amount / 1000),
            ]);
        }

        return response()->json(['message' => 'Seeded 1000 transactions']);
    }
}