<?php
/*
 * Copyright (c) Since 2007 PrestaShop.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PHPStanForPrestaShop\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStanForPrestaShop\PHPConfigurationLoader\ConfigurationLoaderInterface;

/**
 * @implements Rule<Node\Stmt\ClassMethod>
 */
class UseTypedReturnForNewMethodsRule implements Rule
{
    /** @var array */
    private $excludedClassMethodsList;

    /**
     * @param ConfigurationLoaderInterface $configurationFileLoader
     */
    public function __construct(ConfigurationLoaderInterface $configurationFileLoader)
    {
        $this->excludedClassMethodsList = $configurationFileLoader->load();
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    /**
     * @param ClassMethod $node
     * @param Scope $scope
     *
     * @return array
     * @throws \PHPStan\ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // Check if class method is part of the exclusion list. Yes => no violation.
        $className = $scope->getClassReflection()->getName();
        $fullMethodName = $className . '::' . $node->name;
        if (in_array($fullMethodName, $this->excludedClassMethodsList)) {
            return [];
        }

        // Check if class method has return type. If yes => no violation.
        if ($node->getReturnType()) {
            return [];
        }

        $parentClassNames = $scope->getClassReflection()->getParentClassesNames();
        if (empty($parentClassNames)) {
            // Class method does not use return type and has no parents => rule violation.
            return [
                RuleErrorBuilder::message(sprintf('Function %s should declare return type.', $node->name))
                    ->build(),
            ];
        }

        foreach ($parentClassNames as $parentClassName) {
            $fullParentMethodName = $parentClassName . '::' . $node->name;
            if (in_array($fullParentMethodName, $this->excludedClassMethodsList)) {
                // If class has parents who also have this class method
                // If they are part of the exclusion list => no violation.
                return [];
            }
        }

        return [
            RuleErrorBuilder::message(sprintf('Function %s should declare return type.', $node->name))
                ->build(),
        ];
    }
}
