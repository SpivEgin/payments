<?php

namespace Bolt\Extension\Bolt\Payments\Twig;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\GatewayManager;
use Bolt\Extension\Bolt\Payments\Transaction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig_Extension as TwigExtension;
use Twig_Markup as TwigMarkup;
use Twig_SimpleFunction as TwigSimpleFunction;

/**
 * Twig functions.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Functions extends TwigExtension
{
    /** @var Config */
    protected $config;
    /** @var SessionInterface */
    protected $session;
    /** @var RequestStack */
    private $requestStack;
    /** @var Transaction\Manager */
    private $transactionManager;

    /**
     * Constructor.
     *
     * @param Config                  $config
     * @param Config|SessionInterface $session
     * @param RequestStack            $requestStack
     * @param Transaction\Manager     $transactionManager
     */
    public function __construct(Config $config, SessionInterface $session, RequestStack $requestStack, Transaction\Manager $transactionManager)
    {
        $this->config = $config;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->transactionManager = $transactionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Payments';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html'], 'is_safe_callback' => true];
        $env  = ['needs_environment' => true];

        return [
            new TwigSimpleFunction('payment_transaction', [$this, 'createPaymentTransaction'], $safe),
        ];
    }

    /**
     * Create a transaction object and save to the session.
     *
     * <pre>
     *  {{ payment_transaction('mollie', 'purchase', {amount: 1972.09, currency: 'EUR', description: 'Gumleaves' }) }}
     * </pre>
     *
     * @param string $gatewayName
     * @param string $transactionType
     * @param array  $params
     */
    public function createPaymentTransaction($gatewayName, $transactionType, array $params = [])
    {
        $baseUrl = $this->requestStack->getCurrentRequest()->getUri();

        $transaction = $this->transactionManager->createTransaction($params);
        $transaction->setFinalUrl($baseUrl);

        $gatewayManager = new GatewayManager($this->config, $this->session);
        $gatewayManager->initializeSessionGateway($gatewayName);
        $gatewayManager->setSessionValue($gatewayName, $transactionType, $transaction);
    }
}
