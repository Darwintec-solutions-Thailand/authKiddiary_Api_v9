<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
// use Validator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        // dd('ss');
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            // 'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            // 'role' => 'required',
            // 'password' => 'required',
            // 'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');
    }

    public function login(Request $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->accessToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User login successfully. ');
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }

    public function refeshToken()
    {
        // dd(Auth::user()->token());
        $user = Auth::user();
        if ($user) {
            Auth::user()->token()->revoke();
            Auth::user()->tokens->each(function ($token, $key) {
                $token->delete();
            });
            Session::flush();

            $token =  $user->createToken('MyApp')->accessToken;

            return response()->json([
                'success' => true,
                'tokenType' => ' Bearer',
                'access_token' => $token
            ]);
            // return response()->json(['success' => true, 'newToken' => $token]);
        } else {
            return response()->json(['success' => false, 'msg' => 'User is not Authenticated.']);
        }
    }

    public function logout()
    { 
        if (Auth::check()) {
            Auth::user()->token()->revoke();
            Auth::user()->tokens->each(function($token, $key) {
                $token->delete();
            });
            Session::flush();

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        }
    }
}
