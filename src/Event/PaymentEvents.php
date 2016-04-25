<?php

namespace Bolt\Extension\Bolt\Payments\Event;

/**
 * Payment event constants.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentEvents
{
    const PAYMENT_AUTHORIZE_INITIATE = 'payment.authorize.initiate';
    const PAYMENT_AUTHORIZE_SUCCESS = 'payment.authorize.success';
    const PAYMENT_AUTHORIZE_FAILURE = 'payment.authorize.failure';

    const PAYMENT_CAPTURE_INITIATE = 'payment.capture.initiate';
    const PAYMENT_CAPTURE_SUCCESS = 'payment.capture.success';
    const PAYMENT_CAPTURE_FAILURE = 'payment.capture.failure';

    const PAYMENT_CARD_INITIATE = 'payment.card.initiate';
    const PAYMENT_CARD_SUCCESS = 'payment.card.success';
    const PAYMENT_CARD_FAILURE = 'payment.card.failure';

    const PAYMENT_CREATE_INITIATE = 'payment.create.initiate';
    const PAYMENT_CREATE_SUCCESS = 'payment.create.success';
    const PAYMENT_CREATE_FAILURE = 'payment.create.failure';

    const PAYMENT_DELETE_INITIATE = 'payment.delete.initiate';
    const PAYMENT_DELETE_SUCCESS = 'payment.delete.success';
    const PAYMENT_DELETE_FAILURE = 'payment.delete.failure';

    const PAYMENT_PURCHASE_INITIATE = 'payment.purchase.initiate';
    const PAYMENT_PURCHASE_CANCELLED = 'payment.purchase.cancelled';
    const PAYMENT_PURCHASE_SUCCESS = 'payment.purchase.success';
    const PAYMENT_PURCHASE_FAILURE = 'payment.purchase.failure';

    const PAYMENT_UPDATE_INITIATE = 'payment.update.initiate';
    const PAYMENT_UPDATE_SUCCESS = 'payment.update.success';
    const PAYMENT_UPDATE_FAILURE = 'payment.update.failure';
    
    /**
     * Constructor.
     */
    private function __construct()
    {
    }
}
