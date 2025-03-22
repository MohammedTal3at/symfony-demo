<?php

namespace App\Form;

use App\Enum\SubscriptionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your full name'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your name',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your email address'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your email',
                    ]),
                    new Email([
                        'message' => 'Please enter a valid email address',
                    ]),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your phone number'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your phone number',
                    ]),
                    new Regex([
                        'pattern' => '/^\d{10,}$/',
                        'message' => 'Phone number must contain at least 10 digits',
                    ]),
                ],
            ])
            ->add('subscriptionType', ChoiceType::class, [
                'choices' => [
                    'Free' => SubscriptionType::FREE,
                    'Premium' => SubscriptionType::PREMIUM,
                ],
                'expanded' => true,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'form-check-input'];
                },
                'label_html' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select a subscription type',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
