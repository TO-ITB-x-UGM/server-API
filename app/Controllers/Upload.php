<?php

namespace App\Controllers;

use Exception;

class Upload extends BaseController
{
    function apiUpload()
    {
        $file = $this->request->getFile('image');
        if ($file) {
            if (!$file->isValid()) {
                throw new Exception("File not valid", 400);
            }
            $newname = $file->getRandomName();

            $file->move(ROOTPATH . 'public/assets/image', $newname);
            if (!$file->hasMoved()) {
                throw new Exception("Internal error: cannot move file to directory", 500);
            }

            return success([
                'url' => "https://tryout.techdev.my.id/upload/assets/image/$newname"
            ]);
        }
    }

    function apiDelete()
    {
        $src = $this->request->getPost('src');
        $file_name = str_replace(base_url(), ROOTPATH . 'public', $src);
        if (!unlink($file_name)) {
            throw new Exception("Error delete image", 500);
        }

        return success([
            'status' => 'deleted',
            'url' => $src
        ]);
    }

    function apiManage()
    {
    }
}
