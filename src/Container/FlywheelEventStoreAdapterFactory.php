<?php

/*
 * This file is part of the prooph/event-store-flywheel-adapter.
 *
 * (c) 2016 prooph software GmbH <contact@prooph.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prooph\EventStore\Adapter\Flywheel\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\Common\Messaging\MessageConverter;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\Common\Messaging\NoOpMessageConverter;
use Prooph\EventStore\Adapter\Flywheel\FlywheelEventStoreAdapter;
use Prooph\EventStore\Exception\ConfigurationException;

final class FlywheelEventStoreAdapterFactory implements RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    /**
     * {@inheritdoc}
     */
    public function vendorName()
    {
        return 'prooph';
    }

    /**
     * {@inheritdoc}
     */
    public function packageName()
    {
        return 'event_store';
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions()
    {
        return ['adapter' => ['options']];
    }

    /**
     * @param ContainerInterface $container
     *
     * @throws ConfigurationException
     *
     * @return FlywheelEventStoreAdapter
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $this->options($config)['adapter']['options'];

        $dir = null;

        if (isset($config['dir'])) {
            $dir = $config['dir'];
        }

        if (! is_dir($dir)) {
            throw ConfigurationException::configurationError(sprintf(
                '%s was not able to locate %s',
                __CLASS__,
                $dir
            ));
        }

        $messageFactory = $container->has(MessageFactory::class)
            ? $container->get(MessageFactory::class)
            : new FQCNMessageFactory();

        $messageConverter = $container->has(MessageConverter::class)
            ? $container->get(MessageConverter::class)
            : new NoOpMessageConverter();

        return new FlywheelEventStoreAdapter($dir, $messageFactory, $messageConverter);
    }
}
