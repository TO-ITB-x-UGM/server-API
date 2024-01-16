<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Modules\Exam\Models\EventModel;
use Exception;

class Event extends BaseController
{
    protected EventModel $exams;

    function __construct()
    {
        $this->exams = new EventModel();
    }

    function index()
    {
        return success([
            'exams' => $this->exams->findAll()
        ]);
    }

    function show($exam_id)
    {
        try {
            $exam = $this->exams->find($exam_id);
            if (!$exam) {
                throw new Exception("Exam not found", 404);
            }

            return success([
                'exam' => $exam
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function create()
    {
        $id = $this->exams->insert($this->reqdata->getAll());
        if (!$id) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'status' => 'exam_created',
            'exam_id' => $id,
            'exam' => $this->exams->find($id)
        ]);
    }

    function edit($exam_id)
    {
        $exam = $this->exams->find($exam_id);
        if (!$exam) {
            throw new Exception("Exam not found", 404);
        }

        $data = $this->reqdata->getAll();
        if (!$data) {
            throw new Exception("No data provided", 400);
        }

        if (!$this->exams->update($exam_id, $data)) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'status' => 'exam_updated',
            'exam_id' => $exam_id,
            'data_updated' => $data
        ]);
    }

    function delete($exam_id)
    {
        $exam = $this->exams->find($exam_id);
        if (!$exam) {
            throw new Exception("Exam not found", 404);
        }

        if (!$this->exams->delete($exam_id)) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'status' => 'deleted',
            'exam_id' => $exam_id,
        ]);
    }
}
