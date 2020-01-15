<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @group legacy
 */
class AddConsoleCommandPassTest extends TestCase
{
    /**
     * @dataProvider visibilityProvider
     */
    public function testProcess($public)
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->setParameter('my-command.class', 'Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\Compiler\MyCommand');

        $definition = new Definition('%my-command.class%');
        $definition->setPublic($public);
        $definition->addTag('console.command');
        $container->setDefinition('my-command', $definition);

        $container->compile();

        $alias = 'console.command.symfony_bundle_frameworkbundle_tests_dependencyinjection_compiler_mycommand';

        if ($public) {
            $this->assertFalse($container->hasAlias($alias));
            $id = 'my-command';
        } else {
            $id = $alias;
            // The alias is replaced by a Definition by the ReplaceAliasByActualDefinitionPass
            // in case the original service is private
            $this->assertFalse($container->hasDefinition('my-command'));
            $this->assertTrue($container->hasDefinition($alias));
        }

        $this->assertTrue($container->hasParameter('console.command.ids'));
        $this->assertSame([$alias => $id], $container->getParameter('console.command.ids'));
    }

    public function visibilityProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "my-command" tagged "console.command" must not be abstract.
     */
    public function testProcessThrowAnExceptionIfTheServiceIsAbstract()
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new AddConsoleCommandPass());

        $definition = new Definition('Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\Compiler\MyCommand');
        $definition->addTag('console.command');
        $definition->setAbstract(true);
        $container->setDefinition('my-command', $definition);

        $container->compile();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The service "my-command" tagged "console.command" must be a subclass of "Symfony\Component\Console\Command\Command".
     */
    public function testProcessThrowAnExceptionIfTheServiceIsNotASubclassOfCommand()
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new AddConsoleCommandPass());

        $definition = new Definition('SplObjectStorage');
        $definition->addTag('console.command');
        $container->setDefinition('my-command', $definition);

        $container->compile();
    }

    public function testProcessPrivateServicesWithSameCommand()
    {
        $container = new ContainerBuilder();
        $className = 'Symfony\Bundle\FrameworkBundle\Tests\DependencyInjection\Compiler\MyCommand';

        $definition1 = new Definition($className);
        $definition1->addTag('console.command')->setPublic(false);

        $definition2 = new Definition($className);
        $definition2->addTag('console.command')->setPublic(false);

        $container->setDefinition('my-command1', $definition1);
        $container->setDefinition('my-command2', $definition2);

        (new AddConsoleCommandPass())->process($container);

        $alias1 = 'console.command.symfony_bundle_frameworkbundle_tests_dependencyinjection_compiler_mycommand';
        $alias2 = $alias1.'_my-command2';
        $this->assertTrue($container->hasAlias($alias1));
        $this->assertTrue($container->hasAlias($alias2));
    }
}

class MyCommand extends Command
{
}
