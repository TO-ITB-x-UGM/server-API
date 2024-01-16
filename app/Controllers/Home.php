<?php

namespace App\Controllers;

use Config\Passport;
use Config\Services;

class Home extends BaseController
{
    public function index()
    {
        return success([
	    'DateTime'      => date('d M Y H:i:s'),
            'name'          => 'UIxITB',
            'status'        => 'ready',
            'host'          => base_url(''),
            'request_time'  => $this->request->getServer('REQUEST_TIME_FLOAT'),
	    'time_zone'     => date_default_timezone_get() ? date_default_timezone_get()  : 'unknown',
        ], 200, "ready");
    }

    public function ping()
    {
        $this->response->setJSON([
            'time' => time()
        ]);
        $this->response->send();
        die;
    }

    public function error404()
    {
        return error(404, "Route not found");
    }
}
