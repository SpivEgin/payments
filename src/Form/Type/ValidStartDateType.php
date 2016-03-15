<?php

namespace Bolt\Extension\Bolt\Payments\Form\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Credit card valid start date form type.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ValidStartDateType extends AbstractCreditCardDateType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'max_year_valid_start' => date('Y'),
            'min_year_valid_start' => date('Y') - 10,
            'years'                => function (Options $options) {
                return range($options['min_year_valid_start'], $options['max_year_valid_start']);
            },
        ]);
    }
}
