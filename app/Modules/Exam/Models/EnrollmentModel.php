<?php

namespace App\Modules\Exam\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'exam_enrolled';
    protected $allowedFields = [
        'exam_id',
        'user_id',
        'enrolled_at',
        'rank',
        'score'
    ];

    function enroll(string $exam_id, string $user_id)
    {
        $result = $this->builder()->insert([
            'exam_id' => $exam_id,
            'user_id' => $user_id,
            'enrolled_at' => time(),
            'rank' => 0,
            'score' => 0
        ]);
    }

    function countParticipant(string $exam_id)
    {
        return $this->builder()->where('exam_id', $exam_id)->countAllResults();
    }

    function findByExamId(string $exam_id)
    {
    }

    function isExists($exam_id, $user_id)
    {
        return $this->builder()
            ->where('exam_id', $exam_id)
            ->where('user_id', $user_id)
            ->get(1)
            ->getRow();
    }
}
