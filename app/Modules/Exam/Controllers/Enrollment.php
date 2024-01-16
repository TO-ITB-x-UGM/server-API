<?php

namespace App\Modules\v1\Exam\Controllers;

use App\Controllers\BaseController;
use App\Modules\v1\Account\Models\AccountModel;
use Config\Services;
use Exception;

class Enrollment extends BaseController
{

    /**
     * POST /v1/exam/:id/enroll
     */
    function enroll($exam_id)
    {
        $exams = model('\App\Modules\v1\Exam\EventModel');
        $exam = $exams->find($exam_id);
        if (!$exam) {
            throw new Exception("Exam not found", 404);
        }

        if ($exam->price) {
            return success([
                'status' => 'failed',
                'reason' => 'Exam is not free',
                'price' => $exam->price,
                'buy_link' => [
                    'with_wallet' => base_url("v1/exam/purchase/$exam_id/wallet"),
                    'with_cash' => base_url("v1/exam/purchase/$exam_id/cash"),
                ]
            ]);
        }

        $registered = $this->participants->insert([
            'exam_id'       => $exam_id,
            'user_id'          => $this->passport->getUser()->id,
            'enrolled_at'   => time()
        ]);

        if (!$registered) {
            throw new Exception("Enrolling failed. Internal error.", 500);
        }

        return success([
            'status'      => 'enrolled',
            'enrollment'  => [
                'reg_id'    => $registered,
                'exam_id'   => $exam_id
            ]
        ]);
    }

    /**
     * GET /v1/exam/:id/enrolled
     */
    function list($exam_id)
    {
        $query = $this->participants->builder()->where('exam_id', $exam_id);

        $total = $query->countAllResults();
        $limit = $this->request->getGet('limit') ? $this->request->getGet('limit') : 50;
        $page = $this->request->getGet('page') ? $this->request->getGet('page') : 1;

        $pager = Services::pager();
        $pager->makeLinks($page, 10, $total, 'json_pagination');

        return success([
            'exam_id' => $exam_id,
            'current_page' => $page,
            'pagination' => $pager->links('default', 'json_pagination'),
            'participants' => $query->get($limit, ($page - 1) * 10)->getResult()
        ]);
    }

    /**
     * DELETE /v1/exam/:id/enrolled/:id
     */
    function delete($id)
    {
        $this->participants->delete($id);
    }

    /**
     * POST /v1/exam/:id/buy/wallet
     */
    function buyWithWallet($exam_id)
    {
        $exam = model('\App\Modules\v1\Exam\EventModel')->find($exam_id);
        if (!$exam) {
            throw new Exception("Exam not found", 404);
        }

        $user_id = $this->passport->getUser()->id;
        $balance = \App\Modules\v1\Finance\Controllers\Wallet::balance($user_id);

        if ($balance < $exam->price) {
            throw new Exception("Insufficient balance", 400);
        }

        if (!\App\Modules\v1\Finance\Controllers\Wallet::charge($user_id, $exam->price)) {
            throw new Exception("Internal error", 500);
        }

        return $this->enroll($exam_id);
    }

    /**
     * POST /v1/exam/:id/buy/cash
     */
    function buyWithCash($exam_id)
    {
        $exam = model('\App\Modules\v1\Exam\EventModel')->find($exam_id);
        if (!$exam) {
            throw new Exception("Exam not found", 404);
        }

        $user = AccountModel::find($this->passport->getUserId());

        \App\Modules\v1\Finance\Controllers\Invoice::create([
            'product_id'    => '#E' . $exam_id,
            'nominal'       => $exam->price,
            'name'          => $user->firstname . ' ' . $user->lastname,
            'email'         => $user->email
        ]);
    }
}
