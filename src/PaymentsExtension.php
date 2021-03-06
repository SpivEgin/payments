<?php

namespace Bolt\Extension\Bolt\Payments;

use Bolt\Events\ControllerEvents;
use Bolt\Extension\AbstractExtension;
use Bolt\Extension\Bolt\Payments\Controller\Frontend;
use Bolt\Extension\Bolt\Payments\Provider\PaymentsServiceProvider;
use Bolt\Extension\ConfigTrait;
use Bolt\Extension\ControllerMountTrait;
use Bolt\Extension\DatabaseSchemaTrait;
use Bolt\Extension\StorageTrait;
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
    use StorageTrait;
    use TwigTrait;

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $this->extendDatabaseSchemaServices();
        $this->extendRepositoryMapping();
        $this->extendTwigService();

        // Add the Twig Extension.
        $app['twig'] = $app->share(
            $app->extend(
                'twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(
                        new Twig\Functions($app['payments.config'], $app['storage'])
                    );

                    return $twig;
                }
            )
        );

        $app['safe_twig'] = $app->share(
            $app->extend(
                'safe_twig',
                function (\Twig_Environment $twig, $app) {
                    $twig->addExtension(
                        new Twig\Functions($app['payments.config'], $app['storage'])
                    );

                    return $twig;
                }
            )
        );
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
        return [
            'payment'       => Storage\Schema\Table\Payment::class,
            'payment_audit' => Storage\Schema\Table\PaymentAudit::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerRepositoryMappings()
    {
        return [
            'payment'       => [Storage\Entity\Payment::class => Storage\Repository\Payment::class],
            'payment_audit' => [Storage\Entity\PaymentAudit::class => Storage\Repository\PaymentAudit::class],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'mountpoint' => 'payments',
            'forms'      => [
                'address' => [
                    'first_name' => [
                        'label'       => 'First Name',
                        'placeholder' => 'First Name',
                        'required'    => true,
                    ],
                    'last_name' => [
                        'label'       => 'Last Name',
                        'placeholder' => 'Last Name',
                        'required'    => true,
                    ],
                    'address_1' => [
                        'label'       => 'Street Address',
                        'placeholder' => 'Street Address',
                        'required'    => true,
                    ],
                    'address_2' => [
                        'label'       => '',
                        'placeholder' => '',
                        'required'    => false,
                    ],
                    'address_city' => [
                        'label'       => 'City',
                        'placeholder' => 'City',
                        'required'    => true,
                    ],
                    'address_postcode' => [
                        'label'       => 'Postcode',
                        'placeholder' => 'Postcode',
                        'required'    => true,
                    ],
                    'address_state' => [
                        'label'       => 'State or Province',
                        'placeholder' => 'State or Province',
                        'required'    => true,
                    ],
                    'address_country' => [
                        'label'       => 'Country',
                        'placeholder' => 'Country',
                        'required'    => false,
                    ],
                    'phone' => [
                        'label'       => 'Phone Number',
                        'placeholder' => 'Phone Number',
                        'required'    => false,
                    ],
                    'email' => [
                        'label'       => 'Email Address',
                        'placeholder' => 'Email Address',
                        'required'    => false,
                    ],
                ],
                'credit_card' => [
                    'first_name' => [
                        'label'       => 'First Name',
                        'placeholder' => 'First Name',
                        'required'    => true,
                    ],
                    'last_name' => [
                        'label'       => 'Last Name',
                        'placeholder' => 'Last Name',
                        'required'    => true,
                    ],
                    'expiry_date' => [
                        'label'       => 'Expiry Date',
                        'placeholder' => 'Expiry Date',
                        'required'    => true, false,
                    ],
                    'start_date' => [
                        'label'       => 'Start Date',
                        'placeholder' => 'Start Date',
                        'required'    => false,
                    ],
                    'number' => [
                        'label'       => 'Card Number',
                        'placeholder' => 'Card Number',
                        'required'    => true,
                    ],
                    'ccv' => [
                        'label'       => 'Card Verification Value',
                        'placeholder' => 'Card Verification Value',
                        'required'    => true,
                    ],
                    'issue_number' => [
                        'label'       => 'Issue Number',
                        'placeholder' => 'Issue Number',
                        'required'    => false,
                    ],
                ],
            ],
            'templates'  => [
                'parent'   => 'layout.twig',
                'pages'    => [
                    'payment'  => 'payment.twig',
                    'address'  => 'address.twig',
                    'complete' => 'complete.twig',
                ],
                'button' => [
                    'payment' => '_button_payment.twig',
                ],
            ],
        ];
    }
}
