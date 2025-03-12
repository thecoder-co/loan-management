<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'email' => 'required|string|email|unique:customers',
        'password' => 'required|string|min:6'
    ]);

    $customer = Customer::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password'])
    ]);

    $token = JWTAuth::fromUser($customer);

    return response()->json(['token' => $token, 'user' => $customer], 201);
}
}
