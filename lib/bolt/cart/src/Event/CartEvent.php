<?php

namespace Bolt\Extension\Bolt\ShoppingCart\Event;

use Bolt\Extension\Bolt\ShoppingCart\ShoppingCartInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Cart event.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CartEvent extends Event
{
    /** @var ShoppingCartInterface */
    protected $cart;

    /**
     * Constructor.
     *
     * @param ShoppingCartInterface $cart
     */
    public function __construct(ShoppingCartInterface $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return ShoppingCartInterface
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param ShoppingCartInterface $cart
     */
    public function setCart(ShoppingCartInterface $cart)
    {
        $this->cart = $cart;
    }
}
