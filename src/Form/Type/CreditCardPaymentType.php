<?php

namespace Bolt\Extension\Bolt\Payments\Form\Type;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Translation\Translator as Trans;
use Carbon\Carbon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Credit card payment request form type.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CreditCardPaymentType extends AbstractType
{
    /** @var Config */
    protected $config;
    /** @var boolean */
    protected $requireCreditCard;

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
        return 'credit_card_payment';
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
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'first_name')),
                    'data'        => $this->getData($options, 'firstName'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'first_name'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'lastName',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'last_name')),
                    'data'        => $this->getData($options, 'lastName'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'last_name'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'number',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'number')),
                    'data'        => $this->getData($options, 'number'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'number'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Luhn(),
                    ],
                ]
            )
            ->add(
                'expiryMonth',
                NumberType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'expiry_month')),
                    'data'        => $this->getData($options, 'expiryMonth'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'expiry_month'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range([
                            'min'        => 1,
                            'max'        => 12,
                            'minMessage' => 'Must be a numerical month value',
                            'maxMessage' => 'Must be a numerical month value',
                        ]),
                    ],
                ]
            )
            ->add(
                'expiryYear',
                NumberType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'expiry_year')),
                    'data'        => $this->getData($options, 'expiryMonth'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'expiry_year'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range([
                            'min'        => Carbon::now()->format('Y'),
                            'max'        => Carbon::now()->addYears(10)->format('Y'),
                            'minMessage' => 'Year can not be in the past',
                            'maxMessage' => sprintf('Year must be before %s.', Carbon::now()->addYears(10)->format('Y')),
                        ]),
                    ],
                ]
            )
            ->add(
                'startMonth',
                NumberType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'start_month')),
                    'data'        => $this->getData($options, 'startMonth'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'start_month'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range([
                            'min'        => 1,
                            'max'        => 12,
                            'minMessage' => 'Must be a numerical month value',
                            'maxMessage' => 'Must be a numerical month value',
                        ]),
                    ],
                ]
            )
            ->add(
                'startYear',
                NumberType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'start_year')),
                    'data'        => $this->getData($options, 'startMonth'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'start_year'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range([
                            'min'        => Carbon::now()->addYears(-10)->format('Y'),
                            'max'        => Carbon::now()->format('Y'),
                            'minMessage' => 'Year can not be in the future',
                            'maxMessage' => sprintf('Year must be after %s.', Carbon::now()->addYears(-10)->format('Y')),
                        ]),
                    ],
                ]
            )
            ->add(
                'ccv',
                NumberType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'ccv')),
                    'data'        => $this->getData($options, 'ccv'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'ccv'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'issueNumber',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'issue_number')),
                    'data'        => $this->getData($options, 'issueNumber'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'issue_number'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'address1',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'address_1')),
                    'data'        => $this->getData($options, 'address1'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'address_1'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'address2',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'address_2')),
                    'data'        => $this->getData($options, 'address2'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'address_2'),
                    ],
                    'constraints' => [
                    ],
                ]
            )
            ->add(
                'city',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'address_city')),
                    'data'        => $this->getData($options, 'city'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'address_city'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'postcode',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'address_postcode')),
                    'data'        => $this->getData($options, 'postcode'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'address_postcode'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'state',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'address_state')),
                    'data'        => $this->getData($options, 'state'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'address_state'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'country',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'address_country')),
                    'data'        => $this->getData($options, 'country'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'address_country'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'phone')),
                    'data'        => $this->getData($options, 'phone'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'phone'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'email')),
                    'data'        => $this->getData($options, 'email'),
                    'attr'        => [
                        'placeholder' => $this->config->getFormPlaceholder('credit_card', 'email'),
                    ],
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Email(
                            [
                                'checkMX' => true,
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label'   => Trans::__($this->config->getFormLabel('credit_card', 'submit')),
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

    /**
     * @param boolean $requireCreditCard
     *
     * @return CreditCardPaymentType
     */
    public function setRequireCreditCard($requireCreditCard)
    {
        $this->requireCreditCard = $requireCreditCard;

        return $this;
    }
}
