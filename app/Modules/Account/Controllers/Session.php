<?php

namespace App\Modules\Account\Controllers;

use App\Controllers\BaseController;
use App\Libraries\GoogleClient;
use App\Modules\Account\Models\AccountModel;
use Config\Services;
use Exception;;

class Session extends BaseController
{
    protected AccountModel $accounts;

    function __construct()
    {
        $this->accounts = new AccountModel();
    }

    /**
     * Login with Email and Password
     */
    function loginOrdinary()
    {
        try{ 
            $email = $this->reqdata->email;
            $password = $this->reqdata->password;
            if (!$email) {
                throw new Exception("Email required", 400);
            }
            if (!$password) {
                throw new Exception("Password required", 400);
            }

            $account = $this->accounts->findByEmail($email);
            if (!$account) {
                throw new Exception("Account not found", 404);
            }

            if (!password_verify($password, $account->password)) {
                throw new Exception("Password wrong", 400);
            }

            // $this->passport->setUser($account);
            helper("text");
            $session_id = random_string();
            $this->passport->setUser([
                'id'            => $account->id,
                'role_id'       => $account->role_id,
                'session_id'    => $session_id,
            ]);
            return success([
                'account'       => [
                    'name'      => $account->name,
                    'email'     => $account->email,
                    'picture'   => $account->picture,
                    'school'    => $account->school
                ],
                'access_token'  => $this->passport->issueToken()
            ]);
        }catch(\Throwable $th) {
            return error($th->getCode(), $th->getMessage());
        }
    }

    /**
     * Google Sign In
     */
    function loginGoogle()
    {
        try {
            $credential = $this->reqdata->credential;
            if (!$credential) {
                throw new Exception("Credential not provided", 400);
            }
            try {
                $gsi = GoogleClient::verifyIdToken(env('Google.ClientId'), $credential);
                $accounts = new AccountModel();
                $account = $accounts->findByEmail($gsi['email']);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            if (!$account) {
                throw new Exception("User not found", 404);
            }

            $accounts->update($account->id, [
                'picture' => $gsi['picture']
            ]);
            $account = $accounts->findByEmail($gsi['email']);

            helper("text");
            $session_id = random_string();
            $this->passport->setUser([
                'id'            => $account->id,
                'role_id'       => $account->role_id,
                'session_id'    => $session_id,
            ]);

            return success([
                'account'           => [
                    'name'          => $account->name,
                    'email'         => $account->email,
                    'picture'       => $account->picture,
                    'school'        => $account->school,
                    'role_id'       => $account->role_id,
                    'phone_number'  => $account->phone_number
                ],
                'access_token'  => $this->passport->issueToken(),
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode(), $th->getMessage());
        }
    }

    function getLoginData()
    {
        return success([
            'google_client_id' => env('Google.ClientId')
        ]);
    }

    function checkAccessToken()
    {
        try {
            $access_token  = $this->reqdata->access_token;
            if (!$access_token) {
                throw new Exception("Access token required", 400);
            }

            /** Check */

            $passport = Services::passport();
            if (!$passport->verifyToken($access_token)) {
                throw new Exception($passport->getErrorMessage(), $passport->getErrorNo());
            }

            return success([
                'status' => 'token_valid',
                'token' => $access_token
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode(), $th->getMessage());
        }
    }
}
