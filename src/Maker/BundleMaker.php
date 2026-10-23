<?php

declare(strict_types=1);

/*
 * This file is part of the tito10047/symfony-bundle-maker package.
 *
 * (c) Jozef Môstka <jozef@mostka.sk>
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tito10047\BundleMaker\Maker;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use Tito10047\BundleMaker\Utils\Inflector;

class BundleMaker
{
    public function __construct(private readonly string $bundleDir)
    {
    }

    public function make(BundleOptions $options): void
    {
        $directory = $this->bundleDir.'/src';

        if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created.', $directory));
        }

        $namespace = Inflector::namespacefy($options->name);
        $className = Inflector::className($options->name);

        $factory = new BuilderFactory();
        $fileAst = $factory->namespace($namespace);
        $classAst = $factory->class($className)->extend('AbstractBundle');
        $classAst->setDocComment(<<<EOF

            /**
             * @link https://symfony.com/doc/current/bundles/best_practices.html
             */
            EOF
        );

        if ($options->hasConfig) {
            $fileAst->addStmt($factory->use('Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator'));

            $classAst->addStmt(
                $factory->method('configure')->makePublic()
                    ->addParam($factory->param('definition')->setType('DefinitionConfigurator'))
                    ->setReturnType('void')
                    ->addStmt($factory->methodCall($factory->var('definition'), 'import', ['../config/definition.php']))
            );
        }

        $fileAst->addStmt($factory->use('Symfony\Component\DependencyInjection\ContainerBuilder'))
            ->addStmt($factory->use('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator'))
            ->addStmt($factory->use('Symfony\Component\HttpKernel\Bundle\AbstractBundle'));

        $loadExtensionMethodAst = $factory->method('loadExtension')->makePublic()
            ->addParam($factory->param('config')->setType('array'))
            ->addParam($factory->param('container')->setType('ContainerConfigurator'))
            ->addParam($factory->param('builder')->setType('ContainerBuilder'))
            ->setReturnType('void')
            ->addStmt($factory->methodCall($factory->var('container'), 'import', ['../config/services.php']))
        ;

        if ($options->hasConfig) {
            $loadExtensionMethodAst->setDocComment("\r");
        }

        $classAst->addStmt($loadExtensionMethodAst);
        $fileAst->addStmt($classAst);

        $code = (new Standard())->prettyPrintFile([$fileAst->getNode()]);
        file_put_contents(\sprintf('%s/%s.php', $directory, $className), $code);
    }
}
