<?php

namespace App\Service\FS\FileSaver;

interface FileSaver
{
    public function save(string $fromPath, string $toPath);
}