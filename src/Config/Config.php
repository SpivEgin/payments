<?php

namespace Bolt\Extension\Bolt\Payments\Config;

/**
 * Configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Config
{
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->mountpoint = $config['mountpoint'];
    }
}
