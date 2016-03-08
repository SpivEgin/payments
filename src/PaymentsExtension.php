<?php

namespace Bolt\Extension\Bolt\Payments;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\Provider\PaymentsServiceProvider;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\TwigTrait;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Payments extension loader class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentsExtension extends AbstractExtension implements ServiceProviderInterface, EventSubscriberInterface
{
    use ConfigTrait;
    use ControllerMountTrait;
    use DatabaseSchemaTrait;
    use TwigTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->extendTwigService();
        $this->extendDatabaseSchemaServices();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ControllerEvents::MOUNT => [
                ['onMountControllers', 0],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $this->container = $app;
        $this->container['dispatcher']->addSubscriber($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new PaymentsServiceProvider($this->getConfig()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFrontendControllers()
    {
        $app = $this->getContainer();
        $config = $this->getConfig();
        $mountPoint = (string) $config['mountpoint'];

        return [
            $mountPoint => new Frontend($app['payments.config']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerExtensionTables()
    {
        $app = $this->getContainer();

        return [
            'payment' => $app['payments.schema.table']['payment'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'mountpoint' => 'payments',
            'templates'  => [
                'parent'   => 'layout.twig',
                'gateway'  => 'gateway.twig',
                'request'  => 'request.twig',
                'response' => 'layout.twig',
            ],
        ];
    }
}
