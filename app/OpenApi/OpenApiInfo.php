<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Course API',
    description: 'API documentation for authentication, courses, interests, favorites, students, and teachers.'
)]
#[OA\Server(
    url: '/',
    description: 'Application server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class OpenApiInfo
{
}
