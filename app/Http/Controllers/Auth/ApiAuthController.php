<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ApiAuthController extends Controller
{

    private $res = [];
    private $request;

    function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function registrar () {
        $validator = Validator::make($this->request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'rol_id' => 'required|integer',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $this->request['password'] = Hash::make($this->request['password']);
        $this->request['remember_token'] = Str::random(10);
        $data = $this->request->toArray();
        $data['avatar'] = 'avatar.jpg';
        $user = User::create($data);
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
        return response($response, 200);
    }

    public function modificar () {
        $validator = Validator::make($this->request->all(), [
            'name' => 'required|string|max:255',
            'rol_id' => 'required|integer'
        ]);
        if ($validator->fails()){
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::updateOrCreate(['id' => $this->request->user()->id], $this->request->toArray());
        return response($user, 200);
    }

    public function verInformacion () {
        $response = User::find($this->request->user()->id);

        if (!$response) {
            $response = ["message" =>'El usuario no existe.'];   
        }

        return response($response, 200);
    }

    public function login () {
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $this->request->email)->first();
        if ($user) {
            if (Hash::check($this->request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response($response, 200);
            } else {
                $response = ["message" => "Contraseña incorrecta."];
                return response($response, 422);
            }
        } else {
            $response = ["message" =>'El usuario no existe.'];
            return response($response, 422);
        }
    }

    public function logout () {
        $token = $this->request->user()->token();
        $token->revoke();
        $response = ['message' => 'Has cerrado sesión correctamente.'];
        return response($response, 200);
    }
}
