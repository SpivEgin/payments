<?php

namespace Bolt\Extension\Bolt\Payments\Provider;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\Transaction\RequestProcessor;
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

                return new RequestProcessor(
                    $app['payments.config'],
                    $app['payments.records'],
                    $app['twig'],
                    $app['session'],
                    $baseUrl
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
                /** @var Storage\Repository\Payment $payment */
                $payment = $app['storage']->getRepository(Storage\Entity\Payment::class);
                /** @var Storage\Repository\PaymentAuditEntry $paymentAuditEntry */
                $paymentAuditEntry = $app['storage']->getRepository(Storage\Entity\PaymentAuditEntry::class);

                return new Storage\Records($payment, $paymentAuditEntry);
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
