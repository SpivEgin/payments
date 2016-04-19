<?php

namespace Bolt\Extension\Bolt\Payments\Twig;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Storage;
use Bolt\Extension\Bolt\Payments\Transaction;
use Bolt\Storage\EntityManager;
use Twig_Environment as TwigEnvironment;
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
    /** @var EntityManager */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @param Config        $config
     * @param EntityManager $entityManager
     */
    public function __construct(Config $config, EntityManager $entityManager)
    {
        $this->config = $config;
        $this->entityManager = $entityManager;
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
            new TwigSimpleFunction('payment', [$this, 'getPayment']),
        ];
    }

    /**
     * Generate a button to start payments.
     *
     * @param string $transactionId
     *
     * @return Storage\Entity\Payment|false
     */
    public function getPayment($transactionId)
    {
        /** @var Storage\Repository\Payment $repo */
        $repo = $this->entityManager->getRepository(Storage\Entity\Payment::class);
        $transactionEntity = $repo->getPaymentByTransactionId($transactionId);

        return $transactionEntity;
    }
}
