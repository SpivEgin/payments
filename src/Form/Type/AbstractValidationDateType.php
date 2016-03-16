<?php

namespace Bolt\Extension\Bolt\Payments\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Abstract credit card DateType.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AbstractValidationDateType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ('choice' === $options['widget']) {
            if ($view['day']->vars['value'] === '') {
                $view['day']->vars['value'] = $view['day']->vars['choices'][0]->value;
            }

            $style = 'display:none';
            if ($view['day']->vars['attr']['style'] !== null) {
                $style = $view['day']->vars['attr']['style'] . '; ' . $style;
            }
            $view['day']->vars['attr']['style'] = $style;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return DateType::class;
    }
}
