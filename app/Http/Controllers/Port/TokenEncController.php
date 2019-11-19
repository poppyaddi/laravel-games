<?php

namespace App\Http\Controllers\Port;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TokenEncController extends Controller
{
    //
    public function token_private_encrypt($data)
    {

        $privateKey = $this->get__key('private_key');

        $crypto = '';

        foreach (str_split($data, 117) as $chunk)
        {
            openssl_private_encrypt($chunk, $encrypted, $privateKey); // 私钥加密

            $crypto .= $encrypted;
        }

        return base64_encode($crypto);
    }

    public function token_public_decrypt($data)
    {

        $publicKey = $this->get__key('public_key');

        $data = base64_decode($data);
        $crypto = '';

        foreach (str_split($data, 256) as $chunk)
        {
            openssl_public_decrypt($chunk, $decryptData, $publicKey);
            $crypto .= $decryptData;
        }

        return $crypto;
    }

    protected function get__key($key){
        return Cache::remember($key, 1800, function () use ($key) {
            $key =  DB::connection("encrypt")
                ->table("ciphertext")
                ->where('key_name', $key)
                ->first();
            return $key->value;
        });
    }
}
