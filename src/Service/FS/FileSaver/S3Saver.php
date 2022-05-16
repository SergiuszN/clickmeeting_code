<?php

namespace App\Service\FS\FileSaver;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class S3Saver implements FileSaver
{
    private S3Client $s3Client;
    private string $s3BucketName;

    public function __construct(S3Client $s3Client, ParameterBagInterface $bag)
    {
        $this->s3Client = $s3Client;
        $this->s3BucketName = (string)$bag->get('aws_s3_bucket_name');
    }

    public function save(string $fromPath, string $toPath)
    {
        $this->s3Client->putObject([
            'Bucket' => $this->s3BucketName,
            'Key' => $toPath,
            'Body' => fopen($fromPath, 'r'),
            'ACL' => 'public-read',
            'ServerSideEncryption' => 'AES256'
        ]);
    }
}