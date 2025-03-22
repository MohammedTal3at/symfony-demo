<?php

namespace App\Service;

use App\Entity\Address;
use App\Entity\Payment;
use App\Entity\User;
use App\Enum\SubscriptionType;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

readonly class OnboardingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CountryRepository      $countryRepository
    )
    {
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
        $this->createAddress($addressData, $user);

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
        $user->setName($userData['name'] ?? $userData['firstName'] . ' ' . $userData['lastName']);
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
        $address->setStateProvince($addressData['state'] ?? $addressData['stateProvince']);
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

        // Store full credit card number (in a real app, you'd encrypt this)
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
