<?php

namespace App\Modules\Exam\Models;

use App\Modules\Qbank\Models\QuestionModel;
use CodeIgniter\Model;

class AnswerModel extends Model
{
    protected $table = "exam_answers";
    protected $returnType = 'object';
    protected $questions = "qbank_questions";

    protected $allowedFields = [
        'subtest_id',       // summarizing
        'subattempt_id',    // filling
        'user_id',
        'question_id',      // filling + scoring
        'key_id',           // scoring
        'selected_id',      // scoring
        'is_correct',       // scoring => default: 0
        'marks',            // scoring => default: 0
    ];

    function putAnswers(array $data)
    {
        return $this->builder()->updateBatch($data, 'id');
    }

    /**
     * Method 1: Use join queries
     */
    function findQuestionsBySubattemptId(int $subattempt_id)
    {
        return $this->builder()
            ->where('subattempt_id', $subattempt_id)
            ->join($this->questions, "$this->questions.id = question_id")
            ->select([
                "$this->table.id",
                // "$this->table.selected_id",
                "$this->questions.question_text",
                "$this->questions.option_text_1",
                "$this->questions.option_text_2",
                "$this->questions.option_text_3",
                "$this->questions.option_text_4",
                "$this->questions.option_text_5",
            ])
            ->get()
            ->getResultObject();
    }

    /**
     * Method 2: Query for id only
     */
    function findQuestionIdsBySubattemptId($subattempt_id)
    {
        return $this->builder()
            ->where('subattempt_id', $subattempt_id)
            ->select(["id", 'question_id'])
            ->get()
            ->getResultObject();
    }

    function markCorrect($subtest_id)
    {
        return $this->db->query("UPDATE `exam_answers` SET `is_correct` = 1 WHERE `key_id` = `selected_id` AND `subtest_id` = '$subtest_id';");
    }

    function sumTotalCorrect($subtest_id)
    {
        return $this->builder()
            ->select(['question_id', 'SUM(is_correct) AS total_correct'])
            ->where('subtest_id', $subtest_id)
            ->groupBy('question_id')
            ->get()->getResultObject();
    }

    function sumTotalNull($subtest_id)
    {
        return $this->builder()
            ->select(['question_id', 'COUNT(id) AS total_null'])
            ->where('subtest_id', $subtest_id)
            ->where('selected_id', 0)
            ->groupBy('question_id')
            ->get()->getResultObject();
    }

    // function sumTotalAttempt($subtest_id)
    // {
    //     return $this->builder()
    //         ->select(['COUNT(subattempt_id) AS total_attempt'])
    //         ->groupBy('subattempt_id')
    //         ->where('subtest_id', $subtest_id)
    //         // ->getCompiledSelect();
    //         ->get()
    //         ->getRowObject();
    // }

    function markScore(int $question_id, int $subtest_id, float $score)
    {
        return $this->builder()
            ->where('question_id', $question_id)
            ->where('subtest_id', $subtest_id)
            ->where('is_correct', true)
            ->set('marks', $score)
            ->update();
    }

    function getSummarizedParticipantScore(int $subtest_id)
    {
        return $this->builder()
            ->select(['subattempt_id', 'SUM(marks) AS score'])
            ->groupBy('user_id')
            ->where('subtest_id', $subtest_id)
            ->get()
            ->getResultObject();
    }

    function getAnswers($subattempt_id)
    {
        return $this->builder()
            ->where('subattempt_id', $subattempt_id)
            ->select([
                "id",
                "selected_id"
            ])
            ->get()
            ->getResultObject();
    }

    function review($subattempt_id)
    {
        return $this->builder()
            ->where('subattempt_id', $subattempt_id)
            ->get()
            ->getResultObject();
    }
}
