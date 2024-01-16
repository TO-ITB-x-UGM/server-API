<?php

namespace App\Modules\Account\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;
use Exception;

/**
 * Account Model
 * 
 */
class AccountModel extends Model
{
    protected $table = 'accounts';
    protected $allowedFields = [
        'name',
        'email',
	'password',
        'phone_number',
        'picture',
        'school',
        'role_id',
        'status',
        'created_at'
    ];
    protected $returnType = 'object';


    function findByGoogleId($gid)
    {
        return $this->builder()->where('google_user_id', $gid)->get(1)->getRowObject();
    }

    function findByEmail(string $email)
    {
        try {
            return $this->builder()->getWhere(['email' => $email])->getRow();
        } catch (DatabaseException $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    function findByRoleId(int $role_id, int $limit = null, int $offset = 0)
    {
        return $this->builder()
            ->select([
                'id',
                'name',
                'email',
                'school',
                'role_id',
                'status'
            ])
            ->where('role_id', $role_id)
            ->get($limit, $offset)
            ->getResultObject();
    }

    function countByRoleId(int $role_id)
    {
        return $this->builder()
            ->where('role_id', $role_id)
            ->countAllResults();
    }
}
