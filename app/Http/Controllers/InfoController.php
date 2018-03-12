<?php

namespace App\Http\Controllers;

use App\Visit;
use App\Customer;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function add(Request $request)
    {
        $data = $request->getContent();
        $key = 'bdf6508c2c95df15';
        $data = \openssl_decrypt(base64_decode(trim($data)), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = explode('#', $data);
        if (isset($data[0])) {
            if (! Customer::where('mac_address', $data[0])->first()) {
                Customer::create([
                    'mac_address' => $data[0],
                    'pc_config' => json_encode($data)
                ]);
            }
            Visit::create([
                'mac_address' => $data[0],
                'ip' => $request->ip()
            ]);
            return 'ok';
        }
        return abort(500);
    }
    //
}
