<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\DataCollector;

use Symplify\SmartFileSystem\SmartFileInfo;

final class OldControllerFileInfoCollector
{
    /**
     * @var SmartFileInfo[]
     */
    private $fileInfos = [];

    public function addFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $this->fileInfos[] = $smartFileInfo;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getFileInfos(): array
    {
        return $this->fileInfos;
    }
}
