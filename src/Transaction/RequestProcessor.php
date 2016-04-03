<?php

namespace Bolt\Extension\Bolt\Payments\Transaction;

use Bolt\Extension\Bolt\Payments\CombinedGatewayInterface;
use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Exception\GenericException;
use Bolt\Extension\Bolt\Payments\Exception\ProcessorException;
use Bolt\Extension\Bolt\Payments\GatewayManager;
use Bolt\Extension\Bolt\Payments\Storage;
use Bolt\Extension\Bolt\Payments\Storage\Records;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Environment as TwigEnvironment;

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
    /** @var TwigEnvironment */
    protected $twig;
    /** @var Session */
    protected $session;
    /** @var string */
    protected $baseUrl;
    /** @var GatewayManager */
    protected $gatewayManager;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param Records         $records
     * @param TwigEnvironment $twig
     * @param Session         $session
     * @param string          $baseUrl
     */
    public function __construct(Config $config, Records $records, TwigEnvironment $twig, Session $session, $baseUrl)
    {
        $this->config = $config;
        $this->records = $records;
        $this->twig = $twig;
        $this->session = $session;
        $this->baseUrl = $baseUrl;

        $this->gatewayManager = new GatewayManager($config, $session);
    }

    /**
     * Return gateway settings.
     *
     * @param string $name
     *
     * @return string
     */
    public function getSettings($name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        $context = [
            'settings' => $gateway->getParameters(),
        ];

        return $this->render($gateway, 'gateway.twig', $context);
    }

    /**
     * Save gateway settings
     *
     * @param Request $request
     * @param string  $name
     *
     * @return RedirectResponse
     */
    public function setSettings(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeRequestGateway($name, $request);

        // save gateway settings in session
        $this->gatewayManager->setSessionValue($name, $name, $gateway->getParameters());

        // redirect back to gateway settings page
        $this->session->getFlashBag()->add('success', 'Gateway settings updated!');
    }

    /**
     * Request GET handler to authorize an amount on the customer's card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function getAuthorize(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);

        $cardData = $this->gatewayManager->getSessionValue($name, static::TYPE_CARD);
        $card = new CreditCard($cardData);

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_AUTHORIZE, new Transaction());
        $transaction
            ->setReturnUrl($this->getInternalUrl($name, 'completeAuthorize'))
            ->setCancelUrl($request->getUri())
            ->setCard($card)
        ;

        $context = [
            'method'  => 'authorize',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Request POST handler to authorize an amount on the customer's card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function setAuthorize(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
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

        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Handle return from off-site gateways after authorization
     *
     * @param string $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function completeAuthorize($name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
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

        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Request GET handler to capture an amount you have previously authorized.
     *
     * @param string $name
     *
     * @return string
     */
    public function getCapture($name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);

        $context = [
            'method'  => 'capture',
            'params'  => $this->gatewayManager->getSessionValue($name, static::TYPE_CAPTURE, new Transaction()),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Request POST handler to capture an amount you have previously authorized.
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function setCapture(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
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

        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Request GET handler to authorize and immediately capture an amount on the customer's card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function getPurchase(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);

        $card = new CreditCard($this->gatewayManager->getSessionValue($name, static::TYPE_CARD));
        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_PURCHASE, new Transaction());
        $transaction
            ->setReturnUrl($this->getInternalUrl($name, 'completePurchase'))
            ->setCancelUrl($request->getUri())
            ->setCard($card)
        ;
        $this->gatewayManager->setSessionValue($name, static::TYPE_PURCHASE, $transaction);

        $context = [
            'method'  => 'purchase',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Request POST handler to authorize and immediately capture an amount on the customer's card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function setPurchase(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if (!$gateway->supportsPurchase()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "purchase".', $name));
        }

        // load POST data
        $card = new CreditCard($request->request->get('card'));

        $params = $request->request->get('params');

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_PURCHASE);
        if ($transaction === null) {
            throw new GenericException('Sorry, there was an error. Please try again later.');
        }
        $transaction
            ->setCard($card)
            ->setClientIp($request->getClientIp())
        ;

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

        if ($response->isSuccessful()) {
            $this->records->createPaymentAuditEntry($transaction, $response, 'set purchase: success');
        } elseif ($response->isRedirect()) {
            $this->records->createPaymentAuditEntry($transaction, $response, 'set purchase: redirect');
            $this->session->save();
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Handle return from off-site gateways after purchase.
     *
     * NOTE: This won't work for gateways which require an internet-accessible URL (yet)
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function completePurchase(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if (!$gateway->supportsCompletePurchase()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "completePurchase".', $name));
        }

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_PURCHASE, new Transaction());
        $transaction->setClientIp($request->getClientIp());

        $params = $this->getGatewayParameters($name, $transaction);
        try {
            /** @var ResponseInterface $response */
            $response = $gateway->completePurchase((array) $params)->send();
        } catch (\Exception $e) {
            throw new GenericException('Sorry, there was an error. Please try again later.', $e->getCode(), $e);
        }

        if ($response->isSuccessful()) {
            $this->records->createPaymentAuditEntry($transaction, $response, 'complete purchase: success');
        } elseif ($response->isRedirect()) {
            $this->records->createPaymentAuditEntry($transaction, $response, 'complete purchase: redirect');
            $this->session->save();
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Create gateway create Credit Card.
     *
     * @param string $name
     *
     * @return string
     */
    public function getCreateCard($name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);

        $card = new CreditCard($this->gatewayManager->getSessionValue($name, static::TYPE_CARD));
        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_CREATE, new Transaction());
        $transaction->setCard($card);

        $context = [
            'method'  => 'createCard',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Submit gateway create Credit Card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function setCreateCard(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
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

        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Create gateway update Credit Card.
     *
     * @param string $name
     *
     * @return string
     */
    public function getUpdateCard($name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);

        $card = new CreditCard($this->gatewayManager->getSessionValue($name, static::TYPE_CARD));
        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_UPDATE, new Transaction());
        $transaction->setCard($card);

        $context = [
            'method'  => 'updateCard',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Submit gateway update Credit Card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function setUpdateCard(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
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

        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Create gateway delete Credit Card.
     *
     * @param string $name
     *
     * @return string
     */
    public function getDeleteCard($name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, static::TYPE_DELETE, new Transaction());

        $context = [
            'method'  => 'deleteCard',
            'params'  => $transaction,
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Submit gateway delete Credit Card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @throws GenericException
     *
     * @return string
     */
    public function setDeleteCard(Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
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

        } elseif ($response->isRedirect()) {
            /** @var RedirectResponseInterface $response */
            $response->redirect();
        } else {
            throw new ProcessorException($response->getMessage());
        }

        $context = [
            'response' => $response,
        ];

        return $this->render($gateway, 'response.twig', $context);
    }

    /**
     * Render an transaction specific template.
     *
     * @param CombinedGatewayInterface $gateway
     * @param string                   $template
     * @param array                    $context
     *
     * @return string
     */
    protected function render($gateway, $template, array $context = [])
    {
        $context += [
            'baseurl'  => $this->baseUrl,
            'gateway'  => $gateway,
            'settings' => $gateway->getParameters(),
        ];

        return $this->twig->render($template, $context);
    }

    /**
     * Construct an internal payments URL.
     *
     * @param string $provider
     * @param string $routeName
     *
     * @return string
     */
    protected function getInternalUrl($provider, $routeName)
    {
        return sprintf('%s/gateways/%s/%s', $this->baseUrl, $provider, $routeName);
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
