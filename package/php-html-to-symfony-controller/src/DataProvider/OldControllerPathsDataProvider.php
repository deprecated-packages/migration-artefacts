<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\PhpHtmlToSymfonyController\Rector\DataProvider;

use Migrify\MigrationArtefact\PhpHtmlToSymfonyController\Rector\Rector\FunctionsToSymfonyControllerFileSystemRector;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;

final class OldControllerPathsDataProvider
{
    /**
     * @var mixed[]
     */
    private $data = [];

    public function provide()
    {
        if ($this->data !== []) {
            return $this->data;
        }

        $oldControllerPathFile = getcwd() . '/old_controller_paths.json';

        $data = $this->loadFileToJson($oldControllerPathFile);
        $data = array_filter($data, function (array $item): bool {
            // filter out included files
            return ! in_array($item['file_name'], ['jadro_composer.php', 'jadro_paticka.php'], true);
        });

        $this->data = $data;

        return $this->data;
    }

    /**
     * @return string[]
     */
    public function provideFileNames(): array
    {
        $fileNames = [];
        foreach ($this->provide() as $data) {
            $fileNames[] = $data['file_name'];
        }

        return $fileNames;
    }

    /**
     * @return string[]
     */
    public function provideFileNameToRoutes(): array
    {
        $fileNameToRoutes = [];
        foreach ($this->provide() as $data) {
            $fileName = $data['file_name'];
            $route = $this->resolveRoute($fileName);

            $fileNameToRoutes[$fileName] = $route;
        }

        return $fileNameToRoutes;
    }

    /**
     * @return mixed[]
     */
    private function loadFileToJson(string $filePath): array
    {
        $this->ensureFileExists($filePath);

        $fileContent = FileSystem::read($filePath);

        $data = Json::decode($fileContent, Json::FORCE_ARRAY);
        $this->ensureHasData($data, $filePath);

        return $data;
    }

    private function ensureFileExists(string $oldControllerPathFile): void
    {
        if (file_exists($oldControllerPathFile)) {
            return;
        }

        $message = sprintf(
            'File "%s" was not found. Be sure to run first "%s" Rector.',
            $oldControllerPathFile,
            FunctionsToSymfonyControllerFileSystemRector::class
        );

        throw new ShouldNotHappenException($message);
    }

    private function ensureHasData(array $data, string $filePath): void
    {
        if ($data !== []) {
            return;
        }

        $message = sprintf(
            'There are no data in "%s". Be sure to run first "%s" Rector',
            $filePath,
            FunctionsToSymfonyControllerFileSystemRector::class
        );

        throw new ShouldNotHappenException($message);
    }

    private function resolveRoute(string $fileName): string
    {
        $matches = Strings::match($fileName, '#(?<route>.*?)\.php$#');
        return (string) $matches['route'];
    }
}
