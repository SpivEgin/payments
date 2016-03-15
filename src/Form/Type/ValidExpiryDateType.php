<?php

namespace Bolt\Extension\Bolt\Payments\Form\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Credit card valid expiry date form type.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ValidExpiryDateType extends AbstractCreditCardDateType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'max_year_valid_end' => date('Y') + 10,
            'min_year_valid_end' => date('Y'),
            'years'              => function (Options $options) {
                return range($options['min_year_valid_end'], $options['max_year_valid_end']);
            },
        ]);
    }
}
