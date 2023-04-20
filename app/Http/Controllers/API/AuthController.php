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
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\HttpStatus;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

use Laravel\Passport\Client;

class AuthController extends BaseController
{

    public function clients()
{
    $user_id = Auth::id(); // get current user id
    
    $clients = Client::where('user_id', $user_id)->get(); // get all clients of the user
    return $clients;
}

    // public function register(Request $request)
    // {
    //     // dd('ss');
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         // 'username' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required',
    //         // 'role' => 'required',
    //         // 'password' => 'required',
    //         // 'c_password' => 'required|same:password',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->sendError('Validation Error.', $validator->errors());
    //     }

    //     $input = $request->all();
    //     $input['password'] = bcrypt($input['password']);
    //     $user = User::create($input);
    //     $success['token'] =  $user->createToken('MyApp')->accessToken;
    //     $success['name'] =  $user->name;

    //     return $this->sendResponse($success, 'User register successfully.');
    // }
    protected $routeMiddleware = [
        'client' => CheckClientCredentials::class,
    ];

    
    public function register(Request $request)
    {
        // Check inputs validation?
        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:user',
//            'username' => 'required|max:255|unique:user',
            'password' => 'required|min:6',
            'citizen_id' => 'nullable|size:13',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->jsonFailed(HttpStatus::BAD_REQUEST, $validator->errors()->first());
        }

        // Create the user
        $user = new User;
        $user->email = $request->get('email');
        $user->username = $request->get('email'); // Copy email to username
        $user->password = Hash::make($request->get('password'));
        $user->name = $request->get('name');
        $user->citizen_id = $request->get('citizen_id');
        $user->save();

        // TODO: Just work with access token for now.
        $token = $user->createToken(null)->accessToken;

        return response()->json([
            "token_type" => "Bearer",
            "expires_in" => 0,
            "access_token" => $token
        ]);
    }

    public function login(Request $request)
    {
        
        
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            $token =  $user->createToken('MyApp')->accessToken;
            $username =  $user->username;
            $query = "SELECT * FROM user WHERE username = '$username'";
            $dataUser = DB::select($query);
            return response()->json([
                "status" => 'Success',
                "token_type" => "Bearer",
                "expires_in" => 0,
                "access_token" => $token,
                "dataUser" => $dataUser,
            ]);
        } else {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }
    }


    protected function guard()
    {
        return Auth::guard();
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
