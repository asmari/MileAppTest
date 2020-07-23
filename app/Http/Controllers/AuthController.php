<?php

namespace App\Http\Controllers;

use App\ApiCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{

    /**
     * Get a JWT token via given credentials.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return JsonResponse|Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if ($validator->fails()){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Login Details'],
                ['error_message'=>$validator->messages()->toArray()]
            );
        }
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return RB::error(
            ApiCode::CLIENT_UNAUTHORIZED,
            ['error' => 'Invalid Login Details'],
            ['error_message' => trans('api.auth.login_failed')]
        );
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me()
    {
        $user = $this->guard()->user();
        $user->roles->all();
        return RB::success($user,ApiCode::SUCCESS_OK);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return RB::success(['message' => 'Successfully logged out'],ApiCode::SUCCESS_OK);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken($token)
    {
        return RB::success([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->guard()->factory()->getTTL() * 60,
        ],ApiCode::SUCCESS_OK);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard('api');
    }
}
