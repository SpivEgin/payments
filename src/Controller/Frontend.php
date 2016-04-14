<?php

namespace Bolt\Extension\Bolt\Payments\Controller;

use Bolt\Extension\Bolt\Members\AccessControl\Session as MembersSession;
use Bolt\Extension\Bolt\Payments\CombinedGatewayInterface;
use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Payments\Gateway\Manager as GatewayManager;
use Bolt\Extension\Bolt\Payments\Transaction\Manager as TransactionManager;;
use Bolt\Extension\Bolt\Payments\Transaction\RequestProcessor;
use Bolt\Extension\Bolt\Payments\Transaction\Transaction;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment as TwigEnvironment;

/**
 * Frontend controller.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Frontend implements ControllerProviderInterface
{
    /** @var Config */
    protected $config;
    /** @var TwigEnvironment */
    protected $twig;
    /** @var GatewayManager */
    protected $gatewayManager;
    /** @var TransactionManager */
    protected $transManager;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $this->twig = $app['twig'];
        $this->gatewayManager = $app['payments.gateway.manager'];
        $this->transManager = $app['payments.transaction.manager'];

        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];

        // Gateway settings
        $ctr->match('/gateways/{name}', [$this, 'settings'])
            ->method('GET|POST')
            ->bind('payments-settings')
        ;

        // Authorize an amount on the customer's card
        $ctr->match('/gateways/{name}/authorize', [$this, 'authorize'])
            ->method('GET|POST')
            ->bind('payments-authorize')
        ;

        // Handle return from off-site gateways after authorization
        $ctr->get('/gateways/{name}/completeAuthorize', [$this, 'completeAuthorize'])
            ->bind('payments-completeAuthorize')
        ;

        // Capture an amount you have previously authorized
        $ctr->match('/gateways/{name}/capture', [$this, 'capture'])
            ->method('GET|POST')
            ->bind('payments-capture')
        ;

        // Authorize and immediately capture an amount on the customer's card
        $ctr->match('/gateways/{name}/purchase', [$this, 'purchase'])
            ->method('GET|POST')
            ->bind('payments-purchase')
        ;

        // Handle return from off-site gateways after purchase
        $ctr->match('/gateways/{name}/completePurchase', [$this, 'completePurchase'])
            ->method('GET|POST')
            ->bind('payments-purchase-complete')
        ;

        // Create/submit Credit Card creation form
        $ctr->match('/gateways/{name}/create-card', [$this, 'createCard'])
            ->method('GET|POST')
            ->bind('payments-create-card')
        ;

        // Create/submit gateway update Credit Card
        $ctr->match('/gateways/{name}/update-card', [$this, 'updateCard'])
            ->method('GET|POST')
            ->bind('payments-update-card')
        ;

        // Create/submit gateway delete Credit Card
        $ctr->match('/gateways/{name}/delete-card', [$this, 'deleteCard'])
            ->method('GET|POST')
            ->bind('payments-delete-card')
        ;

        $ctr->after([$this, 'before']);

        return $ctr;
    }

    /**
     * Before middleware route.
     *
     * @param Request     $request
     * @param Response    $response
     * @param Application $app
     *
     * @return null|RedirectResponse
     */
    public function before(Request $request, Response $response, Application $app)
    {
        if (!isset($app['members.session']) || !$app['members.session'] instanceof MembersSession) {
            return new Response('Payments controller requires bolt/members to be installed and configured.', Response::HTTP_FAILED_DEPENDENCY);
        }

        if ($app['members.session']->hasAuthorisation()) {
            return null;
        }

        return new RedirectResponse($app['url_generator.lazy']->generate('authenticationLogin'));
    }

    /**
     * Get/save gateway settings.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response|RedirectResponse
     */
    public function settings(Application $app, Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $app['payments.processor']->setSettings($request, $gateway);
            $target = $request->getBaseUrl() . $request->getPathInfo();

            return new RedirectResponse($target);
        }

        $gateway = $app['payments.processor']->getSettings($gateway);
        $context = [
            'settings' => $gateway->getParameters(),
        ];
        $html = $this->render($gateway, 'gateway.twig', $context);

        return new Response($html);
    }

    /**
     * Authorize an amount on the customer's card
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function authorize(Application $app, Request $request, $name)
    {
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $response = $app['payments.processor']->setAuthorize($request, $gateway);
            $template = $this->config->getTemplate('pages', 'payment');
        } else {
            $response = $app['payments.processor']->getAuthorize($request, $gateway);
            $template = $this->config->getTemplate('pages', 'complete');
        }

        /** @var Transaction $transaction */
        $transaction = $this->gatewayManager->getSessionValue($name, RequestProcessor::TYPE_AUTHORIZE);
        $context = [
            'method'   => 'authorize',
            'params'   => $transaction,
            'card'     => $transaction->getCard()->getParameters(),
            'response' => $response,
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Handle return from off-site gateways after authorization
     *
     * @param Application $app\
     * @param string      $name
     *
     * @return Response
     */
    public function completeAuthorize(Application $app, $name)
    {
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        $response = $app['payments.processor']->completeAuthorize($gateway);
        $template = $this->config->getTemplate('pages', 'complete');
        $context = [
            'response' => $response,
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Capture an amount you have previously authorized.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function capture(Application $app, Request $request, $name)
    {
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $response = $app['payments.processor']->setCapture($request, $gateway);
            $template = $this->config->getTemplate('pages', 'complete');
            $context = [
                'response' => $response,
            ];

            $html = $this->render($gateway, $template, $context);

            return new Response($html);
        }

        $template = $this->config->getTemplate('pages', 'payment');
        $context = [
            'method'  => 'capture',
            'params'  => $this->gatewayManager->getSessionValue($name, RequestProcessor::TYPE_CAPTURE, $this->transManager->createTransaction()),
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Authorize and immediately capture an amount on the customer's card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function purchase(Application $app, Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $authorisation = $app['members.session']->getAuthorisation();
            $response = $app['payments.processor']->setPurchase($request, $gateway, $authorisation);
            $template = $this->config->getTemplate('pages', 'complete');
            $context = [
                'response' => $response,
            ];
            $html = $this->render($gateway, $template, $context);

            return new Response($html);
        }

        $transaction = $app['payments.processor']->getPurchase($request, $gateway);
        $template = $this->config->getTemplate('pages', 'payment');
        $context = [
            'method'  => 'purchase',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Handle return from off-site gateways after purchase.
     *
     * NOTE: This won't work for gateways which require an internet-accessible URL (yet)
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function completePurchase(Application $app, Request $request, $name)
    {
        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        $authorisation = $app['members.session']->getAuthorisation();
        $response = $app['payments.processor']->completePurchase($request, $gateway, $authorisation);
        $template = $this->config->getTemplate('pages', 'complete');
        $context = [
            'response' => $response,
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Create/submit gateway create Credit Card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function createCard(Application $app, Request $request, $name)
    {
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $response = $app['payments.processor']->setCreateCard($request, $gateway);
            $template = $this->config->getTemplate('pages', 'complete');
            $context = [
                'response' => $response,
            ];

            $html = $this->render($gateway, $template, $context);

            return new Response($html);
        }

        $transaction = $app['payments.processor']->getCreateCard($gateway);
        $template = $this->config->getTemplate('pages', 'payment');
        $context = [
            'method'  => 'createCard',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Create/submit gateway update Credit Card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function updateCard(Application $app, Request $request, $name)
    {
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $response = $app['payments.processor']->setUpdateCard($request, $gateway);
            $template = $this->config->getTemplate('pages', 'complete');
            $context = [
                'response' => $response,
            ];
            $html = $this->render($gateway, $template, $context);

            return new Response($html);
        }

        $transaction = $app['payments.processor']->getUpdateCard($gateway);
        $template = $this->config->getTemplate('pages', 'payment');
        $context = [
            'method'  => 'updateCard',
            'params'  => $transaction,
            'card'    => $transaction->getCard()->getParameters(),
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
    }

    /**
     * Create/submit gateway delete Credit Card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function deleteCard(Application $app, Request $request, $name)
    {
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        $gateway = $this->gatewayManager->initializeSessionGateway($name);
        if ($request->isMethod('POST')) {
            $response = $app['payments.processor']->setDeleteCard($request, $gateway);

            $template = $this->config->getTemplate('pages', 'complete');
            $context = [
                'response' => $response,
            ];

            $html = $this->render($gateway, $template, $context);

            return new Response($html);
        }

        $transaction = $app['payments.processor']->getDeleteCard($gateway);
        $template = $this->config->getTemplate('pages', 'payment');
        $context = [
            'method'  => 'deleteCard',
            'params'  => $transaction,
        ];
        $html = $this->render($gateway, $template, $context);

        return new Response($html);
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
    private function render($gateway, $template, array $context = [])
    {
        $context += [
            'gateway'  => $gateway,
            'settings' => $gateway->getParameters(),
        ];

        return $this->twig->render($template, $context);
    }
}
