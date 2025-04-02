<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class CustomerController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
   public function register(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:customers',
        'password' => 'required|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $customer = Customer::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Generate a JWT Token (correct method)
    $token = JWTAuth::fromUser($customer);

    return response()->json([
        'message' => 'User registered successfully.',
        'token' => $token,
        'user' => $customer,
    ], 201);
}
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
   public function registerAdmin(Request $request): JsonResponse
{
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:customers',
        'password' => 'required|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 422);
    }

    $customer = Customer::create([
        'name' => $request->name,
        'email' => $request->email,
        'role' => 'admin',
        'password' => bcrypt($request->password),
    ]);

    // Generate a JWT Token (correct method)
    $token = JWTAuth::fromUser($customer);

    return response()->json([
        'message' => 'User registered successfully.',
        'token' => $token,
        'user' => $customer,
    ], 201);
}
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
   public function login(Request $request): JsonResponse
{
    $credentials = $request->only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {

        return response()->json(['error' => 'Unauthorized'], 401);
    }



    return response()->json([
        'message' => 'Login successful.',
        'token' => $token,
        'user' => auth()->user()
    ]);
}

/**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
   public function loginAdmin(Request $request): JsonResponse
{
    $credentials = $request->only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {

        return response()->json(['error' => 'Unauthorized'], 401);
    }
    if(auth()->user()->role !== 'admin'){
        return response()->json(['error' => 'Unauthorized'], 401);

    }
    return response()->json([
        'message' => 'Login successful.',
        'token' => $token,
        'user' => auth()->user()
    ]);
}
public function profile(): JsonResponse
    {
        // Fetch authenticated user via JW  T
        $customer = JWTAuth::user();

        // Return customer details (excluding sensitive info like password)
        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'balance' => $customer->balance,
            'created_at' => $customer->created_at,
        ]);
    }

    public function addBalance(Request $request): JsonResponse
{
    $request->validate(['amount' => 'required|numeric|min:1']);

    $customer = JWTAuth::user();
    $customer->increment('balance', $request->amount);

    $transaction = Transaction::create([
        'customer_id' => $customer->id,
        'direction' => 'credit', // you might use "balance_topup" explicitly if clearer
        'type' => 'balance_topup', // you might use "balance_topup" explicitly if clearer
        'amount' => $request->amount,
        'created_at' => now()
    ]);

    return response()->json([
        'message' => 'Balance updated successfully.',
        'balance' => $customer->balance,
        'transaction' => $transaction,
    ], 201);
}

public function transactions(): JsonResponse
{
    $customer = JWTAuth::user();
    $transactions = $customer->transactions()->latest()->get();

    return response()->json([
        'transactions' => $transactions
    ]);
}

public function getAllCustomers(): JsonResponse
{

    $customer = JWTAuth::user();

    if($customer->role !== 'admin'){
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    $customers = Customer::all();

    return response()->json([
        'customers' => $customers
    ]);
}

public function payService(Request $request)
{
    $request->validate([
        'service_type' => 'required|in:airtime,internet,cable',
        'amount' => 'required|numeric|min:1',
        'description' => 'nullable|string|max:255',
    ]);

    $customer = JWTAuth::user();

    if ($customer->balance < $request->amount) {
        return response()->json(['error' => 'Insufficient balance'], 400);
    }

    DB::beginTransaction();

    try {
        // Deduct amount explicitly from customer balance
        $customer->decrement('balance', $request->amount);

        // Record transaction explicitly
        Transaction::create([
            'customer_id' => $customer->id,
            'type' => $request->service_type,
            'direction' => 'debit',
            'amount' => $request->amount,
            'description' => $request->description ?? ucfirst($request->service_type).' payment',
        ]);

        DB::commit();

        return response()->json([
            'message' => ucfirst($request->service_type).' payment successful.',
            'service_type' => $request->service_type,
            'amount' => $request->amount,
            'current_balance' => $customer->fresh()->balance,
        ], 200);

    } catch (\Exception $e) {
        DB::rollback();

        return response()->json(['error' => 'Payment failed: ' . $e->getMessage()], 500);
    }
}


}
