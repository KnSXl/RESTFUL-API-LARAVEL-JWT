<?php

namespace App\Http\Controllers;
  
use App\Http\Controllers\ResponseController;
use App\Models\User;
use App\Http\Requests\StoreUpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
  
class AuthController extends ResponseController
{
 
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(StoreUpdateUserRequest $request) {

        // Encrypt password
        $requestData = $request->all();
        $requestData['password'] = Hash::make($requestData['password']);
        
        $user = User::create($requestData);
        
        return $this->sendResponse('User registered successfully', new UserResource($user), 200);
    }
  
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $validated = request()->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|max:255',
        ]);

        if (! $token = auth()->attempt($validated)) {
            return $this->sendError('Unregistered credentials', 404);
        }

        $user = auth()->user();

        $success = [
            'token' => $token,
            'user'  => new UserResource($user),
        ];

        return $this->sendResponse('User login successfully', $success, 200);
    }
  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        
        return $this->sendResponse('Successfully logged out', [], 200);
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
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}
