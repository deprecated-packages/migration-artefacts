<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\SpaghettiToSymfonyController\ValueObject;

use Symfony\Component\HttpFoundation\Response;

final class SymfonyClass
{
    /**
     * @var string
     */
    public const RESPONSE_CLASS = Response::class;

    /**
     * @var string
     */
    public const ABSTRACT_CONTROLLER_CLASS = 'Symfony\Bundle\FrameworkBundle\Controller\AbstractController';
}
