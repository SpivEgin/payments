<?php

namespace Bolt\Extension\Bolt\Payments\Controller;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Extension\Bolt\Members\AccessControl\Session as MembersSession;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Frontend controller.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Frontend implements ControllerProviderInterface
{
    /** @var Config */
    protected $config;

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
        return new Response('Not implemented yet', Response::HTTP_FORBIDDEN);

        if ($request->isMethod('POST')) {
            $app['payments.processor']->setSettings($request, $name);
            $target = $request->getBaseUrl() . $request->getPathInfo();

            return new RedirectResponse($target);
        }

        $html = $app['payments.processor']->getSettings($name);

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

        if ($request->isMethod('POST')) {
            $html = $app['payments.processor']->setAuthorize($request, $name);

            return new Response($html);
        }

        $html = $app['payments.processor']->getAuthorize($request, $name);

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

        $html = $app['payments.processor']->completeAuthorize($name);

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

        if ($request->isMethod('POST')) {
            $html = $app['payments.processor']->setCapture($request, $name);

            return new Response($html);
        }

        $html = $app['payments.processor']->getCapture($name);

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
        if ($request->isMethod('POST')) {
            $authorisation = $app['members.session']->getAuthorisation();
            $html = $app['payments.processor']->setPurchase($request, $name, $authorisation);

            return new Response($html);
        }

        $html = $app['payments.processor']->getPurchase($request, $name);

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
        $authorisation = $app['members.session']->getAuthorisation();
        $html = $app['payments.processor']->completePurchase($request, $name, $authorisation);

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

        if ($request->isMethod('POST')) {
            $html = $app['payments.processor']->setCreateCard($request, $name);

            return new Response($html);
        }

        $html = $app['payments.processor']->getCreateCard($name);

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

        if ($request->isMethod('POST')) {
            $html = $app['payments.processor']->setUpdateCard($request, $name);

            return new Response($html);
        }

        $html = $app['payments.processor']->getUpdateCard($name);

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

        if ($request->isMethod('POST')) {
            $html = $app['payments.processor']->setDeleteCard($request, $name);

            return new Response($html);
        }

        $html = $app['payments.processor']->getDeleteCard($name);

        return new Response($html);
    }
}
