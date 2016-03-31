<?php

namespace Bolt\Extension\Bolt\Payments\Provider;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\Form;
use Bolt\Extension\Bolt\Payments\TransactionProcessor;
use Bolt\Extension\Bolt\Payments\Storage;
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
                $baseUrl = sprintf(
                    '%s%s',
                    $app['resources']->getUrl('rooturl'),
                    $this->config['mountpoint']
                );

                return new TransactionProcessor(
                    $app['payments.config'],
                    $app['payments.records'],
                    $app['twig'],
                    $app['session'],
                    $baseUrl
                );
            }
        );

        $app['payments.form.components'] = $app->share(
            function ($app) {
                $type = new Container(
                    [
                        // @codingStandardsIgnoreStart
                        'address'     => $app->share(function () use ($app) { return new Form\Type\AddressType($app['payments.config']); }),
                        'credit_card' => $app->share(function () use ($app) { return new Form\Type\CreditCardType($app['payments.config']); }),
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
                        'credit_card'      => $app->share(
                            function () use ($app) {
                                return new Form\CreditCardForm(
                                    $app['form.factory'],
                                    $app['payments.form.components']['type']['credit_card']
                                );
                            }
                        ),
                        'address'  => $app->share(
                            function () use ($app) {
                                return new Form\AddressForm(
                                    $app['form.factory'],
                                    $app['payments.form.components']['type']['address']
                                );
                            }
                        ),
                    ]
                );
            }
        );

        $app['payments.controller.frontend'] = $app->share(
            function ($app) {
                return new Frontend($app['payments.config']);
            }
        );

        $app['payments.records'] = $app->share(
            function ($app) {
                /** @var Storage\Repository\Payment $repo */
                $repo = $app['storage']->getRepository(Storage\Entity\Payment::class);

                return new Storage\Records($repo);
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
