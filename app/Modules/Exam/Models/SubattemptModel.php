<?php

namespace App\Modules\Exam\Models;

use CodeIgniter\Model;

class SubattemptModel extends Model
{
    protected $table = 'exam_subattempts';
    protected $subtests = "exam_subtests";
    protected $accounts = 'accounts';
    protected $returnType = 'object';
    protected $allowedFields = [
        'exam_id',
        'subtest_id',
        'user_id',
        'attempt_id',
        'started_at',
        'ended_at',
        'score'
    ];

    function findSpecific($subtest_id, $attempt_id)
    {
        return $this->builder()
            ->where('attempt_id', $attempt_id)
            ->where('subtest_id', $subtest_id)
            ->get(1)
            ->getRowObject();
    }

    function totalSubattempt($subtest_id)
    {
        return $this->builder()
            ->select(['COUNT(id)'])
            ->where('subtest_id', $subtest_id)
            ->get()
            ->getRowObject();
    }

    function findByAttemptId($attempt_id)
    {
        return $this->builder()
            ->select([
                "$this->table.*",
                "$this->subtests.title"
            ])
            ->join($this->subtests, "$this->subtests.id = subtest_id")
            ->where('attempt_id', $attempt_id)
            ->get()
            ->getResultObject();
    }

    function findBySubtestId($subtest_id)
    {
        return $this->builder()
            ->select([
                "$this->table.*",
                "$this->accounts.name",
                "$this->accounts.email"
            ])
            ->join($this->accounts, "$this->accounts.id = user_id")
            ->where('subtest_id', $subtest_id)
            ->get()
            ->getResultObject();
    }

    function findScore($exam_id)
    {
        return $this->builder()
            ->select([
                "score",
                "attempt_id"
            ])
            ->where('exam_id', $exam_id)
            ->get()
            ->getResultObject();
    }

    function findScoreByAttemptId($attempt_id)
    {
        return $this->builder()
            ->select("score")
            ->where('attempt_id', $attempt_id)
            ->join($this->subtests, "$this->subtests.id = subtest_id")
            ->get()
            ->getResultObject();
    }

    function rankScoreBySubtestId($subtest_id)
    {
        return $this->builder()
            ->where('subtest_id', $subtest_id)
            ->join($this->accounts, "$this->accounts.id = user_id")
            ->select([
                "$this->table.id",
                "$this->table.score",
                "$this->table.attempt_id",
                "$this->table.user_id",
                "$this->accounts.name",
                "$this->accounts.school"
            ])
            ->orderBy('score', 'DESC')
            ->get()
            ->getResultObject();
    }

    function sumScoreByUserId($exam_id, $type)
    {
        return $this->builder()
            ->where("$this->table.exam_id", $exam_id)
            ->where('type', $type)
            ->join($this->subtests, "$this->subtests.id = subtest_id")
            ->select([
                "attempt_id",
                "SUM(score) AS total_score"
            ])
            ->groupBy('attempt_id')
            ->get()
            ->getResultObject();
    }

    function getScores($exam_id)
    {
        return $this->builder()
            ->where("$this->table.exam_id", $exam_id)
            ->join($this->subtests, "$this->subtests.id = subtest_id")
            ->select([
                'attempt_id',
                'type',
                'score',
            ])
            ->get()
            ->getResultObject();
    }

    function getAllScore($exam_id)
    {
        return $this->builder()
            ->where("$this->table.exam_id", $exam_id)
            ->join($this->subtests, "$this->subtests.id = subtest_id")
            ->select([
                "$this->table.attempt_id",
                "$this->table.score",
                "$this->table.subtest_id",
                // "$this->subtests.type",
                // "$this->subtests.acr",
            ])
            ->get()
            ->getResultObject();
    }
}
