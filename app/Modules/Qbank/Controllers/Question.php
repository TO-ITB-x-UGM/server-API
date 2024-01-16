<?php

namespace App\Modules\Qbank\Controllers;

use App\Controllers\BaseController;
use App\Modules\Qbank\Models\PackageModel;
use App\Modules\Qbank\Models\QuestionModel;
use Exception;

class Question extends BaseController
{
    protected QuestionModel $questions;
    protected PackageModel $packages;

    function __construct()
    {
        $this->questions = new QuestionModel;
        $this->packages = new PackageModel();
    }

    function index()
    {
        if ($this->request->getGet('package')) {
            $package = $this->packages->find($this->request->getGet('package'));
            if (!$package) {
                throw new Exception("Package not found", 404);
            }

            return success([
                'package' => $package,
                'questions' => $this->questions->findSimpleByPackageId($package->id),
            ]);
        } else {
            throw new Exception("Parameter 'package' required", 400);
        }
    }

    function create()
    {
        $id = $this->questions->insert($this->reqdata->getAll());
        if (!$id) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'status' => 'question_created',
            'question_id' => $id,
            'question' => $this->questions->find($id)
        ]);
    }

    function edit($id)
    {
        $result = $this->questions->update($id, $this->reqdata->getAll());
        if (!$result) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'status' => 'question_updated',
            'question_id' => $id,
            'updated_data' => $this->reqdata->getAll()
        ]);
    }

    function delete($id)
    {
        try {
            $result = $this->questions->delete($id);
            if (!$result) {
                throw new Exception("Internal error", 500);
            }

            return success([
                'question_id' => $id
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function show($id)
    {
        $question = $this->questions->find($id);
        if (!$question) {
            throw new Exception("Question not found", 404);
        }

        return success([
            'question' => $question
        ]);
    }
}
