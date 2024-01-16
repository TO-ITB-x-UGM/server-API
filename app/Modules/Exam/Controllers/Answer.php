<?php

namespace App\Modules\Exam\Controllers;

use App\Controllers\BaseController;
use App\Libraries\AnswerToken;
use App\Modules\Exam\Models\AnswerModel;
use Exception;

class Answer extends BaseController
{
    function put()
    {
        try {
            if (!$this->reqdata->answer_token) {
                throw new Exception("answer_token required", 400);
            }
            if (!$this->reqdata->answers) {
                throw new Exception("No answer(s) provided", 400);
            }

            $token = AnswerToken::verify($this->reqdata->answer_token);
            if ($token->user_id != $this->passport->getUserId()) {
                throw new Exception("Action Prohibited", 403);
            }

            $batch = [];
            /**
             * answers format array
             * {[qindex_id,selected_id]}
             */
            foreach ($this->reqdata->answers as $answer) {
                $batch[] = [
                    'id'            => $answer['id'],
                    'selected_id'   => $answer['selected_id']
                ];
            }
            $answers = new AnswerModel();
            try {
                $result = $answers->updateBatch($batch, 'id');
            } catch (\Throwable $th) {
                throw new Exception($th->getMessage(), 500);
            }

            return success([
                'result' => $result,
                'status' => 'Answer saved',
                'raw' => $answer
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    function get()
    {
        try {
            $answer_token = $this->request->getGet('answer_token');
            if (!$answer_token) {
                throw new Exception("Parameter 'answer_token' required", 400);
            }

            $answer_token = AnswerToken::verify($answer_token);

            $answers = new AnswerModel();
            return success([
                'answers' => $answers->getAnswers($answer_token->id),
                'questions' => $answers->findQuestionsBySubattemptId($answer_token->id)
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }

    // function questions()
    // {
    //     try {
    //         $answer_token = $this->request->getGet('answer_token');
    //         if (!$answer_token) {
    //             throw new Exception("Parameter 'answer_token' required", 400);
    //         }
    //         $answer_token = AnswerToken::verify($answer_token);

    //         $answers = new AnswerModel();
    //         $questions = $answers->findQuestionIdsBySubattemptId($answer_token->id);
    //         $questionIds = [];
    //         // foreach($questions as $question)
    //         // {
    //         // $qu
    //         // }

    //         $questionsModel = new QuestionModel();

    //         // $questions = $questionsModel->find($)



    //         return success([
    //             'questions' => $answers->getQuestions($answer_token->id)
    //         ]);
    //     } catch (\Throwable $th) {
    //         return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
    //     }
    // }
}
