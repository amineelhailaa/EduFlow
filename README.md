# Course API

A Laravel 12 REST API for course management, student enrollment, teacher dashboards, favorites, and interest-based recommendations.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![JWT](https://img.shields.io/badge/Auth-JWT-000000)
![Swagger](https://img.shields.io/badge/Docs-L5--Swagger-85EA2D)

## Features

- JWT auth: register, login, forgot/reset password
- Student interests saved on registration (`student_interests` pivot)
- Course and interest CRUD
- Student flows: join course, checkout, payment confirm/cancel, leave course
- Favorites: add/remove favorite courses and list favorites
- Personalized endpoint: courses matching student interests
- Teacher endpoints:
  - enrolled students in teacher courses
  - groups in teacher courses
  - participants in each group
- Swagger docs using `darkaonline/l5-swagger`

## Architecture

This project follows a layered pattern:

- `Controller` for HTTP layer
- `Service` for business logic
- `Repository` for data access
- Contracts (interfaces) bound in `AppServiceProvider`

## Quick Start

### 1. Install

```bash
composer run setup
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
```

Default DB in `.env.example` is SQLite.

### 3. Run locally

```bash
composer run dev
```

Or minimal server:

```bash
php artisan serve
```

## API Documentation (Swagger)

- UI: `GET /api/documentation`
- Generate docs:

```bash
php artisan l5-swagger:generate
```

## API Overview

Base API prefix: `/api/v1` (auth endpoints also exist under `/api`).

### Auth
- `POST /register`
- `POST /login`
- `POST /forgot-password`
- `POST /reset-password`

### Courses
- `GET /courses`
- `POST /courses`
- `GET /courses/{course}`
- `PUT|PATCH /courses/{course}`
- `DELETE /courses/{course}`
- `GET /courses/favorites` (student)
- `GET /courses/matching-interests` (student)

### Favorites
- `POST /courses/{course}/favorites` (student)
- `DELETE /courses/{course}/favorites` (student)

### Student
- `POST /courses/{course}/join`
- `POST /courses/{course}/checkout`
- `DELETE /courses/{course}/leave`
- `GET /payments/success`
- `GET /payments/cancel`

### Teacher
- `GET /teacher/courses/students`
- `GET /teacher/courses/groups`
- `GET /teacher/courses/groups/participants`

## Testing

Run full test suite:

```bash
composer test
```

Run a specific file:

```bash
php artisan test tests/Feature/FavoriteCourseApiTest.php
```

## Tech Stack

- Laravel 12
- PHP 8.2+
- tymon/jwt-auth
- darkaonline/l5-swagger
- Stripe PHP SDK
- PHPUnit 11

## License

MIT
