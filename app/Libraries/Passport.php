<?php

namespace App\Libraries;

use Exception;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Passport Library
 * 
 * Session Manager Library that manage user session
 * 
 * Require Firebase/JWT
 */
class Passport
{
    protected array $user = [];
    protected string $secret_key;
    protected string $error_message = "";
    protected int $error_no = 0;
    protected int $expires;
    protected array $data_interface;

    function __construct(\Config\Passport $config)
    {
        $this->secret_key = $config->secretkey;
        $this->expires = $config->expires;
        $this->data_interface = $config->data_interface;
    }

    /**
     * Set secret key on Passport
     * @param string $secret_key
     * @return void;
     */
    function setSecretKey($secret_key)
    {
        $this->secret_key = $secret_key;
    }

    /**
     * Check whether user is authenticated uet
     * @return bool
     */
    function isLoggedIn()
    {
        if ($this->user == []) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get authenticated user data
     * @param bool $asObject
     * @return object|array|null
     */
    function getUser($asObject = true)
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        if ($asObject) {
            return (object)$this->user;
        } else {
            return $this->user;
        }
    }

    /**
     * Verify access token
     * @param string $access_token
     * @return bool
     */
    function verifyToken($access_token)
    {
        try {
            $payload = (array) JWT::decode($access_token, $this->secret_key, ['HS256']);
            $this->user = $this->decode($payload);
        } catch (Exception $e) {
            $this->error_message = $e->getMessage();
            $this->error_no = 400;
        } finally {
            return ($this->error_no === 0) ? true : false;
        }
    }

    /**
     * Set user
     * @param object|array $user
     * @return bool
     */
    function setUser($user)
    {
        if (is_object($user)) {
            $this->user = (array)$user;
        } else {
            $this->user = $user;
        }
    }

    /**
     * Issue access token
     * @param int|null $expires (in secs) If null, system will use default value (30 mins)
     * @return string|false
     */
    function issueToken($expires = null)
    {
        if (!$this->secret_key) {
            $this->error_message = "Secret key was empty";
            $this->error_no = 500;
            return false;
        }

        if (!$this->user) {
            $this->error_message = "User was empty";
            $this->error_no = 500;
            return false;
        }

        if ($expires == null) {
            $expires = $this->expires;
        }

        $payload = $this->encode($this->user);
        $payload['iss'] = base_url();
        $payload['iat'] = time();
        $payload['nbf'] = time();
        $payload['exp'] = time() + $expires;
        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    /**
     * Get error message
     * @return string
     */
    function getErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * Get error no
     */
    function getErrorNo()
    {
        return $this->error_no;
    }

    /**
     * Get user id
     */
    function getUserId()
    {
        return $this->user['id'];
    }

    /**
     * Get role id
     */
    function getRoleId()
    {
        return $this->user['role_id'];
    }

    private function encode($normalData)
    {
        $final = [];
        foreach ($this->data_interface as $key => $item) {
            if (isset($this->user[$key])) {
                $final[$item] = $this->user[$key];
            }
        }
        return $final;
    }

    private function decode($encodedData)
    {
        $final = [];
        foreach ($this->data_interface as $key => $item) {
            if (isset($encodedData[$item])) {
                $final[$key] = $encodedData[$item];
            }
        }
        return $final;
    }

    function checkPermission($gate_id)
    {
        if ($gate_id) {
            if ($this->user['role_id'] == 9) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
