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
}
