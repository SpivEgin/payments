<?php

namespace Bolt\Extension\Bolt\Payments;

use Bolt\Extension\SimpleExtension;

/**
 * Payments extension loader class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentsExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [];
    }
}
