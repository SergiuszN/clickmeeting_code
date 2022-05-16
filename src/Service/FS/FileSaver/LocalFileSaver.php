<?php

namespace App\Service\FS\FileSaver;

use App\Exception\FS\FileSaver\FileNotExistException;
use App\Exception\FS\FileSaver\FileNotSavedException;

class LocalFileSaver implements FileSaver
{
    /**
     * @throws FileNotExistException
     * @throws FileNotSavedException
     */
    public function save(string $fromPath, string $toPath)
    {
        if (!file_exists($fromPath)) {
            throw new FileNotExistException("File not exist: $fromPath");
        }

        if (!copy($fromPath, $toPath)) {
            throw new FileNotSavedException("From: $fromPath, To: $toPath");
        }
    }
}