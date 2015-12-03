<?php

namespace App\Model;

use Nette;

class Files extends Nette\Object {

    public $dirPath;

    public function __construct() {
        $this->dirPath = Nette\Environment::expand("%appDir%") . "/../www/storage/";
    }

    public function prepareFiles($pubId) {
        $files = $this->getFilesFromDirectory($this->dirPath . $pubId);

        $fileArray = array();

        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            } else {
                $fileArray[] = array('name' => $file, 'path' => $this->dirPath . $pubId . "/" . $file);
            }
        }

        return $fileArray;
    }

    public function deleteFiles($pubId) {

        $files = $this->prepareFiles($pubId);

        if (count($files)) {
            foreach ($files as $file) {
                if (is_file($file['path'])) {
                    unlink($file['path']);
                }
            }
        }
    }

    public function getFileExtension($filename) {
        $narr = explode(".", $filename);
        // return substr($filename, strrpos($filename, '.'));
        return strtolower($narr[count($narr) - 1]);
    }

    public function getFilesFromDirectory($path) {
        if (is_dir($path)) {
            return scandir($path);
        }
        return array();
    }

}
