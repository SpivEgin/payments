<?php

namespace Bolt\Extension\Bolt\Payments\Transaction;

use Bolt\Extension\Bolt\Members\AccessControl\Authorisation;
use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Event\PaymentEvent;
use Bolt\Extension\Bolt\Payments\Event\PaymentEvents;
use Bolt\Extension\Bolt\Payments\Exception\GenericException;
use Bolt\Extension\Bolt\Payments\Exception\ProcessorException;
use Bolt\Extension\Bolt\Payments\Gateway\CombinedGatewayInterface;
use Bolt\Extension\Bolt\Payments\Gateway\Manager as GatewayManager;
use Bolt\Extension\Bolt\Payments\Storage\Records;
use Bolt\Extension\Bolt\ShoppingCart\Event\CartEvent;
use Bolt\Extension\Bolt\ShoppingCart\Event\CartEvents;
use Bolt\Extension\Bolt\ShoppingCart\ShoppingCartInterface;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\Debug\TraceableEventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Transaction processing class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class RequestProcessor
{
    const TYPE_AUTHORIZE = 'authorize';
    const TYPE_CAPTURE = 'capture';
    const TYPE_CARD = 'card';
    const TYPE_CREATE = 'create';
    const TYPE_DELETE = 'delete';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_UPDATE = 'update';

    /** @var Config */
    protected $config;
    /** @var Records */
    protected $records;
    /** @var Session */
    protected $session;
    /** @var string */
    protected $baseUrl;
    /** @var GatewayManager */
    protected $gatewayManager;
    /** @var Manager */
    private $transManager;
    /** @var TraceableEventDispatcher */
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param Records                  $records
     * @param Manager                  $transManager
     * @param GatewayManager           $gatewayManager
     * @param Session                  $session
     * @param TraceableEventDispatcher $dispatcher
     */
    public function __construct(
        Config $config,
        Records $records,
        Manager $transManager,
        GatewayManager $gatewayManager,
        Session $session,
        TraceableEventDispatcher $dispatcher
    ) {
        $this->config = $config;
        $this->records = $records;
        $this->transManager = $transManager;
        $this->gatewayManager = $gatewayManager;
        $this->session = $session;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Return gateway settings.
     *
     * @param CombinedGatewayInterface $gateway
     *
     * @return CombinedGatewayInterface
     */
    public function getSettings(CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();

        return $gateway;
    }

    /**
     * Save gateway settings
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     */
    public function setSettings(Request $request, $gateway)
    {
        $name = $gateway->getName();

        // save gateway settings in session
        $this->gatewayManager->setSessionValue($name, $name, $gateway->getParameters());

        // redirect back to gateway settings page
        $this->session->getFlashBag()->add('success', 'Gateway settings updated!');
    }

    /**
     * Request GET handler to authorize an amount on the customer's card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     *
     * @return Transaction
     */
    public function getAuthorize(Request $request, CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        $cardData = $this->gatewayManager->getSessionValue($name, static::TYPE_CARD);
        $card = new CreditCard($cardData);

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_AUTHORIZE, $this->transManager->createTransaction());
        $transaction
            ->setReturnUrl($this->config->getTransactionUrl($name, 'completeAuthorize'))
            ->setCancelUrl($request->getUri())
            ->setCard($card)
        ;

        return $transaction;
    }

    /**
     * Request POST handler to authorize an amount on the customer's card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function setAuthorize(Request $request, $gateway)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsAuthorize()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "authorize".', $name));
        }

        // load POST data
        $card = new CreditCard($request->request->get('card'));

        $params = $request->request->get('params');
        $transaction = new Transaction($params);
        $transaction
            ->setClientIp($request->getClientIp())
            ->setCard($card)
        ;

        // save POST data into session
        $this->gatewayManager->setSessionValue($name, static::TYPE_AUTHORIZE, $transaction);
        $this->gatewayManager->setSessionValue($name, static::TYPE_CARD, $card);

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->authorize((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            return $response;
        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        }

        throw new ProcessorException($response->getMessage());
    }

    /**
     * Handle return from off-site gateways after authorization
     *
     * @param CombinedGatewayInterface $gateway
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function completeAuthorize(CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsCompleteAuthorize()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "completeAuthorize".', $name));
        }

        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_AUTHORIZE);
        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->completeAuthorize($params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            return $response;
        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        }

        throw new ProcessorException($response->getMessage());
    }

    /**
     * Request POST handler to capture an amount you have previously authorized.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function setCapture(Request $request, CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsCapture()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "capture".', $name));
        }

        // load POST data
        $params = $request->request->get('params');
        $transaction = new Transaction($params);
        $transaction->setClientIp($request->getClientIp());

        // save POST data into session
        $this->gatewayManager->setSessionValue($name, static::TYPE_CAPTURE, $transaction);

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->capture((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            return $response;
        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        }

        throw new ProcessorException($response->getMessage());
    }

    /**
     * Request GET handler to authorize and immediately capture an amount on the customer's card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     * @param ShoppingCartInterface    $cart
     *
     * @return Transaction
     */
    public function getPurchase(Request $request, CombinedGatewayInterface $gateway, ShoppingCartInterface $cart)
    {
        $name = $gateway->getName();
        $card = new CreditCard($this->gatewayManager->getSessionValue($name, static::TYPE_CARD));
        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_PURCHASE, $this->transManager->createTransaction());
        $transaction
            ->setCard($card)
            ->setCartId($cart->getId())
            ->setAmount($cart->getAmount())
            ->setCurrency($cart->getCurrency())
            ->setDescription($cart->getDescription())
        ;
        $cart->setTransactionId($transaction);
        $this->gatewayManager->setSessionValue($name, static::TYPE_PURCHASE, $transaction);

        return $transaction;
    }

    /**
     * Request POST handler to authorize and immediately capture an amount on the customer's card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     * @param Authorisation            $authorisation
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function setPurchase(Request $request, CombinedGatewayInterface $gateway, Authorisation $authorisation)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsPurchase()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "purchase".', $name));
        }

        // load POST data
        $card = new CreditCard($request->request->get('card'));

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_PURCHASE);
        if ($transaction === null) {
            throw new GenericException('Sorry, there was an error. Please try again later.');
        }
        $transaction
            ->setReturnUrl($this->config->getTransactionUrl($name, 'completePurchase'))
            ->setCancelUrl($request->getUri())
            ->setCard($card)
            ->setClientIp($request->getClientIp())
        ;

        // Get the shopping cart
        $sessionName = ShoppingCartInterface::SESSION_KEY_PREFIX .  $transaction->getCartId();

        /** @var ShoppingCartInterface $cart */
        $cart = $this->session->get($sessionName);
        if ($cart === null) {
            throw new ProcessorException('Cart required');
        }

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->purchase((array) $params)->send();
            $transaction->setTransactionReference($response->getTransactionReference());
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        // save POST data into session
        $this->gatewayManager->setSessionValue($name, static::TYPE_PURCHASE, $transaction);
        $this->gatewayManager->setSessionValue($name, static::TYPE_CARD, $card);

        $event = new PaymentEvent($transaction);
        if ($response->isSuccessful()) {
            $this->dispatcher->dispatch(PaymentEvents::PAYMENT_PURCHASE_SUCCESS, $event);
            $this->records->createPayment($authorisation, $gateway, $transaction);
            $this->records->createPaymentAudit($authorisation, $transaction, $response, 'set purchase: success');
        } elseif ($response->isRedirect()) {
            $this->dispatcher->dispatch(PaymentEvents::PAYMENT_PURCHASE_INITIATE, $event);
            $this->dispatcher->dispatch(CartEvents::CART_PAYMENT_START, new CartEvent($cart));
            $this->records->createPayment($authorisation, $gateway, $transaction);
            $this->records->createPaymentAudit($authorisation, $transaction, $response, 'set purchase: redirect');
            $this->session->save();

            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            $this->dispatcher->dispatch(PaymentEvents::PAYMENT_PURCHASE_FAILURE, $event);
            $this->dispatcher->dispatch(CartEvents::CART_PAYMENT_FAILURE, new CartEvent($cart));
            throw new ProcessorException($response->getMessage());
        }

        return $response;
    }

    /**
     * Handle return from off-site gateways after purchase.
     *
     * NOTE: This won't work for gateways which require an internet-accessible URL (yet)
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     * @param Authorisation            $authorisation
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function completePurchase(Request $request, CombinedGatewayInterface $gateway, Authorisation $authorisation)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsCompletePurchase()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "completePurchase".', $name));
        }

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_PURCHASE, $this->transManager->createTransaction());
        $transaction->setClientIp($request->getClientIp());

        // Get the shopping cart
        $sessionName = ShoppingCartInterface::SESSION_KEY_PREFIX .  $transaction->getCartId();

        /** @var ShoppingCartInterface $cart */
        $cart = $this->session->get($sessionName);
        if ($cart === null) {
            throw new ProcessorException('Cart required');
        }

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->completePurchase((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        $payment = $this->records->getCustomerPayment($authorisation->getGuid(), $gateway->getShortName(), $transaction->getTransactionId());

        $event = new PaymentEvent($transaction);
        if ($response->isSuccessful()) {
            $payment->setStatus('paid');
            $this->records->createPaymentAudit($authorisation, $transaction, $response, 'complete purchase: success');

            $this->dispatcher->dispatch(PaymentEvents::PAYMENT_PURCHASE_SUCCESS, $event);
            $this->dispatcher->dispatch(CartEvents::CART_FULFILL, new CartEvent($cart));
        } elseif ($response->isRedirect()) {
            $this->records->createPaymentAudit($authorisation, $transaction, $response, 'complete purchase: redirect');
            $this->session->save();
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } elseif (method_exists($response, 'isCancelled') && $response->isCancelled()) {
            $payment->setStatus('cancelled');
            $this->records->createPaymentAudit($authorisation, $transaction, $response, 'complete purchase: cancelled');

            $this->dispatcher->dispatch(PaymentEvents::PAYMENT_PURCHASE_CANCEL, $event);
            $this->dispatcher->dispatch(CartEvents::CART_PAYMENT_CANCEL, new CartEvent($cart));
        } else {
            $this->dispatcher->dispatch(PaymentEvents::PAYMENT_PURCHASE_FAILURE, $event);
            $this->dispatcher->dispatch(CartEvents::CART_PAYMENT_FAILURE, new CartEvent($cart));
            throw new ProcessorException($response->getMessage());
        }

        $this->records->savePayment($payment);

        // Clear the transaction from the session
        $this->gatewayManager->removeSessionValue($name, static::TYPE_PURCHASE);

        return $response;
    }

    /**
     * Create gateway create Credit Card.
     *
     * @param CombinedGatewayInterface $gateway
     *
     * @return Transaction
     */
    public function getCreateCard(CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        $card = new CreditCard($this->gatewayManager->getSessionValue($name, static::TYPE_CARD));
        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_CREATE, $this->transManager->createTransaction());
        $transaction->setCard($card);

        return $transaction;
    }

    /**
     * Submit gateway create Credit Card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function setCreateCard(Request $request, CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsCreateCard()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "createCard".', $name));
        }

        // load POST data
        $card = new CreditCard($request->request->get('card'));
        $params = $request->request->get('params');
        $transaction = new Transaction($params);
        $transaction
            ->setCard($card)
            ->setClientIp($request->getClientIp())
        ;

        // save POST data into session
        $this->gatewayManager->setSessionValue($name, static::TYPE_CREATE, $transaction);
        $this->gatewayManager->setSessionValue($name, static::TYPE_CARD, $card);

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->createCard((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            return $response;
        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        }

        throw new ProcessorException($response->getMessage());
    }

    /**
     * Create gateway update Credit Card.
     *
     * @param CombinedGatewayInterface $gateway
     *
     * @return Transaction
     */
    public function getUpdateCard(CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        $card = new CreditCard($this->gatewayManager->getSessionValue($name, static::TYPE_CARD));
        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_UPDATE, $this->transManager->createTransaction());
        $transaction->setCard($card);

        return $transaction;
    }

    /**
     * Submit gateway update Credit Card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function setUpdateCard(Request $request, CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsUpdateCard()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "updateCard".', $name));
        }

        // load POST data
        $card = new CreditCard($request->request->get('card'));

        $params = $request->request->get('params');
        $transaction = new Transaction($params);
        $transaction
            ->setCard($card)
            ->setClientIp($request->getClientIp())
        ;

        // save POST data into session
        $this->gatewayManager->setSessionValue($name, static::TYPE_UPDATE, $transaction);
        $this->gatewayManager->setSessionValue($name, static::TYPE_CARD, $card);

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->updateCard((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            return $response;
        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        }

        throw new ProcessorException($response->getMessage());
    }

    /**
     * Create gateway delete Credit Card.
     *
     * @param CombinedGatewayInterface $gateway
     *
     * @return Transaction
     */
    public function getDeleteCard(CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_DELETE, $this->transManager->createTransaction());

        return $transaction;
    }

    /**
     * Submit gateway delete Credit Card.
     *
     * @param Request                  $request
     * @param CombinedGatewayInterface $gateway
     *
     * @throws GenericException
     *
     * @return ResponseInterface
     */
    public function setDeleteCard(Request $request, CombinedGatewayInterface $gateway)
    {
        $name = $gateway->getName();
        if (!$gateway->supportsDeleteCard()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "deleteCard".', $name));
        }

        // load POST data
        $params = $request->request->get('params', []);
        $transaction = new Transaction($params);
        $transaction->setClientIp($request->getClientIp());

        // save POST data into session
        $this->gatewayManager->setSessionValue($name, static::TYPE_DELETE, $transaction);

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->deleteCard((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            return $response;
        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        }

        throw new ProcessorException($response->getMessage());
    }

    /**
     * Return a combined array of parameters to be passed to the gateway.
     *
     * @param string      $name
     * @param Transaction $transaction
     *
     * @return array
     */
    protected function getGatewayParameters($name, Transaction $transaction)
    {
        $params = (array) $this->config->getProviders()->get($name);
        $props = array_keys($transaction->getProperties());
        foreach ($props as $prop) {
            $params[$prop] = $transaction[$prop];
        }

        return $params;
    }
}
