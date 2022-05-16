<?php

namespace App\Tests\Service\Thumbnail;

use App\Service\FS\FileSaver\LocalFileSaver;
use App\Service\FS\Thumbnail\ThumbnailCreator;
use GdImage;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ThumbnailCreatorTest extends KernelTestCase
{
    private const HORIZONTAL_IMAGE_PATH = __DIR__ . '/../../../_files/horizontal.jpg';
    private const VERTICAL_IMAGE_PATH = __DIR__ . '/../../../_files/vertical.jpg';

    private ContainerInterface $container;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->container = static::getContainer();
    }

    public function testProcess()
    {
        /** @var ThumbnailCreator $thumbnailCreator */
        $thumbnailCreator = $this->container->get(ThumbnailCreator::class);

        $thumbnailCreator->process(self::HORIZONTAL_IMAGE_PATH);

        /** @var GdImage $thumbnail */
        $thumbnail = $thumbnailCreator->getThumbnail();

        $this->assertTrue($thumbnail instanceof GdImage);
        $this->assertEquals(150, imagesx($thumbnail));

        $thumbnailCreator->clear();
        $thumbnailCreator->process(self::VERTICAL_IMAGE_PATH);
        $thumbnail = $thumbnailCreator->getThumbnail();

        $this->assertTrue($thumbnail instanceof GdImage);
        $this->assertEquals(150, imagesy($thumbnail));
    }

    public function testClear()
    {
        /** @var ThumbnailCreator $thumbnailCreator */
        $thumbnailCreator = $this->container->get(ThumbnailCreator::class);

        $thumbnailCreator->process(self::HORIZONTAL_IMAGE_PATH);
        $thumbnailCreator->clear();
        $this->assertTrue($thumbnailCreator->getThumbnail() === null);
    }

    public function testSave()
    {
        $localFileSaverMock = $this->getMockBuilder(LocalFileSaver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        /** @var ThumbnailCreator $thumbnailCreator */
        $thumbnailCreator = $this->container->get(ThumbnailCreator::class);
        $thumbnailCreator->process(self::HORIZONTAL_IMAGE_PATH);
        $thumbnailCreator->save($localFileSaverMock, 'path', true);
        $this->assertTrue($thumbnailCreator->getThumbnail() === null);
    }
}
