<?php

function error(int $statusCode, string $message, array $data = [])
{
    $response = \Config\Services::response();
    $response->setStatusCode($statusCode);
    $response->setJSON(json_encode([
        'ok' => false,
        'status_code' => $statusCode,
        'message' => $message,
        'timestamp' => time(),
        'data' => $data,
        'execution_time' => timer("execution")->getElapsedTime('execution')
    ]));
    $response->send();
    die;
}

function success(array $data, int $statusCode = 200, $message = "")
{
    $response = \Config\Services::response();
    $response->setStatusCode($statusCode);
    $response->setJSON(json_encode([
        'ok' => true,
        'status_code' => $statusCode,
        'message' => $message,
        'timestamp' => time(),
        'data' => $data,
        'execution_time' => timer("execution")->getElapsedTime('execution')
    ]));
    $response->send();
    die;
}

function generateUniqueId(int $len = 13)
{
    if ($len < 13) {
        throw new LengthException("Length too short", 400);
    }

    $id = uniqid();
    if ($len > 13) {

        $max = (pow(10, ($len - 13)) - 1);
        if (is_float($max)) {
            throw new LengthException("Length too long0", 400);
        }
        $id .= random_int((pow(10, ($len - 13) - 1)), (pow(10, ($len - 13)) - 1));
    }
    return $id;
}
