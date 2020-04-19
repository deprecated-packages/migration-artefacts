<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\Twig;

use Migrify\MigrationArtefact\SpaghettiToSymfonyController\Naming\ControllerNaming;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ControllerTwigTemplateFactory
{
    /**
     * @var string
     */
    private const TEMPLATE_TEMPLATE = <<<'CODE_SAMPLE'
{%% extends "base.twig" %%}

{%% block main %%}
    {{ render(controller('LegacyApp\\Core\\Symfony\\Controller\\%s::content')) }}
{%% endblock %%}
CODE_SAMPLE;

    /**
     * @var ControllerNaming
     */
    private $controllerNaming;

    public function __construct(ControllerNaming $controllerNaming)
    {
        $this->controllerNaming = $controllerNaming;
    }

    public function create(SmartFileInfo $smartFileInfo): string
    {
        $controllerClass = $this->controllerNaming->createControllerClassName($smartFileInfo);

        return sprintf(self::TEMPLATE_TEMPLATE, $controllerClass) . PHP_EOL;
    }
}
