<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\{Loan, Transaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoanController extends Controller
{
    // Take loan
    public function takeLoan(Request $request)
{
    $customer = JWTAuth::user();
    if (!$customer) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $validated = $request->validate([
        'loan_amount' => 'required|numeric',
        'interest_rate' => 'required|numeric',
        'repayment_plan' => 'required|integer|min:1|max:12',
    ]);

    DB::beginTransaction();
    try {
        $total_due = $validated['loan_amount'] + ($validated['loan_amount'] * $validated['interest_rate'] / 100);
        $monthly_installment = round($total_due / $validated['repayment_plan'], 2);

        $loan = $customer->loans()->create([
            'loan_amount' => $validated['loan_amount'],
            'interest_rate' => $validated['interest_rate'],
            'repayment_plan' => $validated['repayment_plan'],
            'start_date' => now(),
            'end_date' => now()->addMonths($validated['repayment_plan']),
            'total_amount_due' => $total_due,
            'monthly_installment' => $monthly_installment,
            'remaining_balance' => $total_due,
        ]);

        $customer->increment('balance', $validated['loan_amount']);

        Transaction::create([
            'customer_id' => $customer->id,
            'type' => 'loan',
            'direction' => 'credit',
            'amount' => $validated['loan_amount'],
        ]);

        DB::commit();

        return response()->json(['loan' => $loan], 201);
    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['error' => 'Failed to process loan: ' . $e->getMessage()], 500);
    }
}


    public function repay(Request $request, $loanId)
{
    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);

    $customer = JWTAuth::user();

    $loan = Loan::where('customer_id', $customer->id)->findOrFail($loanId);

    if ($request->amount > $customer->balance) {
        return response()->json([
            'error' => 'Insufficient balance. Please add funds to your account.'
        ], 400);
    }

    if ($request->amount > $loan->remaining_balance) {
        return response()->json([
            'error' => 'Payment exceeds remaining loan balance.'
        ], 400);
    }

    DB::beginTransaction();
    try {
        // Deduct from customer balance

        $customer->decrement('balance', $request->amount);

        // Reduce loan remaining balance
        $loan->remaining_balance -= $request->amount;
        $loan->save();

        // Record the transaction clearly
        Transaction::create([
            'customer_id' => $customer->id,
            'type' => 'repayment',
            'direction' => 'debit',
            'amount' => $request->amount,
        ]);

        DB::commit();

        return response()->json([
            'message' => 'Payment recorded successfully',
            'loan' => $loan,
            'remaining_balance' => $loan->remaining_balance,
            'current_balance' => $customer->balance,
        ], 200);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['error' => 'Transaction failed: ' . $e->getMessage()], 500);
    }
}


    public function index()
    {
        $customer = JWTAuth::user();
        $loans = $customer->loans()->get();

        return response()->json(['loans' => $loans]);
    }

    public function show($loanId)
    {
        $loan = Loan::with('repayments', 'notifications')->findOrFail($loanId);
        return response()->json(['loan' => $loan]);
    }
}
