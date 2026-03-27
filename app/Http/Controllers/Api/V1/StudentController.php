<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentPaymentService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private StudentPaymentService $studentPaymentService,
    ) {
    }

    public function joinCourse(Request $request, Course $course): JsonResponse
    {
        $user = $request->user('api');

        // Check if user has already paid for this course
        $paymentStatus = $this->studentPaymentService->paymentStatusForCourse($user, $course->id);

        if ($paymentStatus['paid']) {
            // User already paid, join the course
            $result = $this->studentService->joinCourse($user, $course->id);

            return response()->json([
                'message' => $result['created']
                    ? 'Joined course successfully.'
                    : 'User is already enrolled in this course.',
                'inscription' => $result['inscription'],
            ], $result['created'] ? 201 : 200);
        }

        // User hasn't paid, process checkout
        $checkoutResult = $this->studentService->createCheckoutSession($user, $course->id);

        if ($checkoutResult['already_paid']) {
            return response()->json([
                'message' => 'Inscription is already paid.',
                'inscription' => $checkoutResult['inscription'],
            ]);
        }

        return response()->json([
            'message' => 'Please complete payment to join the course.',
            'session_id' => $checkoutResult['session_id'],
            'checkout_url' => $checkoutResult['checkout_url'],
            'inscription' => $checkoutResult['inscription'],
        ]);
    }

    public function checkoutCourse(Request $request, Course $course): JsonResponse
    {
        $result = $this->studentService->createCheckoutSession($request->user('api'), $course->id);

        if ($result['already_paid']) {
            return response()->json([
                'message' => 'Inscription is already paid.',
                'inscription' => $result['inscription'],
            ]);
        }

        return response()->json([
            'message' => 'Checkout session created successfully.',
            'session_id' => $result['session_id'],
            'checkout_url' => $result['checkout_url'],
            'inscription' => $result['inscription'],
        ]);
    }

    public function paymentSuccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'string'],
        ]);

        $result = $this->studentService->confirmStripePayment($validated['session_id']);

        return response()->json([
            'message' => $result['updated']
                ? 'Payment confirmed and inscription updated.'
                : 'Payment was already confirmed for this inscription.',
            'inscription' => $result['inscription'],
        ]);
    }

    public function paymentCancel(): JsonResponse
    {
        return response()->json([
            'message' => 'Payment canceled.',
        ]);
    }
}
