<?php

namespace App\Tests\Service\FS\FileSaver;

use App\Service\FS\FileSaver\LocalFileSaver;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocalFileSaverTest extends KernelTestCase
{
    private ContainerInterface $container;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();
    }

    public function testSave()
    {
        /** @var LocalFileSaver $localFileSaver */
        $localFileSaver = $this->container->get(LocalFileSaver::class);
        $projectDir = $this->container->getParameter('kernel.project_dir');

        $from = $projectDir . '/tests/_files/horizontal.jpg';
        $to = $projectDir . '/var/cache/dev/_horizontal.jpg';

        $localFileSaver->save($from, $to);
        $this->assertFileExists($to);
        unlink($to);
    }
}
