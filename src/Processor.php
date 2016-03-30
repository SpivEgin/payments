<?php

namespace Bolt\Extension\Bolt\Payments;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Exception\GenericException;
use Bolt\Extension\Bolt\Payments\Exception\ProcessorException;
use Bolt\Storage\EntityManager;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\RuntimeException;
use Omnipay\Common\GatewayFactory;
use Omnipay\Common\Message\RedirectResponseInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Twig_Environment as TwigEnvironment;

/**
 * Transaction processing class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Processor
{
    /** @var Config */
    protected $config;
    /** @var TwigEnvironment */
    protected $twig;
    /** @var Session */
    protected $session;
    /** @var EntityManager */
    protected $em;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param TwigEnvironment $twig
     * @param Session         $session
     * @param EntityManager   $em
     */
    public function __construct(Config $config, TwigEnvironment $twig, Session $session, EntityManager $em)
    {
        $this->config = $config;
        $this->twig = $twig;
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * Get a configured gateway object.
     *
     * @param $name
     *
     * @return CombinedGatewayInterface
     */
    private function getGateway($name)
    {
        $providerConfig = $this->config->getProviders()->get($name);
        $name = Helper::resolveGateway($name);
        $gateway = (new GatewayFactory())
            ->create($name)
            ->initialize($providerConfig)
        ;

        return $gateway;
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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));
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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $request->request->get('gateway'));

        // save gateway settings in session
        $this->session->set($sessionVar, $gateway->getParameters());

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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $params = $this->session->get($sessionVar . '.authorize', []);
        $params['returnUrl'] = str_replace('/authorize', '/completeAuthorize', $request->getUri());
        $params['cancelUrl'] = $request->getUri();
        $card = new CreditCard($this->session->get($sessionVar . '.card'));
        $context = [
            'method'  => 'authorize',
            'params'  => $params,
            'card'    => $card->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Request POST handler to authorize an amount on the customer's card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function setAuthorize(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsAuthorize()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "authorize".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load POST data
        $params = $request->request->get('params');
        $card = $request->request->get('card');

        // save POST data into session
        $this->session->set($sessionVar . '.authorize', $params);
        $this->session->set($sessionVar . '.card', $card);

        $params['card'] = $card;
        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->authorize($params)->send();
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
     * @return string
     */
    public function completeAuthorize($name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsCompleteAuthorize()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "completeAuthorize".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $params = $this->session->get($sessionVar . '.authorize');

        try {
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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $context = [
            'method'  => 'capture',
            'params'  => $this->session->get($sessionVar . '.capture', []),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Request POST handler to capture an amount you have previously authorized.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function setCapture(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsCapture()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "capture".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load POST data
        $params = $request->request->get('params');

        // save POST data into session
        $this->session->set($sessionVar . '.capture', $params);

        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->capture($params)->send();
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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $params = $this->session->get($sessionVar . '.purchase', []);
        $params['returnUrl'] = str_replace('/purchase', '/completePurchase', $request->getUri());
        $params['cancelUrl'] = $request->getUri();
        $card = new CreditCard($this->session->get($sessionVar . '.card'));
        $context = [
            'method'  => 'purchase',
            'params'  => $params,
            'card'    => $card->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Request POST handler to authorize and immediately capture an amount on the customer's card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function setPurchase(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsPurchase()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "purchase".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load POST data
        $params = $request->request->get('params');
        $card = $request->request->get('card');

        // save POST data into session
        $this->session->set($sessionVar . '.purchase', $params);
        $this->session->set($sessionVar . '.card', $card);

        $params['card'] = $card;
        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->purchase($params)->send();
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
     * Handle return from off-site gateways after purchase.
     *
     * NOTE: This won't work for gateways which require an internet-accessible URL (yet)
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function completePurchase(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsCompletePurchase()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "completePurchase".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load request data from session
        $params = $this->session->get($sessionVar . '.purchase', []);
        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->completePurchase($params)->send();
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
     * Create gateway create Credit Card.
     *
     * @param string $name
     *
     * @return string
     */
    public function getCreateCard($name)
    {
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $card = new CreditCard($this->session->get($sessionVar . '.card'));
        $context = [
            'method'  => 'createCard',
            'params'  => $this->session->get($sessionVar . '.create', []),
            'card'    => $card->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Submit gateway create Credit Card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function setCreateCard(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsCreateCard()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "createCard".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load POST data
        $params = $request->request->get('params');
        $card = $request->request->get('card');

        // save POST data into session
        $this->session->set($sessionVar . '.create', $params);
        $this->session->set($sessionVar . '.card', $card);

        $params['card'] = $card;
        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->createCard($params)->send();
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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $card = new CreditCard($this->session->get($sessionVar . '.card'));
        $context = [
            'method'  => 'updateCard',
            'params'  => $this->session->get($sessionVar . '.update', []),
            'card'    => $card->getParameters(),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Submit gateway update Credit Card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function setUpdateCard(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsUpdateCard()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "updateCard".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load POST data
        $params = $request->request->get('params');
        $card = $request->request->get('card');

        // save POST data into session
        $this->session->set($sessionVar . '.update', $params);
        $this->session->set($sessionVar . '.card', $card);

        $params['card'] = $card;
        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->updateCard($params)->send();
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
        $gateway = $this->getGateway($name);
        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        $context = [
            'method'  => 'deleteCard',
            'params'  => $this->session->get($sessionVar . '.delete', []),
        ];

        return $this->render($gateway, 'request.twig', $context);
    }

    /**
     * Submit gateway delete Credit Card.
     *
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public function setDeleteCard(Request $request, $name)
    {
        $gateway = $this->getGateway($name);
        if (!$gateway->supportsDeleteCard()) {
            throw new RuntimeException(sprintf('Gateway %s does not support "deleteCard".', $name));
        }

        $sessionVar = 'omnipay.' . $gateway->getShortName();
        $gateway->initialize((array) $this->session->get($sessionVar));

        // load POST data
        $params = $request->request->get('params');

        // save POST data into session
        $this->session->set($sessionVar . '.delete', $params);

        $params['clientIp'] = $request->getClientIp();

        try {
            $response = $gateway->deleteCard($params)->send();
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
            'baseurl'  => $this->config->getMountpoint(),
            'gateway'  => $gateway,
            'settings' => $gateway->getParameters(),
        ];

        return $this->twig->render($template, $context);
    }
}
