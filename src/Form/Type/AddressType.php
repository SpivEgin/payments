<?php

namespace Bolt\Extension\Bolt\Payments\Form\Type;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Account shipping/billing address form type.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AddressType extends AbstractType
{
    /** @var Config */
    protected $config;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'address';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'first_name')),
                    'data'        => $this->getData($options, 'firstName'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'first_name'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'first_name'),
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'last_name')),
                    'data'        => $this->getData($options, 'lastName'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'last_name'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'last_name'),
                ]
            )
            ->add(
                'address1',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'address_1')),
                    'data'        => $this->getData($options, 'address1'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'address_1'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'address_1'),
                ]
            )
            ->add(
                'address2',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'address_2')),
                    'data'        => $this->getData($options, 'address2'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'address_2'),
                    ],
                    'constraints' => [
                    ],
                    'required'    => $this->config->getFormRequired('address', 'address_2'),
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'address_city')),
                    'data'        => $this->getData($options, 'city'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'address_city'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'address_city'),
                ]
            )
            ->add(
                'postcode',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'address_postcode')),
                    'data'        => $this->getData($options, 'postcode'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'address_postcode'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'address_postcode'),
                ]
            )
            ->add(
                'state',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'address_state')),
                    'data'        => $this->getData($options, 'state'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'address_state'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'address_state'),
                ]
            )
            ->add(
                'country',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'address_country')),
                    'data'        => $this->getData($options, 'country'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'address_country'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'address_country'),
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'phone')),
                    'data'        => $this->getData($options, 'phone'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'phone'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'phone'),
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('address', 'email')),
                    'data'        => $this->getData($options, 'email'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('address', 'email'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(
                            [
                                'checkMX' => true,
                            ]
                        ),
                    ],
                    'required'    => $this->config->getFormRequired('address', 'email'),
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label'   => Trans::__($this->config->getFormLabel('address', 'submit')),
                ]
            )
        ;
    }

    /**
     * Return a valid data option.
     *
     * @param array  $options
     * @param string $field
     *
     * @return mixed|null
     */
    protected function getData(array $options, $field)
    {
        if (!isset($options['data'])) {
            return null;
        }

        return isset($options['data'][$field]) ? $options['data'][$field] : null;
    }
}
