<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\Rector;

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\Controller\OldControllerDetector;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\DataCollector\OldControllerFileInfoCollector;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\Naming\ControllerNaming;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\NodeFactory\ControllerFactory;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\Twig\ControllerTwigTemplateFactory;
use Nette\Utils\FileSystem;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspiration https://github.com/rectorphp/rector/blob/fa5d62948abe5847b1b9deb516477b5def4316b5/rules/autodiscovery/src/Rector/FileSystem/MoveValueObjectsToValueObjectDirectoryRector.php
 *
 * Goal https://github.com/symfony/demo/blob/master/src/Controller/BlogController.php
 *
 * @see \Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector\Tests\Rector\FunctionsToSymfonyControllerFileSystemRector\FunctionsToSymfonyControllerFileSystemRectorTest
 */
final class FunctionsToSymfonyControllerFileSystemRector extends AbstractFileSystemRector
{
    /**
     * @var OldControllerDetector
     */
    private $oldControllerDetector;

    /**
     * @var ControllerFactory
     */
    private $controllerFactory;

    /**
     * @var ControllerTwigTemplateFactory
     */
    private $controllerTwigTemplateFactory;

    /**
     * @var ControllerNaming
     */
    private $controllerNaming;

    /**
     * @var OldControllerFileInfoCollector
     */
    private $oldControllerFileInfoCollector;

    public function __construct(
        OldControllerDetector $oldControllerDetector,
        ControllerFactory $controllerFactory,
        ControllerTwigTemplateFactory $controllerTwigTemplateFactory,
        ControllerNaming $controllerNaming,
        OldControllerFileInfoCollector $oldControllerFileInfoCollector
    ) {
        $this->oldControllerDetector = $oldControllerDetector;
        $this->controllerFactory = $controllerFactory;
        $this->controllerTwigTemplateFactory = $controllerTwigTemplateFactory;
        $this->controllerNaming = $controllerNaming;
        $this->oldControllerFileInfoCollector = $oldControllerFileInfoCollector;
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        if (! $this->oldControllerDetector->detect($smartFileInfo)) {
            return;
        }

        $this->oldControllerFileInfoCollector->addFileInfo($smartFileInfo);

        $this->createAndPrintController($smartFileInfo);
        $this->createAndPrintTemplate($smartFileInfo);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Move controller-like PHP functions to Symfony controller + including Twig template'
        );
    }

    private function createAndPrintController(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        $controller = $this->controllerFactory->create($nodes, $smartFileInfo);
        $controllerFilePath = $this->createControllerFilePath($smartFileInfo);

        $this->printNewNodesToFilePath([$controller], $controllerFilePath);
    }

    private function createControllerFilePath(SmartFileInfo $smartFileInfo): string
    {
        $controllerDirectory = dirname($smartFileInfo->getRealPath()) . '/Symfony/Controller';
        $controllerClassName = $this->controllerNaming->createControllerClassName($smartFileInfo);

        return $controllerDirectory . '/' . $controllerClassName . '.php';
    }

    private function createAndPrintTemplate(SmartFileInfo $smartFileInfo): void
    {
        $templateFilePath = $this->createTemplateFilePath($smartFileInfo);
        $templateContent = $this->controllerTwigTemplateFactory->create($smartFileInfo);

        FileSystem::write($templateFilePath, $templateContent);
    }

    private function createTemplateFilePath(SmartFileInfo $smartFileInfo): string
    {
        $templateDirectory = dirname($smartFileInfo->getRealPath()) . '/../templates/controller';
        $fileName = $smartFileInfo->getBasenameWithoutSuffix() . '.twig';

        return $templateDirectory . '/' . $fileName;
    }
}
