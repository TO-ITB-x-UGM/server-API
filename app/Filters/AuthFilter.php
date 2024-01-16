<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Database;
use Exception;

class AuthFilter implements FilterInterface
{
    public function before($request, $arguments = null)
    {
        try {
            header('Access-Control-Allow-Origin: *');
            $request = \Config\Services::request();

            $header = $request->getHeaderLine("Authorization");
            if ($header !== "") {
                $access_token = explode(" ", $header)[1];
            } else {
                $access_token = $request->getHeaderLine("X-SKY-ACT");
            }

            if ($access_token == "") {
                $auth_header = getallheaders()["authorization"];
                $access_token = explode(" ", $auth_header)[1];
            }

            if ($access_token == "") {
                throw new Exception("authorization_required", 401);
            }

            $passport = \Config\Services::passport();
            // $passport->setSecretKey(env('passport.secretkey'));
            $passport->verifyToken($access_token);
            if (!$passport->isLoggedIn()) {
                throw new Exception($passport->getErrorMessage(), $passport->getErrorNo());
            }

            /** check permission */
            if (isset($arguments[0])) {
                // var_dump($passport->getUser());
                if (!$passport->checkPermission($arguments[0])) {
                    throw new Exception("permission_denied", 403);
                }
            }
        } catch (\Throwable $th) {
            helper("api");
            return error($th->getCode() ? $th->getCode() : 400, $th->getMessage());
        }
    }
    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}
