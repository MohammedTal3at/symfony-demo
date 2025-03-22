<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Payment;
use App\Entity\User;
use App\Enum\SubscriptionType;
use App\Form\AddressInfoType;
use App\Form\PaymentInfoType;
use App\Form\UserInfoType;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OnboardingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CountryRepository $countryRepository,
        private FormFactoryInterface $formFactory
    ) {
    }

    /**
     * Handle the first step of the onboarding process
     */
    public function handleFirstStep(Request $request, SessionInterface $session): array
    {
        // Get user data from session if it exists
        $userData = $session->get('user_data', []);

        $form = $this->formFactory->create(UserInfoType::class, $userData);
        $form->handleRequest($request);

        $result = [
            'form' => $form,
            'success' => false
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data
            $formData = $form->getData();

            // Store in session
            $session->set('user_data', $formData);

            $result['success'] = true;
        }

        return $result;
    }

    /**
     * Handle the second step of the onboarding process
     */
    public function handleSecondStep(Request $request, SessionInterface $session): array
    {
        // Get user data from session if it exists
        $userData = $session->get('user_data', []);
        
        $result = [
            'form' => null,
            'success' => false,
            'redirect' => null
        ];
        
        if (!$userData) {
            $result['redirect'] = 'onboarding_first_step';
            return $result;
        }

        // Get address data from the session if exist
        $addressData = $session->get('address_data', []);

        $form = $this->formFactory->create(AddressInfoType::class, $addressData);
        $form->handleRequest($request);

        $result['form'] = $form;

        if ($form->isSubmitted() && $form->isValid()) {
            // Save address data in the session
            $formData = $form->getData();
            $session->set('address_data', $formData);

            $result['success'] = true;
            
            // Determine next step based on subscription type
            if ($userData['subscriptionType'] === SubscriptionType::PREMIUM) {
                $result['redirect'] = 'onboarding_third_step';
            } else {
                $result['redirect'] = 'onboarding_confirm_step';
            }
        }

        return $result;
    }

    /**
     * Handle the third step of the onboarding process
     */
    public function handleThirdStep(Request $request, SessionInterface $session): array
    {
        // Get user data from session
        $userData = $session->get('user_data', []);
        
        $result = [
            'form' => null,
            'success' => false,
            'redirect' => null
        ];
        
        if (!$userData) {
            $result['redirect'] = 'onboarding_first_step';
            return $result;
        }

        // Check if user selected Premium subscription
        if ($userData['subscriptionType'] !== SubscriptionType::PREMIUM) {
            $result['redirect'] = 'onboarding_confirm_step';
            return $result;
        }

        // Get address data from session
        $addressData = $session->get('address_data', []);
        if (!$addressData) {
            $result['redirect'] = 'onboarding_second_step';
            return $result;
        }

        // Get payment data from session if it exists
        $paymentData = $session->get('payment_data', []);

        $form = $this->formFactory->create(PaymentInfoType::class, $paymentData);
        $form->handleRequest($request);

        $result['form'] = $form;

        if ($form->isSubmitted() && $form->isValid()) {
            // Save payment data in the session
            $formData = $form->getData();
            $session->set('payment_data', $formData);

            $result['success'] = true;
            $result['redirect'] = 'onboarding_confirm_step';
        }

        return $result;
    }

    /**
     * Process and save all onboarding data
     */
    public function processOnboarding(SessionInterface $session): void
    {
        $userData = $session->get('user_data', []);
        $addressData = $session->get('address_data', []);
        $paymentData = $session->get('payment_data', []);

        // Create and save the user
        $user = $this->createUser($userData);
        
        // Create and save the address
        $address = $this->createAddress($addressData, $user);
        
        // If Premium subscription, save payment info
        if ($userData['subscriptionType'] === SubscriptionType::PREMIUM && $paymentData) {
            $this->createPayment($paymentData, $user);
        }
        
        // Save everything to the database
        $this->entityManager->flush();
        
        // Clear session data
        $this->clearSessionData($session);
    }
    
    /**
     * Get confirmation data for display
     */
    public function getConfirmationData(SessionInterface $session): array
    {
        $userData = $session->get('user_data', []);
        $addressData = $session->get('address_data', []);
        $paymentData = $session->get('payment_data', []);
        
        // Get country name if we have a country ID
        $countryName = null;
        if (isset($addressData['country']) && is_numeric($addressData['country'])) {
            $country = $this->countryRepository->find($addressData['country']);
            if ($country) {
                $countryName = $country->getName();
            }
        }
        
        // Obfuscate credit card number for display
        $obfuscatedCardNumber = null;
        if (isset($paymentData['cardNumber'])) {
            $cardNumber = str_replace(' ', '', $paymentData['cardNumber']);
            $obfuscatedCardNumber = 'XXXX XXXX XXXX ' . substr($cardNumber, -4);
        }
        
        return [
            'userData' => $userData,
            'addressData' => $addressData,
            'paymentData' => $paymentData,
            'countryName' => $countryName,
            'obfuscatedCardNumber' => $obfuscatedCardNumber,
        ];
    }
    
    /**
     * Validate that all required data is present
     */
    public function validateOnboardingData(SessionInterface $session): array
    {
        $userData = $session->get('user_data', []);
        $addressData = $session->get('address_data', []);
        $paymentData = $session->get('payment_data', []);
        
        $result = [
            'isValid' => true,
            'redirectRoute' => null
        ];
        
        // Check if we have the required data
        if (!$userData) {
            $result['isValid'] = false;
            $result['redirectRoute'] = 'onboarding_first_step';
            return $result;
        }
        
        if (!$addressData) {
            $result['isValid'] = false;
            $result['redirectRoute'] = 'onboarding_second_step';
            return $result;
        }
        
        // If user selected Premium but no payment data, redirect to payment step
        if ($userData['subscriptionType'] === SubscriptionType::PREMIUM && !$paymentData) {
            $result['isValid'] = false;
            $result['redirectRoute'] = 'onboarding_third_step';
            return $result;
        }
        
        return $result;
    }
    
    private function createUser(array $userData): User
    {
        $user = new User();
        $user->setEmail($userData['email']);
        $user->setName($userData['name']);
        $user->setPhoneNumber($userData['phoneNumber']);
        $user->setSubscriptionType($userData['subscriptionType']);
        
        $this->entityManager->persist($user);
        
        return $user;
    }
    
    private function createAddress(array $addressData, User $user): Address
    {
        $address = new Address();
        $address->setAddressLine1($addressData['addressLine1']);
        
        if (isset($addressData['addressLine2']) && $addressData['addressLine2']) {
            $address->setAddressLine2($addressData['addressLine2']);
        } else {
            $address->setAddressLine2(null);
        }
        
        $address->setCity($addressData['city']);
        $address->setPostalCode($addressData['postalCode']);
        $address->setStateProvince($addressData['stateProvince'] ?? $addressData['state']);
        $address->setUser($user);
        
        // Set country if we have a country ID
        if (isset($addressData['country']) && is_numeric($addressData['country'])) {
            $country = $this->countryRepository->find($addressData['country']);
            if ($country) {
                $address->setCountry($country);
            }
        }
        
        $this->entityManager->persist($address);
        
        return $address;
    }
    
    private function createPayment(array $paymentData, User $user): Payment
    {
        $payment = new Payment();
        
        // Store credit card number
        $cardNumber = str_replace(' ', '', $paymentData['cardNumber']);
        $payment->setCreditCardNumber($cardNumber);
        $payment->setExpirationDate($paymentData['expirationDate']);
        $payment->setCvv($paymentData['cvv']);
        $payment->setUser($user);
        
        $this->entityManager->persist($payment);
        
        return $payment;
    }
    
    private function clearSessionData(SessionInterface $session): void
    {
        $session->remove('user_data');
        $session->remove('address_data');
        $session->remove('payment_data');
    }
}
