<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Config\Database;
use Exception;

class AdminFilter implements FilterInterface
{
    public function before($request, $arguments = null)
    {
        try {

            $passport = \Config\Services::passport();
            if ($passport->getRoleId() != 9) {
                throw new Exception($passport->getRoleId(), 403);
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
 
