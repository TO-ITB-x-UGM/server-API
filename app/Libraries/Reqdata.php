<?php

namespace App\Libraries;

use UnexpectedValueException;

class Reqdata
{
    protected array $reqdata = [];
    protected bool $useException = false;

    function __construct()
    {
        $request = \Config\Services::request();
        if ($request->getHeaderLine('Content-Type') == "application/json") {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data) {
                $this->reqdata = $data;
            } else {
                $this->reqdata = $request->getRawInput();
            }
        } else {
            $this->reqdata = $request->getPostGet();
        }
    }

    function __get($key)
    {

        if (isset($this->reqdata[$key])) {
            return $this->reqdata[$key];
        } else {
            if ($this->useException) {
                throw new UnexpectedValueException("Parameter '$key' was not found in request data", 404);
            } else {
                return null;
            }
        }
    }

    function enableException($allowed)
    {
        $this->useException = true;
    }

    function getAll()
    {
        return $this->reqdata;
    }
}
