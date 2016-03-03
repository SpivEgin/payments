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
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->mountpoint = $config['mountpoint'];
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
}
