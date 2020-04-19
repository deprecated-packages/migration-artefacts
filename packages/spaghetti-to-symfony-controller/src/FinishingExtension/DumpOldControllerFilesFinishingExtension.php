<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\FinishingExtension;

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\DataCollector\OldControllerFileInfoCollector;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Rector\Core\Contract\Extension\FinishingExtensionInterface;

final class DumpOldControllerFilesFinishingExtension implements FinishingExtensionInterface
{
    /**
     * @var OldControllerFileInfoCollector
     */
    private $oldControllerFileInfoCollector;

    public function __construct(OldControllerFileInfoCollector $oldControllerFileInfoCollector)
    {
        $this->oldControllerFileInfoCollector = $oldControllerFileInfoCollector;
    }

    public function run(): void
    {
        $data = $this->createData();

        // prevent unrelated Rector run data override
        if ($data === []) {
            return;
        }

        $json = Json::encode($data, Json::PRETTY);

        $filePath = getcwd() . '/old_controller_paths.json';
        FileSystem::write($filePath, $json);
    }

    /**
     * @return string[][]
     */
    private function createData(): array
    {
        $data = [];
        foreach ($this->oldControllerFileInfoCollector->getFileInfos() as $smartFileInfo) {
            $data[] = [
                'real_path' => $smartFileInfo->getRealPath(),
                'file_name' => $smartFileInfo->getFilename(),
            ];
        }

        return $data;
    }
}
