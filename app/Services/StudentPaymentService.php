<?php

namespace App\Services;

use App\Contracts\StudentRepositoryInterface;
use App\Models\Inscription;
use App\Models\User;

class StudentPaymentService
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private StripeCheckoutService $stripeCheckoutService,
    ) {
    }

    public function paymentStatusForCourse(User $user, int $courseId): array
    {
        $inscription = $this->studentRepository->findInscription($user->id, $courseId);

        if ($inscription === null) {
            return [
                'paid' => false,
                'inscription' => null,
                'reason' => 'inscription_missing',
            ];
        }

        if ($inscription->payment_status === Inscription::PAYMENT_PAID) {
            return [
                'paid' => true,
                'inscription' => $inscription->load(['course', 'group']),
                'reason' => null,
            ];
        }

        if (! $inscription->stripe_session_id) {
            return [
                'paid' => false,
                'inscription' => $inscription->load(['course', 'group']),
                'reason' => 'payment_not_started',
            ];
        }

        $session = $this->stripeCheckoutService->retrieveCheckoutSession($inscription->stripe_session_id);

        if (($session['payment_status'] ?? null) !== 'paid') {
            return [
                'paid' => false,
                'inscription' => $inscription->load(['course', 'group']),
                'reason' => 'payment_not_completed',
            ];
        }

        $inscription = $this->studentRepository->updateInscription($inscription, [
            'payment_status' => Inscription::PAYMENT_PAID,
        ]);

        return [
            'paid' => true,
            'inscription' => $inscription,
            'reason' => null,
        ];
    }
}

