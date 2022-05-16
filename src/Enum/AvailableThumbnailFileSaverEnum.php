<?php

namespace App\Enum;

enum AvailableThumbnailFileSaverEnum: string
{
    case LOCAL = 'local';
    case AWS_S3 = 'aws_s3';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}