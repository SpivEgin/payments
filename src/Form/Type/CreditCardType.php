<?php

namespace Bolt\Extension\Bolt\Payments\Form\Type;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Form\AbstractType;
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
class CreditCardType extends AbstractType
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
        return 'credit_card';
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
                    'required'    => $this->config->getFormRequired('credit_card', 'first_name'),
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
                    'required'    => $this->config->getFormRequired('credit_card', 'last_name'),
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
                    'required'    => $this->config->getFormRequired('credit_card', 'number'),
                ]
            )
            ->add(
                'startDate',
                ValidStartDateType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'start_date')),
                    'data'        => $this->getData($options, 'startDate'),
                    'constraints' => [
                        new Assert\Range([
                            'max' => 'today',
                            'maxMessage' => 'Start date must be in the past.'
                        ])
                    ],
                ]
            )
            ->add(
                'expiryDate',
                ValidExpiryDateType::class,
                [
                    'label'       => Trans::__($this->config->getFormLabel('credit_card', 'expiry_date')),
                    'data'        => $this->getData($options, 'expiryDate'),
                    'constraints' => [
                        new Assert\Range([
                            'min'        => 'today',
                            'minMessage' => 'Expiry date must be in the future.'
                        ])
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
                    'required'    => $this->config->getFormRequired('credit_card', 'ccv'),
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
                    'required'    => $this->config->getFormRequired('credit_card', 'issue_number'),
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
     * @return CreditCardType
     */
    public function setRequireCreditCard($requireCreditCard)
    {
        $this->requireCreditCard = $requireCreditCard;

        return $this;
    }
}
