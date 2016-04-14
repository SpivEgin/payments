<?php

namespace Bolt\Extension\Bolt\ShoppingCart\Event;

/**
 * Cart event constants.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CartEvents
{
    const CART_CREATE = 'cart.create';
    const CART_FLUSH = 'cart.flush';
    const CART_FULFILL = 'cart.fulfill';

    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
