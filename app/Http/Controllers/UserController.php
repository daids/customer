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

    public function active(Request $request)
    {
        $data = $request->only(['code', 'email']);
        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return view('user.active', ['result' => false]);
        }
        if ($user->token != $data['code']) {
            return view('user.active', ['result' => false, 'message' => '激活码不正确']);
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
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        info($data[0].'#'.$data[1]);

        $user = User::where('email', $data[0])->first();
        if ($user) {
            return ['result' => false, 'code' => '50001',  'message' => 'user exists'];
        }

        $user['email'] = $data[0];
        $user['password'] = Hash::make($data[1]);
        $token = str_random(5);
        User::create([
            'email' => $data[0],
            'password' => Hash::make($data[1]),
            'token' => $token
        ]);

        $transport = (new \Swift_SmtpTransport(env('MAIL_SMTP'), 465, 'ssl'))
          ->setUsername(env('MAIL_USERNAME'))
          ->setPassword(env('MAIL_PASSWORD'));

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('Wellcome Register!'))
          ->setFrom(env('MAIL_USERNAME'))
          ->setTo($user['email'])
          ->setBody('Here is the message itself, you active code is '.$token);

        $result = $mailer->send($message);

        return ['result' => true];
    }

    public function login(Request $request)
    {
        $data = $request->getContent();
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        $user = User::where('email', $data[0])->first();
        if (Hash::check($data[1], $user['password'])) {
            return ['result' => true, 'data' => ['']];
        }
        return ['result' => false];
    }
}
