<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Modules\Exam\Models\SubtestModel;
use Exception;

class Subtest extends BaseController
{
    protected SubtestModel $subtests;

    function __construct()
    {
        $this->subtests = new SubtestModel();
    }

    function index()
    {
        $exam_id = $this->request->getGet('exam');
        if (!$exam_id) {
            throw new Exception("Parameter exam required", 400);
        }

        $subtests = $this->subtests->findByExamId($exam_id);
        $categorized = ['tps', 'tka'];
        if (count($subtests) == 0) {
            $categorized['tps'] = [];
            $categorized['tka'] = [];
        } else {
            foreach ($subtests as $subtest) {
                if ($subtest->type == 1) {
                    $categorized['tps'][] = $subtest;
                } else {
                    $categorized['tka'][] = $subtest;
                }
            }
        }

        return success([
            'subtests'  => $this->subtests->findByExamId($exam_id),
            'tps'       => isset($categorized['tps']) ? $categorized['tps'] : [],
            'tka'       => isset($categorized['tka']) ? $categorized['tka'] : [],
        ]);
    }

    function create()
    {
        $id = $this->subtests->insert([
            'exam_id' => $this->reqdata->exam_id,
            'title' => $this->reqdata->title,
            'type' => $this->reqdata->type,
            'attempt_duration' => $this->reqdata->attempt_duration
        ]);

        if (!$id) {
            throw new Exception("Internal Error", 500);
        }

        return success([
            'subtest_id' => $id
        ]);
    }

    function show($id)
    {
        $subtest = $this->subtests->find($id);
        if (!$subtest) {
            throw new Exception("Subtest not found", 404);
        }

        return success([
            'subtest' => $subtest
        ]);
    }

    function edit($id)
    {
        $res = $this->subtests->update($id, [
            'title' => $this->reqdata->title,
            'type' => $this->reqdata->type,
            'attempt_duration' => $this->reqdata->attempt_duration
        ]);

        if (!$res) {
            throw new Exception("Internal Error", 500);
        }

        return success([
            'status' => 'subtest data updated',
            'subtest_id' => $id
        ]);
    }

    function delete($id)
    {
        try {
            if (!$this->subtests->delete($id)) {
                throw new Exception("Internal error", 500);
            }

            return success([
                'subtest_id' => $id
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }
}
