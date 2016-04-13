<?php

namespace Bolt\Extension\Bolt\ShoppingCart;

/**
 * Shopping Cart interface.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
interface ShoppingCartInterface
{
    const SESSION_KEY = 'shopping.cart';

    /**
     * Return the shopping cart's unique ID.
     *
     * @return string
     */
    public function getCartId();

    /**
     * Return the transaciton ID assigned to this cart.
     *
     * @return string
     */
    public function getTransactionId();

    /**
     * Set the transaciton ID for this cart.
     *
     * @param string $transactionId
     *
     * @return ShoppingCartInterface
     */
    public function setTransactionId($transactionId);

    /**
     * Return the data associated with this cart.
     *
     * @return mixed
     */
    public function getData();

    /**
     * Set the data associated with this cart.
     *
     * @param CartDataInterface $cartData
     *
     * @return ShoppingCartInterface
     */
    public function setData(CartDataInterface $cartData);
}
