<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutService
{
    public function createCheckoutSession(Course $course, User $user): array
    {
        $this->configure();

        $appUrl = rtrim(config('app.url', ''), '/');

        $session = Session::create([
            'mode' => 'payment',
            'success_url' => "{$appUrl}/api/v1/payments/success?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => "{$appUrl}/api/v1/payments/cancel?session_id={CHECKOUT_SESSION_ID}",
            'customer_email' => $user->email,
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) $course->price,
                    'product_data' => [
                        'name' => "Course #{$course->id}",
                    ],
                ],
            ]],
            'metadata' => [
                'user_id' => (string) $user->id,
                'course_id' => (string) $course->id,
            ],
        ]);

        return [
            'id' => $session->id,
            'url' => $session->url,
        ];
    }

    public function retrieveCheckoutSession(string $sessionId): array
    {
        $this->configure();

        $session = Session::retrieve($sessionId);

        return [
            'id' => $session->id,
            'payment_status' => $session->payment_status,
            'metadata' => $session->metadata?->toArray() ?? [],
        ];
    }

    private function configure(): void
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            throw new RuntimeException('Stripe secret key is not configured.');
        }

        Stripe::setApiKey($secret);
    }
}
