<?php

namespace App\Model;

use Nette;

class Files {

    use Nette\SmartObject;

    /** @var string */
    public $dirPath;

    /**
     * Files constructor.
     * @param string $appDir
     */
    public function __construct(string $appDir)
    {
        $this->dirPath = $appDir . "/../www/storage/";
    }

    /**
     * @param int $pubId
     * @return array
     */
    public function prepareFiles(int $pubId): array
    {
        $files = $this->getFilesFromDirectory($this->dirPath . $pubId);

        $fileArray = [];

        foreach ($files as $file) {
            if ($file == "." || $file == "..") {
                continue;
            } else {
                $fileArray[] = ['name' => $file, 'path' => $this->dirPath . $pubId . "/" . $file];
            }
        }

        return $fileArray;
    }

    /**
     * @param int $pubId
     */
    public function deleteFiles(int $pubId): void
    {

        $files = $this->prepareFiles($pubId);

        if (count($files)) {
            foreach ($files as $file) {
                if (is_file($file['path'])) {
                    unlink($file['path']);
                }
            }
        }
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFileExtension(string $filename): string
    {
        $narr = explode(".", $filename);
        // return substr($filename, strrpos($filename, '.'));
        return strtolower($narr[count($narr) - 1]);
    }

    /**
     * @param $path
     * @return array
     */
    public function getFilesFromDirectory(string $path): array
    {
        if (is_dir($path)) {
            return scandir($path);
        }
        return [];
    }

}
