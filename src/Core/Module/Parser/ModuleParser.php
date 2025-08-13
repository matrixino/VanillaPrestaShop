<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\PrestaShop\Core\Module\Parser;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeDumper;
use PhpParser\NodeFinder;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PrestaShop\PrestaShop\Core\Exception\InvalidArgumentException;

/**
 * This parser scan the PHP code of a module main class and extracts information from
 * static PHP analysis.
 */
class ModuleParser
{
    private Parser $phpParser;

    private NodeFinder $nodeFinder;

    private const DEFAULT_MODULE_PROPERTIES = [
        'name',
        'tab',
        'version',
        'ps_versions_compliancy',
        'author',
        'displayName',
        'description',
    ];

    public function __construct(
        private readonly array $extractedModuleProperties = self::DEFAULT_MODULE_PROPERTIES,
    ) {
        $this->phpParser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $this->nodeFinder = new NodeFinder();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function parseModule(string $moduleClassPath): array
    {
        $statements = $this->parseModuleStatements($moduleClassPath);
        $classMethods = $this->getModuleMethods($statements);

        if (empty($classMethods['__construct'])) {
            throw new InvalidArgumentException('Module constructor not found');
        }

        $moduleInfos = $this->getModulePropertiesAssignments($classMethods['__construct']);
        $moduleInfos['hooks'] = $this->extractHooks($classMethods);

        return $moduleInfos;
    }

    /**
     * Parse the whole module and dump it, very convenient for debugging.
     *
     * @throws InvalidArgumentException
     */
    public function dumpModuleNodes(string $moduleClassPath): string
    {
        $nodeDumper = new NodeDumper();
        $statements = $this->parseModuleStatements($moduleClassPath);

        return $nodeDumper->dump($statements);
    }

    private function getModulePropertiesAssignments(ClassMethod $classMethod): array
    {
        $assignedProperties = [];
        /** @var Stmt $stmt */
        foreach ($classMethod->stmts as $stmt) {
            // We only look for expressions like: "$this = something;"
            if ($stmt instanceof Expression) {
                if ($stmt->expr instanceof Assign) {
                    if ($stmt->expr->var instanceof PropertyFetch) {
                        if ($stmt->expr->var->var instanceof Variable) {
                            $assignHolder = $stmt->expr->var->var->name;
                        }
                    }
                }
            }
            if (!$stmt instanceof Expression
                || !$stmt->expr instanceof Assign
                || !$stmt->expr->var instanceof PropertyFetch
                || !$stmt->expr->var->var instanceof Variable
                || $stmt->expr->var->var->name !== 'this') {
                continue;
            }

            if (!$stmt->expr->var->name instanceof Identifier) {
                continue;
            }

            $propertyName = $stmt->expr->var->name->name;
            // We only extract properties defined for this parser, unless it is empty then all properties are parsed
            if (!empty($this->extractedModuleProperties) && !in_array($propertyName, $this->extractedModuleProperties)) {
                continue;
            }

            $propertyValue = $this->getExpressionValue($stmt->expr->expr);
            if (null !== $propertyValue) {
                $assignedProperties[$propertyName] = $propertyValue;
            }
        }

        return $assignedProperties;
    }

    private function getExpressionValue(Expr $expr): mixed
    {
        if ($expr instanceof String_) {
            return $expr->value;
        }
        if ($expr instanceof DNumber) {
            return $expr->value;
        }
        if ($expr instanceof LNumber) {
            return $expr->value;
        }
        if ($expr instanceof ConstFetch) {
            return $expr->name;
        }
        if ($expr instanceof Array_) {
            return $this->getArrayValue($expr);
        }

        return null;
    }

    private function getArrayValue(Array_ $array): ?array
    {
        $arrayValue = [];
        foreach ($array->items as $item) {
            $keyValue = $this->getExpressionValue($item->key);
            if (is_scalar($keyValue)) {
                $arrayValue[$keyValue] = $this->getExpressionValue($item->value);
            }
        }

        return !empty($arrayValue) ? $arrayValue : null;
    }

    /**
     * @param Stmt[] $statements
     *
     * @return array<string, ClassMethod>
     */
    private function getModuleMethods(array $statements): array
    {
        /** @var ClassMethod[] $classMethods */
        $classMethods = $this->nodeFinder->find($statements, static function (Node $node) {
            return $node instanceof ClassMethod;
        });

        $mappedMethods = [];
        foreach ($classMethods as $classMethod) {
            $mappedMethods[$classMethod->name->name] = $classMethod;
        }

        return $mappedMethods;
    }

    /**
     * @param string $moduleClassPath
     *
     * @return Stmt[]
     *
     * @throws InvalidArgumentException
     */
    private function parseModuleStatements(string $moduleClassPath): array
    {
        if (!file_exists($moduleClassPath)) {
            throw new InvalidArgumentException('Module file not found: ' . $moduleClassPath);
        }

        $fileContent = file_get_contents($moduleClassPath);

        $statements = $this->phpParser->parse($fileContent);
        if (empty($statements)) {
            throw new InvalidArgumentException('Could not parse module file: ' . $moduleClassPath);
        }

        return $statements;
    }

    /**
     * @param ClassMethod[] $classMethods
     *
     * @return string[]
     */
    private function extractHooks(array $classMethods): array
    {
        $hooks = [];
        foreach ($classMethods as $classMethod) {
            $methodName = $classMethod->name->name;
            if (str_starts_with(strtolower($methodName), 'hook')) {
                // Remove the prefix hook to get the hook name
                $hooks[] = lcfirst(substr($methodName, 4));
            }
        }

        return $hooks;
    }
}
