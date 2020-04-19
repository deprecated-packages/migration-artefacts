<?php

declare(strict_types=1);

namespace MigrationArtefact\PhpHtmlToSymfonyController\Rector\ValueObject;

final class SymfonyClass
{
    /**
     * @var string
     */
    public const RESPONSE_CLASS = 'Symfony\Component\HttpFoundation\Response';

    /**
     * @var string
     */
    public const ABSTRACT_CONTROLLER_CLASS = 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController';
}
