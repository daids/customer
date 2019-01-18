<?php

namespace App\Http\Controllers;

use Cache;
use Hash;
use App\User;
use App\Feedback;
use App\UserSoftwareInfo;
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
        $token = str_random(32);
        if (! $user) {
            $user['email'] = $data[0];
            $user['password'] = Hash::make($data[1]);
            $user = User::create([
                'email' => $data[0],
                'password' => Hash::make($data[1]),
                'active_token' => $token
            ]);
        }

        $token = $user->active_token;

        if ($user->status == 'active') {
            return ['result' => false, 'code' => '50001',  'message' => 'user exists'];
        }

        $transport = (new \Swift_SmtpTransport(env('MAIL_SMTP'), 465, 'ssl'))
         ->setUsername(env('MAIL_USERNAME'))
         ->setPassword(env('MAIL_PASSWORD'));

        $mailer = new \Swift_Mailer($transport);

        $template = file_get_contents(storage_path('app').'/confirm.html');
        $template = str_replace('{#activelink}', $token, $template);

        $message = (new \Swift_Message('Welcome Register!'))
         ->setFrom(env('MAIL_USERNAME'))
         ->setTo($user['email'])
         ->setBody($template, 'text/html');
        $result = $mailer->send($message);
        info('email:'.$user['email'].'result:'.$result);

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

    public function changePassword(Request $request)
    {
        $data = $request->getContent();
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);
        $user = User::where('email', $data[0])->first();
        if (! $user) {
            return ['result' => false, 'message' => 'not found user'];
        }
        if (! Hash::check($data[1], $user['password'])) {
            return ['result' => false, 'message' => 'old password error'];
        }
        $user->update(['password' => Hash::make($data[2])]);
        return ['result' => true];
    }

    public function uploadFile(Request $request)
    {
        $token = $request->header('token');
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($token)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        $user = User::where('email', $data[0])->first();
        if (! $user) {
            return ['result' => false];
        }
        if (! Hash::check($data[1], $user['password'])) {
            return ['result' => false];
        }

        $filename = $request->header('filename');
        $data = $request->getContent();
        file_put_contents(storage_path('userfiles').'/'.$filename, $data);
        return ['result' => true];
    }

    public function file(Request $request, $filename)
    {
        $token = $request->header('token');
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($token)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);

        $user = User::where('email', $data[0])->first();
        if (! $user) {
            return ['result' => false];
        }
        if (! Hash::check($data[1], $user['password'])) {
            return ['result' => false];
        }
        $userfile = storage_path('userfiles').'/'.$filename;
        if (! file_exists($userfile)) {
            return ['result' => false];
        }
        return response()->download($userfile);
    }

    public function feedback(Request $request)
    {
        $token = $request->header('token');
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($token)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

        Feedback::create([
            'email' => $data,
            'content' => $request->getContent()
        ]);

        $transport = (new \Swift_SmtpTransport(env('MAIL_SMTP'), 465, 'ssl'))
          ->setUsername(env('MAIL_USERNAME'))
          ->setPassword(env('MAIL_PASSWORD'));

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('User Feedback!'))
          ->setFrom(env('MAIL_USERNAME'))
          ->setSubject($data)
          ->setTo(['feedback@drevo.net', 'support@drevo.net'])
          ->setBody('feedback content: '.$request->getContent());

        $mailer->send($message);

        return ['result' => true];
    }

    public function softwareInfo(Request $request)
    {
        $token = $request->header('token');
        $key = env('DREVO_KEY');
        $data = \openssl_decrypt(base64_decode(trim($token)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

        $data = explode('#', $data);

        UserSoftwareInfo::create([
            'email' => $data[0],
            'software_version' => $data[1],
            'firmware_version' => $data[2],
            'system_version' => $data[3],
            'ip' => $request->ip()
        ]);
        return ['result' => true];
    }

    public function test()
    {
        return '1';
    }
}
