<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
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
public function profile(): JsonResponse
    {
        // Fetch authenticated user via JW  T
        $customer = JWTAuth::user();

        // Return customer details (excluding sensitive info like password)
        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'created_at' => $customer->created_at,
        ]);
    }
}
