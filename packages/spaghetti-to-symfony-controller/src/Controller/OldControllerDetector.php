<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Controller;

use Nette\Utils\Strings;
use Symplify\SmartFileSystem\SmartFileInfo;

final class OldControllerDetector
{
    /**
     * @see https://regex101.com/r/QQ9Kpm/1/
     * @var string
     */
    private const ACTION_LIKE_FILE_PATTERN = '#^([\dA-Za-z]+)(_[\dA-Za-z]+)+\.php$#';

    /**
     * @see https://regex101.com/r/dX1Ro9/1/
     * @var string
     */
    private const ATYPICAL_CONTROLLER_REGEX = '#(main1|diskuse|fotbalek|gsuite|opplan|sablona|vizitkator|xtree|dl)\.php$#';

    /**
     * @var string
     */
    private const EXCLUDED_FILES = ['jadro_composer.php', 'jadro_paticka.php'];

    public function isOldController(SmartFileInfo $smartFileInfo): bool
    {
        if (in_array($smartFileInfo->getFilename(), self::EXCLUDED_FILES, true)) {
            return false;
        }

        if ((bool) Strings::match($smartFileInfo->getFilename(), self::ACTION_LIKE_FILE_PATTERN)) {
            return true;
        }

        return (bool) Strings::match($smartFileInfo->getFilename(), self::ATYPICAL_CONTROLLER_REGEX);
    }
}
