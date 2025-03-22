<?php

namespace App\Form;

use App\Repository\CountryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressInfoType extends AbstractType
{
    public function __construct(private CountryRepository $countryRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addressLine1', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your street address'
                ],
                'label' => 'Address Line 1',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your address',
                    ]),
                ],
            ])
            ->add('addressLine2', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Apartment, suite, unit, building, floor, etc.'
                ],
                'label' => 'Address Line 2 (optional)',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your city'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your city',
                    ]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your postal/zip code'
                ],
                'label' => 'Postal Code',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your postal code',
                    ]),
                ],
            ])
            ->add('state', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your state/province'
                ],
                'label' => 'State/Province',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your state or province',
                    ]),
                ],
            ])
            ->add('country', ChoiceType::class, [
                'choices' => $this->getCountryChoices(),
                'placeholder' => 'Select a country',
                'attr' => [
                    'class' => 'form-select',
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please select your country',
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

    private function getCountryChoices(): array
    {
        $countries = $this->countryRepository->findAll();
        $choices = [];

        foreach ($countries as $country) {
            $choices[$country->getName()] = $country->getId();
        }

        return $choices;
    }
}
