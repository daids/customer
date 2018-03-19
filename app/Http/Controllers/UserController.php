<?php

namespace App\Http\Controllers;

use Cache;
use Hash;
use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function active($code)
    {
        $userId = Cache::get($code);
        $user = User::find($userId);
        if (! $user) {
            return view('user.active', ['result' => false]);
        }

        if ($user->isActive()) {
            return view('user.active', ['result' => false, 'message' => '账户已被激活']);
        }
        $user->active();
        return view('user.active', ['result' => true]);

    }

    public function create(Request $request)
    {
        $data = $request->getContent();
        $key = env('SOFTWARE_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        $user = User::where('email', $data[0])->first();
        if ($user) {
            return ['result' => false, 'message' => 'user exists'];
        }

        $user['email'] = $data[0];
        $user['password'] = Hash::make($data[1]);
        User::create([
            'email' => $data[0],
            'password' => Hash::make($data[1])
        ]);
        //send email
        return ['result' => true];
    }

    public function login(Request $request)
    {
        $data = $request->getContent();
        $key = env('SOFTWARE_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        $user = User::where('email', $data[0])->first();
        if (Hash::check($data[1], $user['password'])) {
            return ['result' => true, 'data' => ['']];
        }
        return ['result' => false];
    }
}
