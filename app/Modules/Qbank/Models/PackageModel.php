<?php

namespace App\Modules\Qbank\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;
use Config\Database;

class PackageModel extends Model
{
    protected $table = "qbank_packages";
    protected $allowedFields = [
        'title'
    ];
    protected $returnType = 'object';
}
