<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Password\GetPasswordsFromExtensionRequest;
use App\Http\Resources\Password\PasswordResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'getPasswordsFromExtension']]);
    }

    public function getPasswordsFromExtension(GetPasswordsFromExtensionRequest $request) {

        $attributes = $request->validated();

        $user = User::where(['email' => $attributes['email'], 'status_id' => 2])
            ->whereNotNull('password')
            ->whereNotNull('master_password')
            ->first();

        if (!$user) throw Http404::makeForField('user', 'not-found');

        $expirationDate = $user->expirationDate();
        if (is_null($expirationDate) || $expirationDate < Carbon::now()) {
            throw Http422::makeForField('user', 'plan-expired');
        }

        if (!Hash::check($attributes['password'], $user->password)) {
            throw Http422::makeForField('password', 'incorrect');
        }

        if (!Hash::check($attributes['masterPassword'], $user->master_password)) {
            throw Http422::makeForField('master-password', 'incorrect');
        }

        $passwords = $user->passwords()->whereNotNull('password')->get();
        return PasswordResource::collection($passwords);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $user = User::where(['email' => $request->email, 'status_id' => 2])
            ->whereNotNull('password')
            ->whereNotNull('master_password')
            ->first();

        if (!$user) {
            throw Http404::makeForField('user', 'not-found');
        }

        $expirationDate = $user->expirationDate();

        if (is_null($expirationDate) || $expirationDate < Carbon::now()) {
            throw Http422::makeForField('user', 'plan-expired');
        }

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function checkMasterPassword()
    {
        $masterPassword = request('master');
        $userMasterPassword = Auth::user()->master_password;
        $response = Hash::check($masterPassword, $userMasterPassword);
        return response()->json($response);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $daysToExpire = 7;
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => (auth()->factory()->getTTL() * 60) * $daysToExpire,
            'user' => auth()->user()->name,
            'shuffled' => auth()->user()->master_password
        ]);
    }
}
