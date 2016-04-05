<?php

namespace Bolt\Extension\Bolt\Payments\Config;

/**
 * Configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Config
{
    /** @var string */
    protected $mountpoint;
    /** @var array */
    protected $templates;
    /** @var array */
    protected $forms;
    /** @var Provider */
    protected $providers;
    /** @var string */
    private $baseUrl;

    /**
     * Constructor.
     *
     * @param array  $config
     * @param string $baseUrl
     */
    public function __construct(array $config, $baseUrl)
    {
        $this->mountpoint = $config['mountpoint'];
        $this->forms = $config['forms'];
        $this->templates = $config['templates'];
        $this->providers = new Provider($config);
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return Provider
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Return the base route.
     *
     * @return string
     */
    public function getMountpoint()
    {
        return $this->mountpoint;
    }

    /**
     * Return a configured template name.
     *
     * @param string $category
     * @param string $type
     *
     * @return string
     */
    public function getTemplate($category, $type)
    {
        return $this->templates[$category][$type];
    }

    /**
     * @return string
     */
    public function getFormLabel($form, $field)
    {
        return $this->forms[$form][$field]['label'];
    }

    /**
     * @return string
     */
    public function getFormPlaceholder($form, $field)
    {
        return $this->forms[$form][$field]['placeholder'];
    }

    /**
     * @return string
     */
    public function getFormRequired($form, $field)
    {
        return $this->forms[$form][$field]['required'];
    }
    
    /**
     * Construct an internal payments URL.
     *
     * @param string $provider
     * @param string $routeName
     *
     * @return string
     */
    public function getTransactionUrl($provider, $routeName)
    {
        return sprintf('%s/gateways/%s/%s', $this->baseUrl, $provider, $routeName);
    }
}
