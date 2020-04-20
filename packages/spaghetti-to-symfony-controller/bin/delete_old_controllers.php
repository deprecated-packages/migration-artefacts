<?php

declare(strict_types=1);

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\DataProvider\OldControllerPathsDataProvider;
use Nette\Utils\FileSystem;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

require __DIR__ . '/../../../vendor/autoload.php';

final class DeleteOldControllers
{
    /**
     * @var FinderSanitizer
     */
    private $finderSanitizer;

    /**
     * @var OldControllerPathsDataProvider
     */
    private $oldControllerPathsDataProvider;

    public function __construct()
    {
        $this->finderSanitizer = new FinderSanitizer();
        $this->oldControllerPathsDataProvider = new OldControllerPathsDataProvider();
    }

    public function run(): void
    {
        $fileNames = $this->oldControllerPathsDataProvider->provideFileNames();
        foreach ($this->findPhpFiles() as $phpFileInfo) {
            if (! in_array($phpFileInfo->getFilename(), $fileNames, true)) {
                continue;
            }

            FileSystem::delete($phpFileInfo->getRealPath());
            echo sprintf('[DELETION] File "%s" was be deleted', $phpFileInfo->getFilename()) . PHP_EOL;
        }
    }

    /**
     * @return SmartFileInfo[]
     */
    private function findPhpFiles(): array
    {
        $finder = (new Finder())->files()
            ->in(__DIR__ . '/../../../src')
            ->name('*.php')
            ->sortByName();

        return $this->finderSanitizer->sanitize($finder);
    }
}

(new DeleteOldControllers())->run();
