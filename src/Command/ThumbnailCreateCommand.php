<?php

namespace App\Command;

use App\Enum\AvailableThumbnailFileSaverEnum;
use App\Service\FS\FileSaver\FileSaver;
use App\Service\FS\FileSaver\LocalFileSaver;
use App\Service\FS\FileSaver\S3Saver;
use App\Service\FS\Thumbnail\ThumbnailCreator;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:thumbnail:create',
    description: 'Create thumbnail for selected image and save to chosen place',
)]
class ThumbnailCreateCommand extends Command
{
    private ThumbnailCreator $thumbnailCreator;
    private LocalFileSaver $localFileSaver;
    private S3Saver $s3Saver;

    public function __construct(
        ThumbnailCreator $thumbnailCreator,
        LocalFileSaver $localFileSaver,
        S3Saver $s3Saver
    )
    {
        $this->thumbnailCreator = $thumbnailCreator;
        $this->localFileSaver = $localFileSaver;
        $this->s3Saver = $s3Saver;

        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('image', InputArgument::REQUIRED, 'Image full path')
            ->addArgument('path', InputArgument::REQUIRED, 'Thumbnail save path')
            ->addOption(
                'saver',
                null,
                InputOption::VALUE_REQUIRED,
                'Chose saver from list: ' . implode(', ', AvailableThumbnailFileSaverEnum::values()),
                AvailableThumbnailFileSaverEnum::LOCAL->value
            )
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $imagePath = $input->getArgument('image');
        $savePath = $input->getArgument('path');
        $saverName = $input->getOption('saver');

        $this->thumbnailCreator
            ->process($imagePath)
            ->save($this->getSelectedSaver($saverName), $savePath)
            ->clear();

        $io->success('Thumbnail successfully created and saved');

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function getSelectedSaver(string $saverName): FileSaver
    {
        $selectedSaverConst = AvailableThumbnailFileSaverEnum::from($saverName);
        switch ($selectedSaverConst) {
            case AvailableThumbnailFileSaverEnum::LOCAL:
                return $this->localFileSaver;
            case AvailableThumbnailFileSaverEnum::AWS_S3:
                return $this->s3Saver;
        }

        throw new Exception('That row must be never reached since used Enum::from and default CommandOption value');
    }
}
