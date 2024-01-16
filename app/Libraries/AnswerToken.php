<?php

namespace App\Libraries;

use Exception;

class AnswerToken
{
    static function generate(object $data, $additional_time = 5)
    {
        $tokendata = [
            'id'            => $data->id,
            'user_id'       => $data->user_id,
            'iat'           => time(),
            'nbf'           => $data->started_at,
            'exp'           => ($data->ended_at + $additional_time),
        ];
        return \Firebase\JWT\JWT::encode($data, env("Answer.TokenKey"), 'HS256');
    }

    static function verify($token)
    {
        try {
            return \Firebase\JWT\JWT::decode($token, env("Answer.TokenKey"), ['HS256']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 400);
        }
    }
}
