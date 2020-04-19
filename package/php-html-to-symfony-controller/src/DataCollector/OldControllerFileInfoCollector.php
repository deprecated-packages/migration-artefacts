<?php

declare(strict_types=1);

namespace MigrationArtefact\PhpHtmlToSymfonyController\Rector\DataCollector;

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
