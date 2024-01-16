<?php

namespace App\Libraries;

class Signature
{
    /**
     * Sign a data
     */
    static function hash(string $uuid, array $data, string $key, int $time)
    {
        $code = $uuid . '/w//' . json_encode($data) . '/at//' . $time;
        $code = base64_encode($code);

        return hash_hmac('sha256', $code, $key);
    }
}
