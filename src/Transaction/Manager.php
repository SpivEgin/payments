<?php

namespace Bolt\Extension\Bolt\Payments\Transaction;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Storage;
use Closure;

/**
 * Transaction Manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Manager
{
    /** @var Config */
    protected $config;
    /** @var Closure */
    protected $idGenerator;

    /**
     * Constructor.
     *
     * @param Config  $config
     * @param Closure $idGenerator
     */
    public function __construct(Config $config, Closure $idGenerator)
    {
        $this->config = $config;
        $this->idGenerator = $idGenerator;
    }

    /**
     * Create a new transaction.
     *
     * @param array $params
     *
     * @return Transaction
     */
    public function createTransaction(array $params = [])
    {
        if (!isset($params['transactionId'])) {
            $generator = $this->idGenerator;
            $id = $generator();
            $params['transactionId'] = $id;
        }
        $transaction = new Transaction($params);

        return $transaction;
    }
}
