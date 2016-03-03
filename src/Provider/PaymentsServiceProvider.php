<?php

namespace Bolt\Extension\Bolt\Payments\Provider;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\Processor;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Payments service provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentsServiceProvider implements ServiceProviderInterface
{
    /** @var array */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['payments.config'] = $app->share(
            function () {
                return new Config($this->config);
            }
        );

        $app['payments.processor'] = $app->share(
            function ($app) {
                return new Processor(
                    $app['payments.config'],
                    $app['twig'],
                    $app['session'],
                    $app['storage']
                );
            }
        );

        $app['payments.controller.frontend'] = $app->share(
            function ($app) {
                return new Frontend($app['payments.config']);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
