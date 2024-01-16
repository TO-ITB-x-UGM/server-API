<?php


namespace Config;

use CodeIgniter\Config\BaseConfig;

class Passport extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Passport Secret Key
     * --------------------------------------------------------------------------
     *
     * Secret key for Access Token.
     *
     * @var string
     */
    public $secretkey;

    /**
     * --------------------------------------------------------------------------
     * Token Expires
     * --------------------------------------------------------------------------
     * 
     * How long token expires in secs
     */
    public $expires;

    /**
     * --------------------------------------------------------------------------
     * Data in JWT
     * --------------------------------------------------------------------------
     * 
     * Data in JWT
     */
    public array $data_interface = [
        'id'            => 'sub',
        'role_id'       => 'rol',
        'session_id'    => 'ses',
    ];
}
