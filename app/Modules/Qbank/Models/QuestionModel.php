<?php

namespace App\Modules\Qbank\Models;

use CodeIgniter\Model;

class QuestionModel extends Model
{
    protected $table = 'qbank_questions';
    protected $returnType = 'object';
    protected $allowedFields = [
        'package_id',
        'question_type',
        'question_text',
        'question_img',
        'option_text_1',
        'option_text_2',
        'option_text_3',
        'option_text_4',
        'option_text_5',
        'correct_option_id',
        'created_by',
        'created_at',
        'updated_at',
    ];

    function findWithoutKey($id)
    {
        $fields = $this->allowedFields;
        unset($fields['correct_option_id']);
        return $this->builder->where('id', $id)->select($fields)->get()->getRow();
    }

    function findSimpleByPackageId($package_id)
    {
        return $this->builder()
            ->select([
                'id',
                'question_text',
            ])
            ->where('package_id', $package_id)
            ->get()
            ->getResultObject();
    }

    function findWithoutKeyByPackageId($package_id)
    {
        $fields = $this->allowedFields;
        unset($fields['correct_option_id']);
        return $this->builder()
            ->select([
                'id',
                'question_text',
            ])
            ->where('package_id', $package_id)
            ->get()
            ->getResultObject();
    }

    function findIdOnlyByPackageId($package_id)
    {
        return $this->builder()->select(['id'])->where('package_id', $package_id)->get()->getResultObject();
    }
}
