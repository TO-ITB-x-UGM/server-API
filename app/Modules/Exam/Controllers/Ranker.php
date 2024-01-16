<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Modules\Exam\Models\AnswerModel;
use App\Modules\Exam\Models\AttemptModel;
use App\Modules\Exam\Models\EventModel;
use App\Modules\Exam\Models\MaterialModel;
use App\Modules\Exam\Models\SubattemptModel;
use App\Modules\Exam\Models\SubtestModel;
use Exception;

class Ranker extends BaseController
{
    protected AttemptModel $attempts;
    protected AnswerModel $answers;
    protected SubtestModel $subtests;

    function __construct()
    {
        $this->attempts = new AttemptModel();
        $this->answers = new AnswerModel();
        $this->subtests = new SubtestModel();
    }

    function markCorrect($subtest_id)
    {
        try {
            try {
                $result = $this->answers->markCorrect($subtest_id);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            return success([
                'status' => "Scoring subtest '$subtest_id' success",
                'result' => $result
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    /**
     * PHASE 1 OF IRT
     */
    function preparing($subtest_id)
    {
        try {
            try {
                $marking = $this->answers->markCorrect($subtest_id);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }
            if (!$marking) {
                throw new Exception("Internal error", 500);
            }
            $subtests = new SubtestModel();
            $subtest = $subtests->find($subtest_id);
            if (!$subtest) {
                throw new Exception("Subtest not found", 404);
            }

            $total_attempt = $this->attempts->totalAttempt($subtest->exam_id)->total_attempt;

            $corrects = $this->answers->sumTotalCorrect($subtest_id);
            $finalCorrects = [];
            foreach ($corrects as $correct) {
                $finalCorrects[$correct->question_id] = (int)$correct->total_correct;
            }

            $nulls = $this->answers->sumTotalNull($subtest_id);
            $finalNulls = [];
            foreach ($nulls as $null) {
                $finalNulls[$null->question_id] = $null->total_null;
            }

            $materials = new MaterialModel();
            $questions = $materials->findIdOnlyBySubtestId($subtest_id);
            $batch = [];
            foreach ($questions as $question) {
                $total_correct  = isset($finalCorrects[$question->question_id]) ? $finalCorrects[$question->question_id] : 0;
                $total_null     = isset($finalNulls[$question->question_id]) ? $finalNulls[$question->question_id] : 0;
                $batch[] = [
                    'id'                => $question->id,
                    'total_correct'     => $total_correct,
                    'total_incorrect'   => $total_attempt - ($total_correct + $total_null),
                    'total_null'        => $total_null,
                    'total_attempt'     => $total_attempt,
                ];
            }

            try {
                $result = $materials->updateBatch($batch, 'id');
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            return success([
                'result' => $result,
                'batch' => $batch
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function summaryIRT()
    {
        try {

            $subtest_id = $this->request->getGet('subtest');
            if (!$subtest_id) {
                throw new Exception("Parameter 'subtest' required", 400);
            }
            $materials = new MaterialModel();
            $questions = $materials->findIRTDataBySubtestId($subtest_id);
            $subtests = new SubtestModel();
            return success([
                'questions' => $questions,
                'subtest' => $subtests->find($subtest_id)
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function weighting($subtest_id)
    {
        try {

            $score_max = 1000;
            $score_min = 210;
            $score_min_each_question = 20;

            $materials = new MaterialModel();
            // $items = $materials->findIRTDataBySubtestId($subtest_id);
            $items = $materials->sortIrt($subtest_id);
            $batch = [];

            // // phase 2 of irt
            // foreach ($items as $item) {
            //     $weight = (string)round(($item->total_attempt - $item->total_correct) / $item->total_attempt, 4);
            //     $batch[$weight] = $item->id;
            // }

            // sort
            // ksort($batch);

            $denumerator = 0;
            for ($i = 0; $i < count($items); $i++) {
                $denumerator += ($i + 1);
            }

            $weightbase = ($score_max - $score_min) - $score_min_each_question * count($items);

            // weighting per question
            $final = [];
            foreach ($items as $i => $item) {
                $weight = round(($i + 1) / $denumerator * $weightbase + $score_min_each_question);
                $batch[] = [
                    'id' => $item->id,
                    'marks' => $weight
                ];
            }


            // $maxscorePerItem = ((1000 - 258) / count($items) - 17);
            // foreach ($items as $item) {
            //     $batch[] = [
            //         'id' => $item->id,
            //         // formula
            //         'marks' => round(($item->total_attempt - $item->total_correct) / $item->total_attempt * $maxscorePerItem) + 17,
            //     ];
            // }

            try {
                $res = $materials->updateBatch($batch, 'id');
                $res = 10;
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }
            if (!$res) {
                throw new Exception("Internal server error. Returned (" . gettype($res) . ") $res", 500);
            }

            return success([
                'status' => 'Weighting Success',
                'total_weighted' => $items
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function distributing($subtest_id)
    {
        try {
            $materials = new MaterialModel();
            $answers = new AnswerModel();
            $items = $materials->findIRTDataBySubtestId($subtest_id);
            $result = [];
            foreach ($items as $item) {
                $result[] = [
                    'question_id' => $item->question_id,
                    'status' => $answers->markScore($item->question_id, $subtest_id, $item->marks)
                ];
            }

            return success([
                'result' => $result
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function summarizing($subtest_id)
    {
        try {
            $answers = new AnswerModel();
            $participants = $answers->getSummarizedParticipantScore($subtest_id);
            $batch = [];
            foreach ($participants as $participant) {
                $batch[] = [
                    'id' => $participant->subattempt_id,
                    'score' => (210 + $participant->score)
                ];
            }

            $subattempts = new SubattemptModel();
            try {
                $result = $subattempts->updateBatch($batch, 'id');
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            if (!$result) {
                throw new Exception("Internal server error. Returned (" . gettype($result) . ") $result", 500);
            }

            return success([
                'result' => $result
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function agregating($exam_id)
    {
        try {
            $subattempts = new SubattemptModel();

            $scores = $subattempts->getScores($exam_id);
            $final = [];
            foreach ($scores as $score) {
                if ($score->type == 1) {
                    $final[$score->attempt_id][] = ['score' => $score->score, 'type' => $score->type];
                } else {
                    $final[$score->attempt_id][] = ['score' => $score->score, 'type' => $score->type];
                }
            }

            $attemptsModel = new AttemptModel();
            $attempts = $attemptsModel->findByExamId($exam_id);
            $final2 = [];
            foreach ($attempts as $attempt) {
                $scorefinal = 0;
                $scoretps = 0;
                $scoretka = 0;
                if (isset($final[$attempt->id])) {
                    foreach ($final[$attempt->id] as $subattempt) {
                        if ($subattempt['type'] == 1) {
                            $scoretps += $subattempt['score'];
                            $scorefinal += $subattempt['score'] * 0.175;
                        } else {
                            $scoretka += $subattempt['score'];
                            $scorefinal += $subattempt['score'] * 0.075;
                        }
                    }
                }
                $final2[] = [
                    'id' => $attempt->id,
                    'score_tps' => $scoretps / 4,
                    'score_tka' => $scoretka / 4,
                    'score_agregate' => $scorefinal
                ];
            }

            // $tps = [];
            // foreach ($subattempts->sumScoreByUserId($exam_id, 1) as $score) {
            //     $tps[$score->attempt_id] = [
            //         'id' => $score->attempt_id,
            //         'tps' => $score->total_score
            //     ];
            // }

            // $scoreTKA = $subattempts->sumScoreByUserId($exam_id, 2);
            // $batch2 = [];
            // foreach ($scoreTKA as $score) {
            //     if (isset($batch[$score->attempt_id])) {
            //         $batch2[] = [
            //             'id' => $score->attempt_id,
            //             'score_tps' => $batch[$score->attempt_id]['tps'],
            //             'score_tka' => $score->total_score,
            //             'score_agregate' => ($batch[$score->attempt_id]['tps']) * 0.075 + $score->total_score * 0.05
            //         ];
            //     } else {
            //         $batch2[] = [
            //             'id' => $score->attempt_id,
            //             'score_tps' => 0,
            //             'score_tka' => $score->total_score,
            //             'score_agregate' => $score->total_score * 0.05
            //         ];
            //     }
            // }


            $attempts = new AttemptModel();
            try {
                $result = $attempts->updateBatch($final2, 'id');
                // $result = 12;
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            return success([
                'test' => $final2,
                'result' => $result,
                // 'score_tka' => $batch2,
                // 'score_tps' => $batch,
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    // DISPLAY

    function subtest($subtest_id)
    {
        try {
            $subattempts = new SubattemptModel();
            try {
                $result = $subattempts->rankScoreBySubtestId($subtest_id);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            $subtests = new SubtestModel();
            $subtest = $subtests->find(($subtest_id));
            $exams = new EventModel();
            return success([
                'rank' => $result,
                'subtest' => $subtest,
                'exam' => $exams->find($subtest->exam_id)
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }


    function agregate($exam_id)
    {
        try {
            $attempts = new AttemptModel();
            $exams = new EventModel();
            try {
                $result = $attempts->rankScore($exam_id);
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            return success([
                'exam' => $exams->find($exam_id),
                'rank' => $result
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function getFullRankScore($exam_id)
    {
        try {
            $subattempts = new SubattemptModel();
            $all = $subattempts->getAllScore($exam_id);

            $subtestModel = new SubtestModel();
            $subtests = $subtestModel->findByExamId($exam_id);

            foreach ($all as $subattempt) {
                $final[$subattempt->attempt_id][$subattempt->subtest_id] = $subattempt;
            }

            $participantModel = new AttemptModel();
            $final2 = [];
            $participants = $participantModel->rankScore($exam_id);
            foreach ($participants as $participant) {
                $user = [
                    'name' => $participant->name,
                    'school' => $participant->school,
                    'score_agregate' => $participant->score_agregate,
                    // 'subtests' => ,
                    // 'subtests' => isset($final[$participant->id]) ? $final[$participant->id] : [],
                ];

                if (isset($final[$participant->id])) {
                    foreach ($subtests as $i => $subtest) {
                        if (isset($final[$participant->id][$subtest->id])) {
                            $user['subtests'][$i] = $final[$participant->id][$subtest->id]->score;
                        } else {
                            $user['subtests'][$i] = 0;
                        }
                    }
                    // $user['subtests']
                } else {
                    $user['subtests'] = [0, 0, 0, 0, 0, 0, 0, 0];
                }
                $final2[] = $user;
            }

            $exams = new EventModel();

            return success([
                'rank' => $final2,
                'subtests' => $subtests,
                'exam' => $exams->find($exam_id)
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }
}
