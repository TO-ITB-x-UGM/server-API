<?php

namespace App\Modules\Exam\Models;

use CodeIgniter\Model;

class AttemptModel extends Model
{
    protected $table = "exam_attempts";
    protected $users = 'accounts';
    protected $returnType = 'object';
    protected $allowedFields = [
        'id',
        'exam_id',
        'user_id',
        'started_at',
        'ended_at',
        'score_agregate',
        'score_tps',
        'score_tka'
    ];

    function findByUser(string $exam_id, string $user_id)
    {
        return $this->builder()
            ->where('exam_id', $exam_id)
            ->where('user_id', $user_id)
            ->get()
            ->getResult();
    }

    function hasAttempt($exam_id, $user_id): int|false
    {
        return $this->builder()
            ->where('exam_id', $exam_id)
            ->where('user_id', $user_id)
            ->countAllResults();
    }

    function create(string $exam_id, string $user_id, int $duration)
    {
        $this->builder()->insert([
            'exam_id' => $exam_id,
            'user_id' => $user_id,
            'started_at' => time(),
            'ended_at'  => time() + $duration
        ]);
    }

    function totalAttempt($exam_id)
    {
        return $this->builder()
            ->select('COUNT(id) AS total_attempt')
            ->where('exam_id', $exam_id)
            ->get()
            ->getRowObject();
    }

    function findByExamId($exam_id)
    {
        return $this->builder()
            ->join($this->users, "$this->users.id = $this->table.user_id")
            ->select([
                "$this->table.*",
                "$this->users.name",
                "$this->users.school",
                "$this->users.email"
            ])
            ->where('exam_id', $exam_id)
            ->get()
            ->getResult();
    }

    function findUser($user_id)
    {
        return $this->builder()
            ->where('user_id', $user_id)
            ->get(1)
            ->getRowObject();
    }

    function checkUser($exam_id, $user_id)
    {
        return $this->builder()
            ->where('user_id', $user_id)
            ->where('exam_id', $exam_id)
            ->get(1)
            ->getRowObject();
    }

    function rankScore($exam_id)
    {
        return $this->builder()
            ->join($this->users, "$this->users.id = user_id", 'LEFT')
            ->where('exam_id', $exam_id)
            ->select([
                "$this->table.id",
                "$this->users.name",
                "$this->users.school",
                "score_tps",
                "score_tka",
                "score_agregate"
            ])
            ->orderBy('score_agregate', 'DESC')
            ->get()
            ->getResultObject();
    }
}
