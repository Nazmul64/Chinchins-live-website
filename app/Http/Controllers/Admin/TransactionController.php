<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of all coin transactions.
     */
    public function index(Request $request)
    {
        $query = CoinTransaction::with('user')->latest();

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('account_id', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        $stats = [
            'total_transactions' => CoinTransaction::count(),
            'total_added' => CoinTransaction::where('amount', '>', 0)->sum('amount'),
            'total_deducted' => abs(CoinTransaction::where('amount', '<', 0)->sum('amount')),
        ];

        return view('admin.transactions.index', compact('transactions', 'stats'));
    }
}
