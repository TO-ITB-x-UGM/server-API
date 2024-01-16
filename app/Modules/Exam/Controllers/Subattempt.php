<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Libraries\AnswerToken;
use App\Modules\Account\Models\AccountModel;
use App\Modules\Exam\Models\AnswerModel;
use App\Modules\Exam\Models\AttemptModel;
use App\Modules\Exam\Models\EventModel;
use App\Modules\Exam\Models\MaterialModel;
use App\Modules\Exam\Models\SubattemptModel;
use App\Modules\Exam\Models\SubtestModel;
use Exception;

class Subattempt extends BaseController
{
    function index()
    {
        if ($this->request->getGet('attempt')) {
            $attempts = new AttemptModel();
            $attempt = $attempts->find($this->request->getGet('attempt'));
            if (!$attempt) {
                throw new Exception("Attempt not found", 404);
            }

            $accounts = new AccountModel();
            $exams = new EventModel();
            $subattempts = new SubattemptModel();

            return success([
                'attempt' => $attempt,
                'exam' => $exams->find($attempt->exam_id),
                'user' => $accounts->find($attempt->user_id),
                'subattempts' => $subattempts->findByAttemptId($this->request->getGet('attempt'))
            ]);
        }

        if ($this->request->getGet('subtest')) {
            $subattempts = new SubattemptModel();
            $subtests = new SubtestModel();
            return success([
                'subattempts' => $subattempts->findBySubtestId($this->request->getGet('subtest')),
                'subtest' => $subtests->find($this->request->getGet('subtest')),
            ]);
        }
    }

    /**
     * Required:
     * - subtest_id
     * - attempt_id
     */
    function start()
    {
        try {
            $subtest_id = $this->reqdata->subtest;
            if (!$subtest_id) {
                throw new Exception("Parameter 'subtest' required", 400);
            }

            $attempt_id = $this->reqdata->attempt;
            if (!$attempt_id) {
                throw new Exception("Parameter 'attempt' required", 400);
            }

            $subtests = new SubtestModel();
            $subtest = $subtests->find($subtest_id);
            if (!$subtest) {
                throw new Exception("Subtest not found", 404);
            }

            $attempts = new AttemptModel();
            $attempt = $attempts->find($attempt_id);
            if (!$attempt) {
                throw new Exception("Attempt not found", 404);
            }

            if ($attempt->ended_at < time()) {
                throw new Exception("Time expired", 403);
            }

            // Cek apakah uda ada atau belum
            $subattempts = new SubattemptModel();
            $subattempt = $subattempts->findSpecific($subtest_id, $attempt_id);
            if ($subattempt) {
                // Sedang dikerjakan sehingga hanya perlu get data dan answer token

                if ($subattempt->ended_at < time()) {
                    throw new Exception("Time expired", 403);
                }

                return success([
                    'subattempt_id'     => $subattempt->id,
                    'answer_token'      => AnswerToken::generate($subattempt, 10),
                    'ended_at'          => $subattempt->ended_at,
                    'time_remaining'    => ($subattempt->ended_at - time())
                ]);
            } else {
                // Register subattempt baru untuk pengerjaan subtest
                $subattempt = [
                    'exam_id'       => $attempt->exam_id,
                    'subtest_id'    => $subtest_id,
                    'user_id'       => $attempt->user_id,
                    'attempt_id'    => $attempt_id,
                    'started_at'    => time(),
                    'ended_at'      => (($subtest->attempt_duration) + time()) > $attempt->ended_at ? $attempt->ended_at : (($subtest->attempt_duration) + time()),
                    'score'         => 0
                ];
                $subattempt_id = $subattempts->insert($subattempt);

                if (!$subattempt_id) {
                    throw new Exception("Internal error: failed when create new subattempt", 500);
                }

                $subattempt['id'] = $subattempt_id;

                // Generate and shuffle soal yang akan dikerjakan oleh peserta
                $materialModel = new MaterialModel();
                $questions = $materialModel->findQuestionIdAndKeyBySubtestId($subtest_id);

                if (!shuffle($questions)) {
                    throw new Exception("Internal error: shuffling failed", 500);
                }

                $batch = [];
                foreach ($questions as $question) {
                    $batch[] = [
                        'subtest_id'    => $subtest_id,
                        'subattempt_id' => $subattempt_id,
                        'user_id'       => $attempt->user_id,
                        'question_id'   => $question->question_id,
                        'key_id'        => $question->key_id,
                        'selected_id'   => 0,
                        'is_correct'    => 0,
                        'marks'         => 0,
                    ];
                }

                $answers = new AnswerModel();
                try {
                    $result = $answers->insertBatch($batch);
                } catch (\Throwable $th) {
                    throw new Exception($th->getMessage(), 500);
                }

                if (!$result) {
                    throw new Exception("Internal error", 500);
                }


                return success([
                    'subattempt_id'     => $subattempt_id,
                    'answer_token'      => AnswerToken::generate((object)$subattempt, 10),
                    'ended_at'          => $subattempt['ended_at'],
                    'time_remaining'    => ($subattempt['ended_at'] - time())
                ]);
            }
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function show($subattempt_id)
    {
        try {
            $subattempts = new SubattemptModel();
            $subattempt = $subattempts->find($subattempt_id);
            if (!$subattempt) {
                throw new Exception("Subattempt not found", 404);
            }

            $answers = new AnswerModel();
            return success([
                'questions'         => $answers->findQuestionsBySubattemptId($subattempt_id),
                'answers'           => $answers->getAnswers($subattempt_id),
                'answer_token'      => AnswerToken::generate((object)$subattempt, 10),
                'ended_at'          => $subattempt->ended_at,
                'time_remaining'    => ($subattempt->ended_at - time())
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function delete($id)
    {
        try {
            $subattempts = new SubattemptModel();
            if (!$subattempts->delete($id)) {
                throw new Exception("Internal error", 500);
            }

            return success([
                'status' => 'Subattempt Deleted',
                'subattempt_id' =>  $id
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function finish($subattempt_id)
    {
        try {
            $subattempts = new SubattemptModel();
            $subattempt = $subattempts->find($subattempt_id);
            if (!$subattempt) {
                throw new Exception("Subattempt not found", 404);
            }

            $user_id = $this->passport->getUserId();
            if ($user_id != $subattempt->user_id) {
                throw new Exception("Forbidden", 400);
            }

            try {
                $res = $subattempts->update($subattempt_id, ['ended_at' => time()]);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            return success([
                'status' => 'success',
                'subattempt_id' => $subattempt_id,
                'exam_id' => $subattempt->exam_id
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function review($subattempt_id)
    {
        try {
            $subattempts = new SubattemptModel();
            $exams = new EventModel();
            $subattempt = $subattempts->find($subattempt_id);
            $subtests = new SubtestModel();
            $accounts = new AccountModel();
            $answers = new AnswerModel();

            return success([
                'exam' => $exams->find($subattempt->exam_id),
                'subtest' => $subtests->find($subattempt->subtest_id),
                'subattempt' => $subattempt,
                'account' => $accounts->find($subattempt->user_id),
                'answers' => $answers->review($subattempt_id)
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }
}
