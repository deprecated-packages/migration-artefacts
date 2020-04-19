<?php

declare(strict_types=1);

namespace Migrify\MigrationArtefact\PhpHtmlToSymfonyController\Rector\NodeFactory;

use Migrify\MigrationArtefact\PhpHtmlToSymfonyController\Rector\Naming\ControllerNaming;
use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ControllerFactory
{
    /**
     * @var ControllerClassFactory
     */
    private $controllerClassFactory;

    /**
     * @var NamespaceFactory
     */
    private $namespaceFactory;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var ControllerNaming
     */
    private $controllerNaming;

    public function __construct(
        ControllerClassFactory $controllerClassFactory,
        NamespaceFactory $namespaceFactory,
        NodesToRemoveCollector $nodesToRemoveCollector,
        CallableNodeTraverser $callableNodeTraverser,
        ValueResolver $valueResolver,
        ControllerNaming $controllerNaming
    ) {
        $this->controllerClassFactory = $controllerClassFactory;
        $this->namespaceFactory = $namespaceFactory;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->valueResolver = $valueResolver;
        $this->controllerNaming = $controllerNaming;
    }

    /**
     * @param Node[] $nodes
     */
    public function create(array $nodes, SmartFileInfo $smartFileInfo): Namespace_
    {
        $controllerClassName = $this->controllerNaming->createControllerClassName($smartFileInfo);

        $this->removeRequires($nodes);
        $this->removeLastExit($nodes);

        // move imports above the namespace
        $useImports = $this->decoupleUseImports($nodes);
        $useImports[] = $this->createUse('Symfony\Component\Routing\Annotation\Route');

        $routePath = $smartFileInfo->getBasenameWithoutSuffix();
        $controllerClass = $this->controllerClassFactory->createFromNameAndStmts(
            $controllerClassName,
            $routePath,
            $nodes
        );

        return $this->namespaceFactory->createNamespaceFromClass($controllerClass, $useImports);
    }

    /**
     * @param Node[] $nodes
     * @return Use_[]
     */
    private function decoupleUseImports(array $nodes): array
    {
        $useNodes = [];
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use (&$useNodes) {
            if (! $node instanceof Use_) {
                return null;
            }

            // needs to be cloned so it's not removed
            $useNodes[] = clone $node;
            $this->nodesToRemoveCollector->addNodeToRemove($node);

            return null;
        });

        return $useNodes;
    }

    private function createUse(string $useImport): Use_
    {
        $useRouteImport = new UseUse(new Name($useImport));
        return new Use_([$useRouteImport]);
    }

    /**
     * Remove: require "jadro_composer.php";
     * @param Node[] $nodes
     */
    private function removeRequires(array $nodes): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) {
            if (! $node instanceof Include_) {
                return null;
            }

            if (! $this->valueResolver->isValue($node->expr, 'jadro_composer.php') && ! $this->valueResolver->isValue(
                $node->expr,
                'jadro_paticka.php'
            )) {
                return null;
            }

            $this->nodesToRemoveCollector->addNodeToRemove($node);

            return null;
        });
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    private function removeLastExit(array $nodes): array
    {
        $lastItemKey = array_key_last($nodes);
        if (! $lastItemKey) {
            return $nodes;
        }

        $lastNode = $nodes[$lastItemKey];

        // is exit expression?
        if ($this->isExpressionWithNodeType($lastNode, Exit_::class)) {
            $this->nodesToRemoveCollector->addNodeToRemove($lastNode);
            return $nodes;
        }

        // there is include "..." after exit;
        if ($this->isExpressionWithNodeType($lastNode, Include_::class)) {
            if (isset($nodes[$lastItemKey - 1])) {
                $preLastNode = $nodes[$lastItemKey - 1];
                if ($this->isExpressionWithNodeType($preLastNode, Exit_::class)) {
                    $this->nodesToRemoveCollector->addNodeToRemove($preLastNode);
                    return $nodes;
                }
            }
        }

        return $nodes;
    }

    private function isExpressionWithNodeType(Node $lastNode, string $nodeType): bool
    {
        if (! $lastNode instanceof Expression) {
            return false;
        }

        return is_a($lastNode->expr, $nodeType, true);
    }
}
