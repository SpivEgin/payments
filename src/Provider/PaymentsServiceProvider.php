<?php

namespace Bolt\Extension\Bolt\Payments\Provider;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\Form;
use Bolt\Extension\Bolt\Payments\Processor;
use Bolt\Extension\Bolt\Payments\Storage\Schema\Table;
use Pimple as Container;
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
                    $app['storage'],
                    $app['payments.form']
                );
            }
        );

        $app['payments.form.components'] = $app->share(
            function ($app) {
                $type = new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'credit_card' => $app->share(function () use ($app) { return new Form\Type\CreditCardPayment($app['payments.config']); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
                $entity = new Container(
                    [
                    ]
                );
                $constraint = new Container(
                    [
                    ]
                );

                return new Container([
                    'type'       => $type,
                    'entity'     => $entity,
                    'constraint' => $constraint,
                ]);
            }
        );

        $app['payments.form'] = $app->share(
            function ($app) {
                return new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'credit_card_payment' => $app->share(function () use ($app) { return new Form\CreditCardPaymentForm($app['form.factory'], $app['payments.form.components']['type']['credit_card']); }),
                        // @codingStandardsIgnoreEnd
                    ]
                );
            }
        );

        $app['payments.controller.frontend'] = $app->share(
            function ($app) {
                return new Frontend($app['payments.config']);
            }
        );

        $app['payments.schema.table'] = $app->share(
            function () use ($app) {
                /** @var \Doctrine\DBAL\Platforms\AbstractPlatform $platform */
                $platform = $app['db']->getDatabasePlatform();
                $prefix = $app['schema.prefix'];

                // @codingStandardsIgnoreStart
                return new Container([
                    'payment' => $app->share(function () use ($platform, $prefix) { return new Table\Payment($platform, $prefix); }),
                ]);
                // @codingStandardsIgnoreEnd
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
