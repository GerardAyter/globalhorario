<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return $this->error('Invalid credentials', null, 401);
        }

        // create a personal access token (Sanctum)
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load([
            'employee.company',
            'tenant.whitelabel',
            'tenant.planFeatureFlags' => fn ($q) => $q->where('actiu', true)->select('id', 'tenant_id', 'feature'),
        ]);
        return $this->success($user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email,' . $user->id,
            'current_password'      => 'required_with:password|string',
            'password'              => 'nullable|string|min:8|confirmed',
            'password_confirmation' => 'nullable|string',
        ]);

        if ($request->filled('password')) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return $this->error('Credencials incorrectes', [
                    'current_password' => ['La contrasenya actual és incorrecta.'],
                ], 422);
            }
            $user->password = Hash::make($request->input('password'));
        }

        $user->name  = $request->input('name');
        $user->email = $request->input('email');
        $user->save();

        return $this->success($user->load('employee.company'));
    }
}
