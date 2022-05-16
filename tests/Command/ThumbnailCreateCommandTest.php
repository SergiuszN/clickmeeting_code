<?php

namespace App\Tests\Command;

use App\Kernel;
use App\Service\FS\FileSaver\LocalFileSaver;
use App\Service\FS\FileSaver\S3Saver;
use App\Service\FS\Thumbnail\ThumbnailCreator;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ThumbnailCreateCommandTest extends KernelTestCase
{
    private ContainerInterface $container;
    private Kernel $symfonyKernel;

    protected function setUp(): void
    {
        $this->symfonyKernel = self::bootKernel();
        $this->container = static::getContainer();
    }

    public function testExecute()
    {
        $thumbnailCreatorMock = $this->getMockBuilder(ThumbnailCreator::class)->disableOriginalConstructor()->getMock();
        $this->container->set(ThumbnailCreator::class, $thumbnailCreatorMock);

        $localFileSaverMock = $this->getMockBuilder(LocalFileSaver::class)->disableOriginalConstructor()->getMock();
        $this->container->set(LocalFileSaver::class, $localFileSaverMock);

        $s3SaverMock = $this->getMockBuilder(S3Saver::class)->disableOriginalConstructor()->getMock();
        $this->container->set(S3Saver::class, $s3SaverMock);

        $application = new Application($this->symfonyKernel);
        $command = $application->find('app:thumbnail:create');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'image' => '/test/path/to/image',
            'path' => '/test/path/to/thumbnail',
            '--saver' => 'local',
        ]);

        $commandTester->assertCommandIsSuccessful();

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Thumbnail successfully created and saved', $output);
    }
}
