<?php

namespace App\Controller;


use App\Service\OnboardingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OnboardingController extends AbstractController
{
    #[Route('onboarding/step-1', name: 'onboarding_first_step')]
    public function firstStep(
        Request $request, 
        SessionInterface $session,
        OnboardingService $onboardingService
    ): Response {
        $result = $onboardingService->handleFirstStep($request, $session);
        
        if ($result['success']) {
            return $this->redirectToRoute('onboarding_second_step');
        }

        return $this->render('onboarding/first_step.html.twig', [
            'form' => $result['form']->createView(),
        ]);
    }

    #[Route('onboarding/step-2', name: 'onboarding_second_step')]
    public function secondStep(
        Request $request, 
        SessionInterface $session,
        OnboardingService $onboardingService
    ): Response {
        $result = $onboardingService->handleSecondStep($request, $session);
        
        if ($result['redirect']) {
            return $this->redirectToRoute($result['redirect']);
        }
        
        if ($result['success']) {
            return $this->redirectToRoute($result['redirect']);
        }

        return $this->render('onboarding/second_step.html.twig', [
            'form' => $result['form']->createView(),
        ]);
    }

    #[Route('onboarding/step-3', name: 'onboarding_third_step')]
    public function thirdStep(
        Request $request, 
        SessionInterface $session,
        OnboardingService $onboardingService
    ): Response {
        $result = $onboardingService->handleThirdStep($request, $session);
        
        if ($result['redirect']) {
            return $this->redirectToRoute($result['redirect']);
        }
        
        if ($result['success']) {
            return $this->redirectToRoute($result['redirect']);
        }

        return $this->render('onboarding/third_step.html.twig', [
            'form' => $result['form']->createView(),
        ]);
    }

    #[Route('onboarding/confirm-step', name: 'onboarding_confirm_step')]
    public function confirmStep(
        Request $request,
        SessionInterface $session,
        OnboardingService $onboardingService
    ): Response {
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
