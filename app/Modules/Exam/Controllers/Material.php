<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Modules\Exam\Models\EventModel;
use App\Modules\Exam\Models\MaterialModel;
use App\Modules\Exam\Models\SubtestModel;
use App\Modules\Qbank\Models\PackageModel;
use App\Modules\Qbank\Models\QuestionModel;
use Firebase\JWT\JWT;
use Exception;


class Material extends BaseController
{
    protected MaterialModel $materials;

    function __construct()
    {
        $this->materials = new MaterialModel();
    }

    function index()
    {
        try {
            if ($this->request->getGet('subtest')) {
                $packages = new PackageModel();
                $subtest = new SubtestModel();
                return success([
                    'subtest' => $subtest->find($this->request->getGet('subtest')),
                    'questions' => $this->materials->findSummaryBySubtestId($this->request->getGet('subtest')),
                    'available_packages' => $packages->findAll()
                ]);
            } else {
                throw new Exception("Parameter 'subtest' required", 400);
            }
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function getQuestionOnly()
    {
        $answer_token = $this->request->getPost('token');
        if ($answer_token) {
            throw new Exception("parameter 'token' required");
        }
        $answer_token = JWT::decode($answer_token, 'HS256');
        if ($this->passport->getUserId() !== $answer_token->user_id) {
            throw new Exception("Account not authorized to access these attempt");
        }

        return success([
            'questions' => $this->materials->find($answer_token->subtest_id)
        ]);
    }

    function add($exam_id, $subtest_id)
    {
        $question_id = $this->request->getPost('question_id');
        if (!$question_id) {
            throw new Exception("Parameter question_id required");
        }

        $exams = new EventModel;
        $exam = $exams->builder()
            ->join('exam_subtests', 'exam_subtests.exam_id = exams.id')
            ->where('exams.id', $exam_id)
            ->where('exam_subtests.id', $subtest_id)
            ->get(1)->getRow();
        if (!$exam) {
            throw new Exception("Exam or Subtest not found");
        }

        $this->materials->insert([
            'subtest_id' => $subtest_id,
            'question_id' => $question_id,
        ]);

        return success([
            'status' => 'question added',
            'exam_id' => $exam_id,
            'subtest_id' => $subtest_id,
            'question_id' => $question_id,
        ]);
    }

    function import()
    {
        try {
            $subtest_id = $this->reqdata->subtest_id;
            $package_id = $this->reqdata->package_id;

            if (!$package_id) {
                throw new Exception("Parameter package_id required", 400);
            }

            if (!$subtest_id) {
                throw new Exception("Parameter subtest_id required", 400);
            }

            $questionModel = new QuestionModel();
            $already = $this->materials->findIdOnlyBySubtestId($subtest_id);

            $alreadyFormatted = [];
            foreach ($already as $a) {
                $alreadyFormatted[] = $a->question_id;
            }


            $questions = $questionModel->findIdOnlyByPackageId($package_id);

            $batch = [];
            foreach ($questions as $question) {
                if (!in_array($question->id, $alreadyFormatted)) {
                    $batch[] = [
                        'subtest_id' => $subtest_id,
                        'question_id' => $question->id,
                    ];
                }
            }

            if ($batch) {
                try {
                    $inserted = $this->materials->insertBatch($batch);
                } catch (\Throwable $th) {
                    throw new Exception($th->getMessage(), 500);
                }
            } else {
                $inserted = 0;
            }


            return success([
                'total_inserted' => $inserted,
                'question_ids' => $batch
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function remove($id)
    {
        try {
            try {
                $res = $this->materials->delete($id);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }
            if (!$res) {
                throw new Exception("Internal error", 500);
            }

            return success([
                'deleted_id' => $id
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }
}
