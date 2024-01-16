<?php

namespace App\Modules\Qbank\Controllers;

use App\Controllers\BaseController;
use App\Modules\Qbank\Models\PackageModel;
use App\Modules\Qbank\Models\QuestionModel;
use Exception;

class Package extends BaseController
{
    protected PackageModel $packages;

    function __construct()
    {
        $this->packages = new PackageModel();
    }

    function index()
    {
        return success([
            'packages' => $this->packages->findAll()
        ]);
    }

    function show($id)
    {
        $package = $this->packages->find($id);
        if (!$package) {
            throw new Exception("Package not found", 404);
        }

        $questions = new QuestionModel;

        return success([
            'package' => $package,
            'questions' => $questions->findSimpleByPackageId($id)
        ]);
    }

    function edit($id)
    {
        $package = $this->packages->find($id);
        if (!$package) {
            throw new Exception("Package not found", 404);
        }

        $result = $this->packages->update($id, $this->reqdata->getAll());
        if (!$result) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'package_id' => $id,
            'request_data' => $this->reqdata->getAll()
        ]);
    }

    function create()
    {
        $id = $this->packages->insert($this->reqdata->getAll());
        if (!$id) {
            throw new Exception("Internal error", 500);
        }

        return success([
            'package_id' => $id,
            'package' => $this->packages->find($id)
        ]);
    }

    function delete($id)
    {
        try {
            $package = $this->packages->find($id);
            if (!$package) {
                throw new Exception("Package not found", 404);
            }

            $res = $this->packages->delete($id);
            if (!$res) {
                throw new Exception("Internal error", 500);
            }

            return success([
                'package_id' => $id,
                'status' => 'deleted'
            ]);
        } catch (\Throwable $th) {
            return error($th->getCode() ? $th->getCode() : 500, $th->getMessage());
        }
    }
}
