<?php

namespace App\Service\FS\Thumbnail;

use App\Service\FS\FileSaver\FileSaver;
use GdImage;
use InvalidArgumentException;

class ThumbnailCreator
{
    private ?GdImage $image;
    private ?string $thumbnailPath;

    public function __construct()
    {
        $this->thumbnailPath = null;
    }

    public function process(string $imagePath, int $maxSideSize = 150): self
    {
        $this->clear();

        [$baseWidth, $baseHeight, $type] = $this->load($imagePath);
        $this->resample($baseWidth, $baseHeight, $maxSideSize, $type);
        $this->temporarySave($type);

        return $this;
    }

    public function getThumbnail(): ?GdImage
    {
        return $this->image;
    }

    public function save(FileSaver $saver, string $saveToPath, bool $withClear = false): self
    {
        $saver->save($this->thumbnailPath, $saveToPath);

        if ($withClear) {
            $this->clear();
        }

        return $this;
    }

    public function clear(): void
    {
        if ($this->thumbnailPath) {
            unlink($this->thumbnailPath);
            $this->image = null;
            $this->thumbnailPath = null;
        }
    }

    public function __destruct()
    {
        $this->clear();
    }

    private function load(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image: $imagePath do not exist!");
        }

        [$baseWidth, $baseHeight, $type] = getimagesize($imagePath);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($imagePath);
                break;
            case IMAGETYPE_WBMP:
                $this->image = imagecreatefromwbmp($imagePath);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($imagePath);
                break;
            default:
                throw new InvalidArgumentException('Image must be one of that types: jpeg, wbmp or png');
        }

        if (!(($baseWidth > 0) && ($baseHeight > 0))) {
            throw new InvalidArgumentException("Image not read or is empty");
        }

        return [$baseWidth, $baseHeight, $type];
    }

    private function resample(int $baseWidth, int $baseHeight, int $maxSideSize, int $type): void
    {
        $originalRatio = $baseWidth / $baseHeight;
        $neededRation = 1;

        [$newWidth, $newHeight] = ($neededRation > $originalRatio)
            ? [$baseWidth * $maxSideSize / $baseHeight, $maxSideSize]
            : [$maxSideSize, $baseHeight * $maxSideSize / $baseWidth];

        $newWidth = (int)$newWidth;
        $newHeight = (int)$newHeight;

        $imageReSampled = imagecreatetruecolor($newWidth, $newHeight);

        if ($type == IMAGETYPE_PNG) {
            imagecolortransparent($imageReSampled, imagecolorallocatealpha($imageReSampled, 0, 0, 0, 127));
            imagealphablending($imageReSampled, false);
            imagesavealpha($imageReSampled, true);
        }

        imagecopyresampled($imageReSampled, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $baseWidth, $baseHeight);
        $this->image = $imageReSampled;
    }

    private function temporarySave(int $type): void
    {
        $this->thumbnailPath = stream_get_meta_data(tmpfile())['uri'];
        ($type == IMAGETYPE_PNG)
            ? imagepng($this->image, $this->thumbnailPath, 9)
            : imagejpeg($this->image, $this->thumbnailPath, 80);
    }
}