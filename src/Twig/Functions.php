<?php

namespace Bolt\Extension\Bolt\Payments\Twig;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\GatewayManager;
use Bolt\Extension\Bolt\Payments\Transaction;
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

    /**
     * Constructor.
     *
     * @param Config           $session
     * @param SessionInterface $session
     */
    public function __construct(Config $config, SessionInterface $session)
    {
        $this->config = $config;
        $this->session = $session;
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
            new TwigSimpleFunction('create_transaction', [$this, 'createTransaction'], $safe),
        ];
    }

    /**
     * Create a transaction object and save to the session.
     *
     * <pre>
     *  {{ create_transaction('mollie', 'purchase', {amount: 1972.09, currency: 'EUR', description: 'Gumleaves' }) }}
     * </pre>
     *
     * @param string $gatewayName
     * @param string $transactionType
     * @param array  $params
     */
    public function createTransaction($gatewayName, $transactionType, array $params = [])
    {
        $transaction = new Transaction($params);

        $gatewayManager = new GatewayManager($this->config, $this->session);
        $gatewayManager->initializeSessionGateway($gatewayName);
        $gatewayManager->setSessionValue($gatewayName, $transactionType, $transaction);
    }
}
