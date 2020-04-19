<?php

use MigrationArtefact\PhpHtmlToSymfonyController\Rector\DataProvider\OldControllerPathsDataProvider;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Finder\Finder;
use Symplify\SmartFileSystem\Finder\FinderSanitizer;
use Symplify\SmartFileSystem\SmartFileInfo;

require __DIR__ . '/../../../vendor/autoload.php';

final class ReplaceOldPathsWithRoutes
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
        $fileNameToRoutes = $this->oldControllerPathsDataProvider->provideFileNameToRoutes();

        foreach ($this->findPhpFiles() as $phpFileInfo) {
            $fileContent = $phpFileInfo->getContents();
            $originalFileContent = $fileContent;

            foreach ($fileNameToRoutes as $fileName => $route) {
                $quotedFileName = preg_quote($fileName, '#');
                $quotedFileName = '#' . $quotedFileName . '#';
                $fileContent = Strings::replace($fileContent, $quotedFileName, $route);
            }

            if ($fileContent === $originalFileContent) {
                // no change
                continue;
            }

            FileSystem::write($phpFileInfo->getRealPath(), $fileContent);

            echo sprintf('[CHANGE] Updating "%s"', $phpFileInfo->getFilename()) . PHP_EOL;
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

(new ReplaceOldPathsWithRoutes())->run();
