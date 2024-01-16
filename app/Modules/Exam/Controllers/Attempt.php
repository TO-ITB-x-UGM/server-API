<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Modules\Account\Models\AccountModel;
use App\Modules\Exam\Models\AttemptModel;
use App\Modules\Exam\Models\EventModel;
use App\Modules\Exam\Models\SubattemptModel;
use App\Modules\Exam\Models\SubtestModel;
use Exception;

class Attempt extends BaseController
{
    protected EventModel $exams;
    protected AttemptModel $attempts;
    function __construct()
    {
        $this->exams = new EventModel();
        $this->attempts = new AttemptModel();
    }


    function index()
    {
        try {
            $exam_id = $this->request->getGet('exam');
            $exam = $this->exams->find($exam_id);
            if (!$exam) {
                throw new Exception("Exam event not found", 404);
            }

            return success([
                'exam' => $exam,
                'attempts' => $this->attempts->findByExamId($exam_id)
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    /** Custom for UIxITB */
    function available()
    {
        try {
            $registered = $this->attempts->findUser($this->passport->getUserId());
            if (!$registered) {
                $exs = $this->exams->findAll();
                $exams = [];
                foreach ($exs as $ex) {
                    if ($ex->attempt_closed_at > time()) {
                        $exams[] = $ex;
                    }
                }
            } else {
                $exam = $this->exams->find($registered->exam_id);
                $exams[] = $exam;
            }
            return success($exams);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function start()
    {
        try {
            $exam_id = $this->reqdata->exam;
            if (!$exam_id) {
                throw new Exception("Parameter 'exam' required", 400);
            }

            $exam = $this->exams->find($exam_id);
            if (!$exam) {
                throw new Exception("Exam event not found", 404);
            }

            if ($exam->attempt_closed_at < time()) {
                throw new Exception("Time expired", 403);
            }

            $user_id = $this->passport->getUserId();
            $registered = $this->attempts->checkUser($exam_id, $user_id);

            $subtests = new SubtestModel();
            if (!$registered) {
                $attempt = [
                    'exam_id' => $exam_id,
                    'user_id' => $user_id,
                    'started_at' => time(),
                    'ended_at' => ((time() + ($exam->attempt_duration * 60)) > $exam->attempt_closed_at) ? $exam->attempt_closed_at : (time() + ($exam->attempt_duration * 60)),
                    'score_agregate' => 0
                ];
                $attempt_id = $this->attempts->insert($attempt);

                if (!$attempt_id) {
                    throw new Exception("Internal error", 500);
                }

                return success([
                    'attempt_id'        => $attempt_id,
                    'attempt'           => $this->attempts->find($attempt_id),
                    'time_remaining'    => ($attempt['ended_at'] - time()),
                    'subtests'          => $subtests->findByExamId($exam_id)
                ]);
            } else {
                if ($registered->ended_at < time()) {
                    throw new Exception("Time expired", 403);
                }

                return success([
                    'attempt_id'        => $registered->id,
                    'attempt'           => $registered,
                    'time_remaining'    => ($registered->ended_at - time()),
                    'subtests'          => $subtests->findByExamId($exam_id)
                ]);
            }
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function show($id)
    {
        try {
            $attempt = $this->attempts->find($id);
            if (!$attempt) {
                throw new Exception("Attempt not found");
            }

            if ($attempt->user_id != $this->passport->getUserId()) {
                if ($this->passport->getRoleId() != 9) {
                    throw new Exception("Prohibited", 403);
                }
            }

            $subattemptModel = new SubattemptModel();
            $subattemptBySubtest = [];
            foreach ($subattemptModel->findByAttemptId($id) as $subattempt) {
                $subattemptBySubtest[$subattempt->subtest_id] = $subattempt;
            }

            $final = [];
            $subtestModel = new SubtestModel();
            foreach ($subtestModel->findByExamId($attempt->exam_id) as $subtest) {
                $type = ($subtest->type == 1) ? "tps" : "tka";
                $obj = [
                    'subtest_id'        => $subtest->id,
                    'subtest_title'     => $subtest->title,
                    'subtest_duration'  => $subtest->attempt_duration,
                ];
                if (isset($subattemptBySubtest[$subtest->id])) {
                    $subattempt = $subattemptBySubtest[$subtest->id];
                    $obj['subattempt_id'] = $subattempt->id;
                    $obj['status'] = ($subattempt->ended_at < time()) ? "finish" : "ongoing";
                    $obj['started_at'] = $subattempt->started_at;
                    $obj['ended_at'] = $subattempt->ended_at;
                    $obj['score'] = $subattempt->score;
                } else {
                    $obj['subattempt_id'] = 0;
                    $obj['status'] = "null";
                    $obj['started_at'] = 0;
                    $obj['ended_at'] = 0;
                    $obj['score'] = 0;
                }
                $final[$type][] = $obj;
            }


            $users = new AccountModel();

            return success([
                'attempt'           => $attempt,
                'subattempts'       => $final,
                'user'              => $users->find($attempt->user_id),
                'exam'              => $this->exams->find(($attempt->exam_id)),
                'time_remaining'    => ($attempt->ended_at - time())
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function create()
    {
        $id = $this->attempts->insert($this->reqdata->getAll());

        if (!$id) {
            throw new Exception("Internal server error", 500);
        }

        return success([
            'attempt_id' => $id,
            'attempt' => $this->attempts->find($id)
        ]);
    }

    function edit($id)
    {
        $res = $this->attempts->update($id, $this->reqdata->getAll());
        if (!$res) {
            throw new Exception("Internal server error", 500);
        }

        return success([
            'status' => 'updated',
            'attempt_id' => $id,
            'attempt' => $this->attempts->find($id)
        ]);
    }

    function delete($id)
    {
        $res = $this->attempts->delete($id);
        if (!$res) {
            throw new Exception("Internal server error", 500);
        }

        return success([
            'status' => 'deleted',
            'attempt_id' => $id,
        ]);
    }
}
