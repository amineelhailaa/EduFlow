<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\StudentPaymentService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService,
        private StudentPaymentService $studentPaymentService,
    ) {
    }

    #[OA\Post(
        path: '/api/v1/courses/{course}/join',
        summary: 'Join a course or start checkout flow',
        tags: ['Students'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Joined course'),
            new OA\Response(response: 200, description: 'Already enrolled or checkout required'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/courses/{course}/checkout',
        summary: 'Create checkout session for course payment',
        tags: ['Students'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Checkout session created or already paid'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

    #[OA\Get(
        path: '/api/v1/payments/success',
        summary: 'Confirm successful payment by session id',
        tags: ['Students'],
        parameters: [
            new OA\Parameter(name: 'session_id', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Payment confirmed'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

    #[OA\Get(
        path: '/api/v1/payments/cancel',
        summary: 'Handle canceled payment',
        tags: ['Students'],
        responses: [
            new OA\Response(response: 200, description: 'Payment canceled'),
        ]
    )]
    public function paymentCancel(): JsonResponse
    {
        return response()->json([
            'message' => 'Payment canceled.',
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/courses/{course}/leave',
        summary: 'Leave a course',
        tags: ['Students'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'course', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Left course or not enrolled'),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Course not found'),
        ]
    )]
    public function leaveCourse(Request $request, Course $course): JsonResponse
    {
        $result = $this->studentService->leaveCourse($request->user('api'), $course->id);

        return response()->json([
            'message' => $result['removed']
                ? 'Left course successfully.'
                : 'User is not enrolled in this course.',
        ]);
    }
}
