<?php

namespace Bolt\Extension\Bolt\Payments\Controller;

use Bolt\Extension\Bolt\Payments\Config\Config;
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

        // gateway settings
        $ctr->get('/gateways/{name}', [$this, 'getSettings'])
            ->bind('payments-settings-get')
        ;

        // save gateway settings
        $ctr->post('/gateways/{name}', [$this, 'setSettings'])
            ->bind('payments-settings-set')
        ;

        // create gateway authorize
        $ctr->get('/gateways/{name}/authorize', [$this, 'getAuthorize'])
            ->bind('payments-authorize-get')
        ;

        // submit gateway authorize
        $ctr->post('/gateways/{name}/authorize', [$this, 'setAuthorize'])
            ->bind('payments-authorize-set')
        ;

        // create gateway completeAuthorize
        $ctr->get('/gateways/{name}/completeAuthorize', [$this, 'completeAuthorize'])
            ->bind('payments-completeAuthorize')
        ;

        // create gateway capture
        $ctr->get('/gateways/{name}/capture', [$this, 'getCapture'])
            ->bind('payments-capture-get')
        ;

        // submit gateway capture
        $ctr->post('/gateways/{name}/capture', [$this, 'setCapture'])
            ->bind('payments-capture-set')
        ;

        // create gateway purchase
        $ctr->get('/gateways/{name}/purchase', [$this, 'getPurchase'])
            ->bind('payments-purchase-get')
        ;

        // submit gateway purchase
        $ctr->post('/gateways/{name}/purchase', [$this, 'setPurchase'])
            ->bind('payments-purchase-set')
        ;

        // gateway purchase return
        // this won't work for gateways which require an internet-accessible URL (yet)
        $ctr->match('/gateways/{name}/completePurchase', [$this, 'completePurchase'])
            ->bind('payments-purchase-complete')
        ;

        // create gateway create Credit Card
        $ctr->get('/gateways/{name}/create-card', [$this, 'getCreateCard'])
            ->bind('payments-create-card-get')
        ;

        // submit gateway create Credit Card
        $ctr->post('/gateways/{name}/create-card', [$this, 'setCreateCard'])
            ->bind('payments-create-card-set')
        ;

        // create gateway update Credit Card
        $ctr->get('/gateways/{name}/update-card', [$this, 'getUpdateCard'])
            ->bind('payments-update-card-get')
        ;

        // submit gateway update Credit Card
        $ctr->post('/gateways/{name}/update-card', [$this, 'setUpdateCard'])
            ->bind('payments-update-card-set')
        ;

        // create gateway delete Credit Card
        $ctr->get('/gateways/{name}/delete-card', [$this, 'getDeleteCard'])
            ->bind('payments-delete-card-get')
        ;

        // submit gateway delete Credit Card
        $ctr->post('/gateways/{name}/delete-card', [$this, 'setDeleteCard'])
            ->bind('payments-delete-card-set')
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
     */
    public function before(Request $request, Response $response, Application $app)
    {
    }

    /**
     * Return gateway settings.
     *
     * @param Application $app
     * @param string      $name
     *
     * @return Response
     */
    public function getSettings(Application $app, $name)
    {
        $html = $app['payments.processor']->getSettings($name);

        return new Response($html);
    }

    /**
     * Save gateway settings.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return RedirectResponse
     */
    public function setSettings(Application $app, Request $request, $name)
    {
        $app['payments.processor']->setSettings($request, $name);
        $target = $request->getBaseUrl() . $request->getPathInfo();

        return new RedirectResponse($target);
    }

    /**
     * Create gateway authorize.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function getAuthorize(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->getAuthorize($request, $name);

        return new Response($html);
    }

    /**
     * Submit gateway authorize.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function setAuthorize(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->setAuthorize($request, $name);

        return new Response($html);
    }

    /**
     * Create gateway completeAuthorize.
     *
     * @param Application $app\
     * @param string      $name
     *
     * @return Response
     */
    public function completeAuthorize(Application $app, $name)
    {
        $html = $app['payments.processor']->completeAuthorize($name);

        return new Response($html);
    }

    /**
     * Create gateway capture.
     *
     * @param Application $app
     * @param string      $name
     *
     * @return Response
     */
    public function getCapture(Application $app, $name)
    {
        $html = $app['payments.processor']->getCapture($name);

        return new Response($html);
    }

    /**
     * Submit gateway capture.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function setCapture(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->setCapture($request, $name);

        return new Response($html);
    }

    /**
     * Create gateway purchase.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function getPurchase(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->getPurchase($request, $name);

        return new Response($html);
    }

    /**
     * Submit gateway purchase.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function setPurchase(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->setPurchase($request, $name);

        return new Response($html);
    }

    /**
     * Gateway purchase return.
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
        $html = $app['payments.processor']->completePurchase($request, $name);

        return new Response($html);
    }

    /**
     * Create gateway create Credit Card.
     *
     * @param Application $app
     * @param string      $name
     *
     * @return Response
     */
    public function getCreateCard(Application $app, $name)
    {
        $html = $app['payments.processor']->getCreateCard($name);

        return new Response($html);
    }

    /**
     * Submit gateway create Credit Card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function setCreateCard(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->setCreateCard($request, $name);

        return new Response($html);
    }

    /**
     * Create gateway update Credit Card.
     *
     * @param Application $app
     * @param string      $name
     *
     * @return Response
     */
    public function getUpdateCard(Application $app, $name)
    {
        $html = $app['payments.processor']->getUpdateCard($name);

        return new Response($html);
    }

    /**
     * Submit gateway update Credit Card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function setUpdateCard(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->setUpdateCard($request, $name);

        return new Response($html);
    }

    /**
     * Create gateway delete Credit Card.
     *
     * @param Application $app
     * @param string      $name
     *
     * @return Response
     */
    public function getDeleteCard(Application $app, $name)
    {
        $html = $app['payments.processor']->getDeleteCard($name);

        return new Response($html);
    }

    /**
     * Submit gateway delete Credit Card.
     *
     * @param Application $app
     * @param Request     $request
     * @param string      $name
     *
     * @return Response
     */
    public function setDeleteCard(Application $app, Request $request, $name)
    {
        $html = $app['payments.processor']->setDeleteCard($request, $name);

        return new Response($html);
    }
}
