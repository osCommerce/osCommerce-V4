<?php

namespace Indigo\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Override the autoloader generator.
 *
 * This might conflict with other plugins dealing with the autoloader as well.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class DoctrineAnnotationAutoloadPlugin implements PluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $autoloadGenerator = new Autoload\DoctrineAutoloadGenerator($composer->getEventDispatcher(), $io);

        $composer->setAutoloadGenerator($autoloadGenerator);
    }
}
