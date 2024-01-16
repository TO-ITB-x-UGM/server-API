<?php

namespace App\Modules\Exam\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = "exam_questions";
    protected $questions = 'qbank_questions';
    protected $allowedFields = [
        'id',
        'subtest_id',
        'question_id',
        'total_correct',
        'total_attempt',
        'total_null',
        'total_incorrect',
        'marks'
    ];

    function findSummaryBySubtestId($subtest_id)
    {
        return $this->builder()
            ->where('subtest_id', $subtest_id)
            ->join($this->questions, "$this->questions.id = question_id")
            ->select([
                "$this->table.*",
                "$this->questions.question_text",
                "$this->questions.package_id"
            ])
            ->get()
            ->getResultObject();
    }

    function findIRTDataBySubtestId($subtest_id)
    {
        return $this->builder()
            ->where('subtest_id', $subtest_id)
            ->get()
            ->getResultObject();
    }

    function sortIrt($subtest_id)
    {
        return $this->builder()
            ->where('subtest_id', $subtest_id)
            ->orderBy('total_correct', 'DESC')
            ->get()
            ->getResultObject();
    }


    function findQuestionIdAndKeyBySubtestId($subtest_id)
    {
        return $this->builder()
            ->join($this->questions, "$this->questions.id = question_id")
            ->select(['question_id', 'correct_option_id	AS key_id'])
            ->where('subtest_id', $subtest_id)
            ->get()
            ->getResultObject();
    }

    /**
     * Find with return question_id only filter by subtest_id
     */
    function findIdOnlyBySubtestId($subtest_id)
    {
        return $this->builder()
            ->select(['id', 'question_id'])
            ->where('subtest_id', $subtest_id)
            ->get()
            ->getResultObject();
    }

    function findPreparedQuestion($subtest_id)
    {
        return $this->builder()
            ->join($this->questions, "$this->questions.id = $this->table.question_id")
            ->where('subtest_id', $subtest_id)
            ->select([
                "$this->table.id",
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
}
