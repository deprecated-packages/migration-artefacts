<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\PhpHtmlToSymfonyController\Rector\NodeFactory;

use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;

final class NamespaceFactory
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(BuilderFactory $builderFactory)
    {
        $this->builderFactory = $builderFactory;
    }

    /**
     * @param Use_[] $uses
     */
    public function createNamespaceFromClass(Class_ $controllerClass, array $uses): Namespace_
    {
        $namespaceBuilder = $this->builderFactory->namespace('LegacyApp\Core\Symfony\Controller');
        foreach ($uses as $use) {
            $namespaceBuilder->addStmt($use);
        }

        $namespaceBuilder->addStmt($controllerClass);

        return $namespaceBuilder->getNode();
    }
}
