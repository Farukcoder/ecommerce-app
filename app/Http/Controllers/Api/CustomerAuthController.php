<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Log;

class CustomerAuthController extends Controller
{
    private const CUSTOMER_ROLE = 'customer';

    private const TOKEN_NAME = 'customer-api-token';

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^(\+88)?01[3-9]\d{8}$/', 'unique:users,phone'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'phone'    => '+88' . preg_replace('/^\+88/', '', $data['phone']),
            'email'    => $data['email'],
            'password' => $data['password'],
        ]);

        $this->assignCustomerRole($user);

        $token = $user->createToken(self::TOKEN_NAME, [self::CUSTOMER_ROLE])->plainTextToken;

        return response()->json([
            'message' => 'Customer account created successfully.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->makeUserPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $phone = '+88' . preg_replace('/^\+88/', '', $data['phone']);

        $user = User::query()->where('phone', $phone)->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $this->userIsCustomer($user)) {
            return response()->json([
                'message' => 'This account is not allowed to use the customer API.',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken(self::TOKEN_NAME, [self::CUSTOMER_ROLE])->plainTextToken;

        return response()->json([
            'message' => 'Customer login successful.',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => $this->makeUserPayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $this->userIsCustomer($user)) {
            return response()->json([
                'message' => 'Unauthorized customer account.',
            ], 403);
        }

        return response()->json([
            'user' => $this->makeUserPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user || ! $this->userIsCustomer($user)) {
            return response()->json([
                'message' => 'Unauthorized customer account.',
            ], 403);
        }

        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Customer logged out successfully.',
        ]);
    }

    protected function assignCustomerRole(User $user): void
    {
        $role = Role::firstOrCreate(
            ['slug' => self::CUSTOMER_ROLE],
            ['name' => Str::title(self::CUSTOMER_ROLE)]
        );

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role);

            return;
        }

        if (method_exists($user, 'roles')) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    protected function userIsCustomer(User $user): bool
    {
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(self::CUSTOMER_ROLE);
        }

        if (! method_exists($user, 'roles')) {
            return false;
        }

        return $user->roles()->where('slug', self::CUSTOMER_ROLE)->exists();
    }

    protected function makeUserPayload(User $user): array
    {
        $user->loadMissing('roles');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'roles' => $user->roles->pluck('slug')->values()->all(),
        ];
    }
}
