<?php

namespace App\Http\Controllers;

use Auth;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class JWTController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','loginAdmin','loginEcommerce', 'register']]);
    }

    /**
     * Register user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'lastname' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }


        //TODO:Este es mucho mas rapido para aceptar todos los campos
        // $user = User::create($request->all());

        $user = User::create([
                'name' => $request->name,
                'lastname' => $request->lastname,
                'type_user' => $request->type_user,
                'state' => $request->state,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            //El hash significa que tiene que ser encriptado

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    
    /**
     * login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function loginAdmin(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //         'password' => 'required|string|min:6',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json($validator->errors(), 422);
    //     }

    ////validator te da los valores de aca arriba pero abajo los personaliza
    //     if (!$token = auth()->attempt($validator->validated())) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     return $this->respondWithToken($token);
    // }
    
    public function loginAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt(["email" => $request->email, "password" => $request->password, "state"=>1, "type_user"=>2])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function loginEcommerce(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //"type_user"=>1
        if (!$token = auth()->attempt(["email" => $request->email, "password"=> $request->password, "state"=>1])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth()->user());
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
        $user = auth()->user();
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * (60*24*365),
            'user' => [
                "name" => $user->name,
                "lastname" => $user->lastname,
                "email"=> $user->email,
                "role" => $user->role,
            ]
            //otra manera de encontrar el role
            // 'user' => auth() -> user()->role,
        ]);
    }
}