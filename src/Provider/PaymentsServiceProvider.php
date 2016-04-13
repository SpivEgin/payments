<?php

namespace Bolt\Extension\Bolt\Payments\Provider;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\GatewayManager;
use Bolt\Extension\Bolt\Payments\Storage;
use Bolt\Extension\Bolt\Payments\Transaction;
use Ramsey\Uuid\Uuid;
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
            function ($app) {
                $baseUrl = sprintf(
                    '%s%s',
                    $app['resources']->getUrl('rooturl'),
                    $this->config['mountpoint']
                );

                return new Config($this->config, $baseUrl);
            }
        );

        $app['payments.processor'] = $app->share(
            function ($app) {
                return new Transaction\RequestProcessor(
                    $app['payments.config'],
                    $app['payments.records'],
                    $app['payments.transaction.manager'],
                    $app['payments.gateway.manager'],
                    $app['twig'],
                    $app['session'],
                    $app['dispatcher']
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
                /** @var Storage\Repository\PaymentAudit $paymentAudit */
                $paymentAudit = $app['storage']->getRepository(Storage\Entity\PaymentAudit::class);

                return new Storage\Records($payment, $paymentAudit);
            }
        );

        if (!isset($app['payments.transaction.id_generator'])) {
            $app['payments.transaction.id_generator'] = $app->protect(
                function () {
                    return Uuid::uuid4()->toString();
                }
            );
        }

        $app['payments.transaction.manager'] = $app->share(
            function ($app) {
                return new Transaction\Manager($app['payments.config'], $app['payments.transaction.id_generator']);
            }
        );

        $app['payments.gateway.manager'] = $app->share(
            function ($app) {
                return new GatewayManager($app['payments.config'], $app['session']);
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
