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
        $user = User::where('active_token', $code)->first();
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
        $token = str_random(32);
        User::create([
            'email' => $data[0],
            'password' => Hash::make($data[1]),
            'active_token' => $token
        ]);

        $transport = (new \Swift_SmtpTransport(env('MAIL_SMTP'), 465, 'ssl'))
          ->setUsername(env('MAIL_USERNAME'))
          ->setPassword(env('MAIL_PASSWORD'));

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('Wellcome Register!'))
          ->setFrom(env('MAIL_USERNAME'))
          ->setTo($user['email'])
          ->setBody('Here is the message itself, please click active <a href="http://customer.drevo.net/active/'.$token.'">http://customer.drevo.net/active/'.$token.'</a>');

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
        if (! $user) {
            return ['result' => false];
        }
        if (Hash::check($data[1], $user['password'])) {
            return ['result' => true, 'data' => ['']];
        }
        return ['result' => false];
    }

    public function getResetPasswordToken(Request $request)
    {
        $data = $request->getContent();
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

        $user = User::where('email', $data)->first();
        if (! $user) {
            return ['result' => false, 'message' => 'not found user'];
        }
        $token = strtolower(str_random(5));
        $user->fill([
            'reset_token' => $token
        ])->save();

        $transport = (new \Swift_SmtpTransport(env('MAIL_SMTP'), 465, 'ssl'))
          ->setUsername(env('MAIL_USERNAME'))
          ->setPassword(env('MAIL_PASSWORD'));

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('Reset Password Code!'))
          ->setFrom(env('MAIL_USERNAME'))
          ->setTo($user['email'])
          ->setBody('the code is '.$token);

        $result = $mailer->send($message);

        return ['result' => true];
    }

    public function resetPassword(Request $request)
    {
        $data = $request->getContent();
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        $user = User::where('email', $data[0])->first();
        if (! $user) {
            return ['result' => false, 'message' => 'not found user'];
        }
        if ($user->reset_token != $data[2]) {
            return ['result' => false, 'message' => 'error token'];
        }
        $user->fill(['password' => Hash::make($data[1])])->save();
        return ['result' => true];
    }

    public function uploadFile(Request $request)
    {
        $filename = $request->header('filename');
        $data = $request->getContent();
        file_put_contents(storage_path('userfiles').'/'.$filename, $data);
        return ['result' => true];
    }

    public function file($filename)
    {
        return response()->file(storage_path('userfiles').'/'.$filename);
    }
}
