<?php

namespace App\Modules\Account\Controllers;

use App\Controllers\BaseController;
use App\Modules\Account\Models\AccountModel;
use Exception;

class User extends BaseController
{
    protected AccountModel $accounts;

    function __construct()
    {
        $this->accounts = new AccountModel;
    }

    function index()
    {
        if ($this->request->getGet('email')) {
            $user = $this->accounts->findByEmail($this->request->getGet('email'));
            if (!$user) {
                return error(404, "user_not_found");
            }
            return success([
                'email' => $user->email,
                'name' => $user->name,
                'id' => $user->id,
            ]);
        }

        $limit = $this->request->getGet('limit') ? $this->request->getGet('limit') : 0;
        $offset = $this->request->getGet('offset') ? $this->request->getGet('offset') : 0;
        return success([
            'users' => $this->accounts->findAll($limit, $offset),
            'total' => $this->accounts->countAllResults()
        ]);
    }

    function participant()
    {
        $limit = $this->reqdata->limit ? $this->reqdata->limit : 0;
        $offset = $this->reqdata->offset ? $this->reqdata->offset : 0;
        return success([
            'users' => $this->accounts->findByRoleId(1, $limit, $offset),
            'total' => $this->accounts->countByRoleId(1)
        ]);
    }

    function committe()
    {
        $limit = $this->reqdata->limit ? $this->reqdata->limit : null;
        $offset = $this->reqdata->offset ? $this->reqdata->offset : 0;
        return success([
            'users' => $this->accounts->findByRoleId(9, $limit, $offset),
            'total' => $this->accounts->countByRoleId(9)

        ]);
    }

    /**
     * GET /v1/profile
     * GET /v1/user/:id
     */
    function show($user_id = null)
    {
        if ($user_id == null) {
            $user_id = $this->passport->getUserId();
        }

        $account = $this->accounts->find($user_id);

        if (!$account) {
            throw new Exception("Account not found", 404);
        }

        return success([
            'account' => [
                'id'                => $account->id,
                'name'              => $account->name,
                'email'             => $account->email,
		'password'          => $account->password,
                'picture'           => $account->picture,
                'status'            => $account->status,
                'school'            => $account->school,
                'phone_number'      => $account->phone_number,
                'role_id'           => $account->role_id
            ]
        ]);
    }

    /**
     * PATCH /v1/profile
     * PATCH /v1/user/:id
     */
    function edit($user_id = null)
    {
        if ($user_id == null) {
            $user_id = $this->passport->getUserId();
        }

        if ($user_id !== $this->passport->getUserId()) {
            if ($this->passport->getRoleId() != 9) {
                throw new Exception("Forbidden", 403);
            }
        }

        $data = $this->reqdata->getAll();

        if (isset($data['role_id'])) {
            if ($this->passport->getRoleId() != 9) {
                throw new Exception("Forbidden", 403);
            }
        }

        if (!$this->accounts->update($user_id, $data)) {
            throw new Exception("Interal error", 500);
        }

        return success([
            'status' => 'updated',
            'user_id' => $user_id,
            'data_updated' => $data,
        ]);
    }

    function create()
    {
        $result = $this->accounts->insert([
            'id'                => generateUniqueId(15),
            'name'              => $this->reqdata->name,
            'picture'           => $this->reqdata->picture,
            'email'             => $this->reqdata->email,
            'password'          => password_hash($this->reqdata->password, PASSWORD_DEFAULT),
            'is_active'         => $this->reqdata->is_active,
            'role_id'           => $this->reqdata->role_id,
        ]);

        if (!$result) {
            throw new Exception("Error", 500);
        }

        return success([
            'status' => 'user_created',
            'user_id' => $result,
            'account' => $this->accounts->find($result)
        ]);
    }

    function createBatch()
    {
        $result = $this->accounts->insertBatch($this->reqdata->users);
        if (!$result) {
            throw new Exception("Internal error", 500);
        } else {
            return success([
                'status'                    => 'Users created',
                'total_users_requested'     => count($this->reqdata->users),
                'total_users_inserted'      => $result,
                'users'                     => $this->reqdata->users
            ]);
        }
    }

    function delete($id)
    {
        $result = $this->accounts->delete($id);
        if (!$result) {
            throw new Exception("Internal error", 500);
        } else {
            return success([
                'status'                    => 'user_deleted',
                'user_id'                   => $id
            ]);
        }
    }
}
