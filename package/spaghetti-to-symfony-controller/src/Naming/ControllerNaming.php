<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\Naming;

use Rector\Core\Util\RectorStrings;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ControllerNaming
{
    public function createControllerClassName(SmartFileInfo $smartFileInfo): string
    {
        return RectorStrings::underscoreToCamelCase($smartFileInfo->getBasenameWithoutSuffix()) . 'Controller';
    }
}
