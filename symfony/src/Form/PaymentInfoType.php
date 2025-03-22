<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use DateTime;

class PaymentInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'XXXX XXXX XXXX XXXX',
                    'autocomplete' => 'cc-number',
                ],
                'label' => 'Credit Card Number',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your credit card number',
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9\s]{13,19}$/',
                        'message' => 'Please enter a valid credit card number',
                    ]),
                    new Callback([
                        'callback' => [$this, 'validateCreditCard'],
                    ]),
                ],
            ])
            ->add('expirationDate', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'MM/YY',
                    'autocomplete' => 'cc-exp',
                ],
                'label' => 'Expiration Date',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the expiration date',
                    ]),
                    new Regex([
                        'pattern' => '/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
                        'message' => 'Please enter a valid expiration date (MM/YY)',
                    ]),
                    new Callback([
                        'callback' => [$this, 'validateExpirationDate'],
                    ]),
                ],
            ])
            ->add('cvv', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'XXX',
                    'autocomplete' => 'cc-csc',
                    'maxlength' => 3,
                ],
                'label' => 'CVV',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter the CVV',
                    ]),
                    new Length(exactly: 3, exactMessage: 'CVV should be exactly 3 digits'),
                    new Regex([
                        'pattern' => '/^[0-9]{3}$/',
                        'message' => 'CVV should contain only digits',
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

    public function validateCreditCard($value, ExecutionContextInterface $context): void
    {
        // Remove spaces
        $cardNumber = str_replace(' ', '', $value);

        // Check if the card number passes the Luhn algorithm
        if (!$this->validateLuhn($cardNumber)) {
            $context->buildViolation('Please enter a valid credit card number')
                ->addViolation();
        }
    }

    public function validateExpirationDate($value, ExecutionContextInterface $context): void
    {
        if (preg_match('/^(0[1-9]|1[0-2])\/([0-9]{2})$/', $value, $matches)) {
            $month = (int)$matches[1];
            $year = (int)('20' . $matches[2]);

            $now = new DateTime();
            $expirationDate = new DateTime("$year-$month-01");
            $expirationDate->modify('last day of this month');

            if ($expirationDate <= $now) {
                $context->buildViolation('The expiration date must be in the future')
                    ->addViolation();
            }
        }
    }

    private function validateLuhn(string $number): bool
    {
        // Luhn algorithm implementation
        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$number[$i];

            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return ($sum % 10) == 0;
    }
}
