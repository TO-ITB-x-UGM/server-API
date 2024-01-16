<?php

namespace App\Modules\Exam\Models;

use CodeIgniter\Model;

class SubtestModel extends Model
{
    protected $table = 'exam_subtests';
    protected $allowedFields = [
        'exam_id',
        'type',
        'title',
        'acr',
        'attempt_duration',
    ];
    protected $returnType = 'object';

    function hasAttempt($subtest_id, $user_id): int
    {
        return 0;
    }


    function findByExamId($exam_id)
    {
        return $this->builder()->where('exam_id', $exam_id)->get()->getResult();
    }
}
