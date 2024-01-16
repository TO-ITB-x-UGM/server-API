<?php

namespace App\Libraries;

use Exception;
use Google_Client;
use LogicException;
use UnexpectedValueException;

class GoogleClient
{
    /**
     * Verify Id Token
     * @param string $client_id
     * @param string $id_token
     * @throws Exception
     * @return array|false
     */
    public static function verifyIdToken($client_id, $id_token)
    {
        try {
            $client = new Google_Client(['client_id' => $client_id]);
            $payload = $client->verifyIdToken($id_token);
            if ($payload) {
                return $payload;
            } else {
                throw new Exception("Verification failed");
                // echo $payload;
            }
        } catch (LogicException $th) {
            throw new Exception("No credential provided", 400);
        } catch (UnexpectedValueException $th) {
            throw new Exception("JWT Invalid: " . $th->getMessage(), 400);
        } catch (Exception $th) {
            throw new Exception($th->getMessage(), 500);
        }
    }
}
