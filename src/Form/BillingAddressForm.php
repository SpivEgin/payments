<?php

namespace Bolt\Extension\Bolt\Payments\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Billing address form.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class BillingAddressForm
{
    /** @var FormFactoryInterface */
    protected $formFactory;
    /** @var FormTypeInterface */
    protected $type;
    /** @var Form */
    protected $form;

    public function __construct(FormFactoryInterface $formFactory, FormTypeInterface $type)
    {
        $this->formFactory = $formFactory;
        $this->type = $type;
    }

    /**
     * Create the form.
     *
     * @param array $entity
     * @param array $data
     *
     * @return Form
     */
    public function createForm($entity = [], $data = [])
    {
        $builder = $this->formFactory->createBuilder($this->type, $entity, $data);

        return $this->form = $builder->getForm();
    }

    /**
     * Return the form object.
     *
     * @throws \RuntimeException
     *
     * @return Form
     */
    public function getForm()
    {
        if ($this->form === null) {
            throw new \RuntimeException('Form has not been created.');
        }

        return $this->form;
    }
}
