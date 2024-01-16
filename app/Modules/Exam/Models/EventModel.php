<?php

namespace App\Modules\Exam\Models;

use CodeIgniter\Model;

class EventModel extends Model
{
    protected $table = 'exams';
    protected $allowedFields = [
        'id',
        'title',
        'description',
        'attempt_open_at',
        'attempt_closed_at',
        'attempt_duration',
        'published_at',
        'created_at',
    ];
    protected $returnType = 'object';
}
