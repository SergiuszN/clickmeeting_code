<?php

namespace App\Tests\Service\FS\FileSaver;

use App\Service\FS\FileSaver\S3Saver;
use Aws\S3\S3Client;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class S3SaverTest extends KernelTestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();
    }

    public function testSave()
    {
        $s3Client = $this->getMockBuilder(S3Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $projectDir = $this->container->getParameter('kernel.project_dir');
        $s3Saver = new S3Saver($s3Client, $this->container->get(ParameterBagInterface::class));
        $s3Saver->save($projectDir . '/tests/_files/horizontal.jpg', '/to/test');
        $this->assertTrue(true);
    }
}
