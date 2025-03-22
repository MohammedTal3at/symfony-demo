<?php

namespace App\Controller;

use App\Enum\SubscriptionType;
use App\Form\AddressInfoType;
use App\Form\PaymentInfoType;
use App\Form\UserInfoType;
use App\Service\OnboardingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OnboardingController extends AbstractController
{
    #[Route('onboarding/step-1', name: 'onboarding_first_step')]
    public function firstStep(Request $request, SessionInterface $session): Response
    {
        // Get user data from session if it exists
        $userData = $session->get('user_data', []);

        $form = $this->createForm(UserInfoType::class, $userData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get form data
            $formData = $form->getData();

            // Store in session
            $session->set('user_data', $formData);

            return $this->redirectToRoute('onboarding_second_step');

        }

        return $this->render('onboarding/first_step.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('onboarding/step-2', name: 'onboarding_second_step')]
    public function secondStep(Request $request, SessionInterface $session): Response
    {
        // Get user data from session if it exists
        $userData = $session->get('user_data', []);
        if (!$userData) {
            return $this->redirectToRoute('onboarding_first_step');
        }

        // Get address data from the session if exist
        $addressData = $session->get('address_data', []);

        $form = $this->createForm(AddressInfoType::class, $addressData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Save address data in the session
            $formData = $form->getData();
            $session->set('address_data', $formData);

            return $userData['subscriptionType'] === SubscriptionType::PREMIUM ?
                $this->redirectToRoute('onboarding_third_step') :
                $this->redirectToRoute('onboarding_confirm_step');
        }
        return $this->render('onboarding/second_step.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('onboarding/step-3', name: 'onboarding_third_step')]
    public function thirdStep(Request $request, SessionInterface $session): Response
    {
        // Get user data from session
        $userData = $session->get('user_data', []);
        if (!$userData) {
            return $this->redirectToRoute('onboarding_first_step');
        }

        // Check if user selected Premium subscription
        if ($userData['subscriptionType'] !== SubscriptionType::PREMIUM) {
            return $this->redirectToRoute('onboarding_confirm_step');
        }

        // Get address data from session
        $addressData = $session->get('address_data', []);
        if (!$addressData) {
            return $this->redirectToRoute('onboarding_second_step');
        }

        // Get payment data from session if it exists
        $paymentData = $session->get('payment_data', []);

        $form = $this->createForm(PaymentInfoType::class, $paymentData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save payment data in the session
            $formData = $form->getData();
            $session->set('payment_data', $formData);

            return $this->redirectToRoute('onboarding_confirm_step');
        }

        return $this->render('onboarding/third_step.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('onboarding/confirm-step', name: 'onboarding_confirm_step')]
    public function confirmStep(
        Request           $request,
        SessionInterface  $session,
        OnboardingService $onboardingService
    ): Response
    {
        // Validate that all required data is present
        $validationResult = $onboardingService->validateOnboardingData($session);
        if (!$validationResult['isValid']) {
            return $this->redirectToRoute($validationResult['redirectRoute']);
        }

        // Get confirmation data for display
        $confirmationData = $onboardingService->getConfirmationData($session);

        // Handle form submission
        if ($request->isMethod('POST')) {
            // Process and save all onboarding data
            $onboardingService->processOnboarding($session);

            // Redirect to success page
            return $this->redirectToRoute('onboarding_success');
        }

        return $this->render('onboarding/confirm_step.html.twig', $confirmationData);
    }

    #[Route('onboarding/success', name: 'onboarding_success')]
    public function success(): Response
    {
        return $this->render('onboarding/success.html.twig');
    }
}
