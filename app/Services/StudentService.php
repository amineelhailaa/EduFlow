<?php

namespace App\Services;

use App\Contracts\StudentRepositoryInterface;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentService
{
    private const MAX_GROUP_SIZE = 25;

    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private StripeCheckoutService $stripeCheckoutService,
    ) {
    }

    public function joinCourse(User $user, int $courseId): array
    {
        return DB::transaction(function () use ($user, $courseId): array {
            $course = $this->studentRepository->findCourseOrFail($courseId);

            $existingInscription = $this->studentRepository->findInscription($user->id, $course->id);

            if ($existingInscription !== null) {
                return [
                    'inscription' => $existingInscription->load(['course', 'group']),
                    'created' => false,
                ];
            }

            $group = null;

            foreach ($this->studentRepository->groupsForCourse($course->id) as $existingGroup) {
                if ($this->studentRepository->countGroupStudents($existingGroup->id) < self::MAX_GROUP_SIZE) {
                    $group = $existingGroup;
                    break;
                }
            }

            if ($group === null) {
                $group = $this->studentRepository->createGroup($course->id);
            }

            $inscription = $this->studentRepository->createInscription([
                'user_id' => $user->id,
                'cours_id' => $course->id,
                'group_id' => $group->id,
                'payment_status' => Inscription::PAYMENT_UNPAID,
            ]);

            return [
                'inscription' => $inscription,
                'created' => true,
            ];
        });
    }

    public function createCheckoutSession(User $user, int $courseId): array
    {
        $course = $this->studentRepository->findCourseOrFail($courseId);

        if ($course->price <= 0) {
            throw ValidationException::withMessages([
                'course' => 'This course has no payable amount.',
            ]);
        }

        $inscription = $this->studentRepository->findInscription($user->id, $course->id);

        // If no inscription exists, create one with unpaid status
        if ($inscription === null) {
            $inscription = $this->studentRepository->createInscription([
                'user_id' => $user->id,
                'cours_id' => $course->id,
                'payment_status' => Inscription::PAYMENT_UNPAID,
            ]);
        }

        // If already paid, return the paid inscription
        if ($inscription->payment_status === Inscription::PAYMENT_PAID) {
            return [
                'already_paid' => true,
                'inscription' => $inscription->load(['course', 'group']),
            ];
        }

        // Create Stripe checkout session
        $checkout = $this->stripeCheckoutService->createCheckoutSession($course, $user);

        // Update inscription with Stripe session ID
        $inscription = $this->studentRepository->updateInscription($inscription, [
            'stripe_session_id' => $checkout['id'],
        ]);

        return [
            'already_paid' => false,
            'checkout_url' => $checkout['url'],
            'session_id' => $checkout['id'],
            'inscription' => $inscription,
        ];
    }

    public function confirmStripePayment(string $sessionId): array
    {
        $session = $this->stripeCheckoutService->retrieveCheckoutSession($sessionId);

        if (($session['payment_status'] ?? null) !== 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'Stripe session is not marked as paid.',
            ]);
        }

        $inscription = $this->studentRepository->findInscriptionByStripeSessionId($sessionId);

        if ($inscription === null) {
            throw ValidationException::withMessages([
                'inscription' => 'No inscription linked to this Stripe session.',
            ]);
        }

        if ($inscription->payment_status === Inscription::PAYMENT_PAID) {
            return [
                'updated' => false,
                'inscription' => $inscription->load(['course', 'group']),
            ];
        }

        $inscription = $this->studentRepository->updateInscription($inscription, [
            'payment_status' => Inscription::PAYMENT_PAID,
        ]);

        return [
            'updated' => true,
            'inscription' => $inscription,
        ];
    }

    public function leaveCourse(User $user, int $courseId): array
    {
        $course = $this->studentRepository->findCourseOrFail($courseId);

        $inscription = $this->studentRepository->findInscription($user->id, $course->id);

        if ($inscription === null) {
            return [
                'removed' => false,
            ];
        }

        $this->studentRepository->deleteInscription($inscription);

        return [
            'removed' => true,
        ];
    }
}
