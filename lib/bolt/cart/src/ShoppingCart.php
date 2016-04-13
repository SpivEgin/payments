<?php

namespace Bolt\Extension\Bolt\ShoppingCart;

use Ramsey\Uuid\Uuid;

/**
 * Simple shopping cart implementation.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ShoppingCart implements ShoppingCartInterface
{
    /** @var string */
    protected $cartId;
    /** @var string */
    protected $transactionId;
    /** @var CartDataInterface */
    protected $cartData;

    /**
     * Constructor.
     *
     * @param mixed $cartData
     */
    public function __construct($cartData = null)
    {
        $this->cartId = Uuid::uuid4()->toString();
        $this->cartData = $cartData;
    }

    /**
     * @return string
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(CartDataInterface $cartData)
    {
        $this->cartData = $cartData;

        return $this;
    }
}
