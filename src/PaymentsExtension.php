<?php

namespace Bolt\Extension\Bolt\Payments;

use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\SimpleExtension;

/**
 * Payments extension loader class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentsExtension extends SimpleExtension
{
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
