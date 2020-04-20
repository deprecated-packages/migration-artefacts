<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Rector;

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Controller\OldControllerDetector;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\DataCollector\OldControllerFileInfoCollector;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Naming\ControllerNaming;
use Migrify\MigrationArtefact\SpaghettiToSymfonyController\NodeFactory\ControllerFactory;
use Nette\Utils\FileSystem;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspiration https://github.com/rectorphp/rector/blob/fa5d62948abe5847b1b9deb516477b5def4316b5/rules/autodiscovery/src/Rector/FileSystem/MoveValueObjectsToValueObjectDirectoryRector.php
 *
 * Goal https://github.com/symfony/demo/blob/master/src/Controller/BlogController.php
 *
 * @see \Migrify\MigrationArtefact\SpaghettiToSymfonyController\Tests\Rector\FunctionsToSymfonyControllerFileSystemRector\FunctionsToSymfonyControllerFileSystemRectorTest
 */
final class FunctionsToSymfonyControllerFileSystemRector extends AbstractFileSystemRector
{
    /**
     * @var string
     */
    private const TEMPLATE_TEMPLATE = <<<'CODE_SAMPLE'
{% extends "base.twig" %}

{% block main %}
    {{ content }}
{% endblock %}
CODE_SAMPLE;

    /**
     * @var OldControllerDetector
     */
    private $oldControllerDetector;

    /**
     * @var ControllerFactory
     */
    private $controllerFactory;

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
        ControllerNaming $controllerNaming,
        OldControllerFileInfoCollector $oldControllerFileInfoCollector
    ) {
        $this->oldControllerDetector = $oldControllerDetector;
        $this->controllerFactory = $controllerFactory;
        $this->controllerNaming = $controllerNaming;
        $this->oldControllerFileInfoCollector = $oldControllerFileInfoCollector;
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        if (! $this->oldControllerDetector->isOldController($smartFileInfo)) {
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

    private function createAndPrintTemplate(SmartFileInfo $smartFileInfo): void
    {
        $templateFilePath = $this->createTemplateFilePath($smartFileInfo);
        $templateContent = self::TEMPLATE_TEMPLATE . PHP_EOL;

        FileSystem::write($templateFilePath, $templateContent);
    }

    private function createControllerFilePath(SmartFileInfo $smartFileInfo): string
    {
        $controllerDirectory = dirname($smartFileInfo->getRealPath()) . '/Symfony/Controller';
        $controllerClassName = $this->controllerNaming->createControllerClassName($smartFileInfo);

        return $controllerDirectory . '/' . $controllerClassName . '.php';
    }

    private function createTemplateFilePath(SmartFileInfo $smartFileInfo): string
    {
        $templateDirectory = dirname($smartFileInfo->getRealPath()) . '/../templates/controller';
        $fileName = $smartFileInfo->getBasenameWithoutSuffix() . '.twig';

        return $templateDirectory . '/' . $fileName;
    }
}
