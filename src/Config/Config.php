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

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->mountpoint = $config['mountpoint'];
        $this->forms = $config['forms'];
        $this->providers = new Provider($config);
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
     * @return string
     */
    public function getTemplate($template)
    {
        return $this->templates[$template];
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
}
