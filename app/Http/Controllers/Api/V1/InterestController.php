<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInterestRequest;
use App\Http\Requests\Api\V1\UpdateInterestRequest;
use App\Models\Interest;
use App\Services\InterestService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class InterestController extends Controller
{
    public function __construct(
        private InterestService $interestService,
    ) {
    }

    #[OA\Get(
        path: '/api/v1/interests',
        summary: 'List all interests',
        tags: ['Interests'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Interests list',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(type: 'object'),
                    example: [[
                        'id' => 1,
                        'name' => 'Web Development',
                    ]]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json($this->interestService->all());
    }

    #[OA\Post(
        path: '/api/v1/interests',
        summary: 'Create an interest',
        tags: ['Interests'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Web Development'),
                ],
                example: [
                    'name' => 'Web Development',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Interest created',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Interest created successfully.',
                        'interest' => [
                            'id' => 1,
                            'name' => 'Web Development',
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'name' => ['The name field is required.'],
                        ],
                    ]
                )
            ),
        ]
    )]
    public function store(StoreInterestRequest $request): JsonResponse
    {
        $interest = $this->interestService->create($request->validated());

        return response()->json([
            'message' => 'Interest created successfully.',
            'interest' => $interest,
        ], 201);
    }

    #[OA\Get(
        path: '/api/v1/interests/{interest}',
        summary: 'Show one interest',
        tags: ['Interests'],
        parameters: [
            new OA\Parameter(name: 'interest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Interest details',
                content: new OA\JsonContent(
                    example: [
                        'id' => 1,
                        'name' => 'Web Development',
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Interest not found',
                content: new OA\JsonContent(
                    example: ['message' => 'No query results for model [App\\Models\\Interest] 999']
                )
            ),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        return response()->json($this->interestService->findOrFail($id));
    }

    #[OA\Put(
        path: '/api/v1/interests/{interest}',
        summary: 'Update an interest',
        tags: ['Interests'],
        parameters: [
            new OA\Parameter(name: 'interest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Data Science'),
                ],
                example: [
                    'name' => 'Data Science',
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Interest updated',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'Interest updated successfully.',
                        'interest' => [
                            'id' => 1,
                            'name' => 'Data Science',
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(
                    example: [
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'name' => ['The name field is required.'],
                        ],
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Interest not found',
                content: new OA\JsonContent(
                    example: ['message' => 'No query results for model [App\\Models\\Interest] 999']
                )
            ),
        ]
    )]
    public function update(UpdateInterestRequest $request, Interest $interest): JsonResponse
    {
        $interest = $this->interestService->update($interest, $request->validated());

        return response()->json([
            'message' => 'Interest updated successfully.',
            'interest' => $interest,
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/interests/{interest}',
        summary: 'Delete an interest',
        tags: ['Interests'],
        parameters: [
            new OA\Parameter(name: 'interest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Interest deleted',
                content: new OA\JsonContent(
                    example: ['message' => 'Interest deleted successfully.']
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Interest not found',
                content: new OA\JsonContent(
                    example: ['message' => 'No query results for model [App\\Models\\Interest] 999']
                )
            ),
        ]
    )]
    public function destroy(Interest $interest): JsonResponse
    {
        $this->interestService->delete($interest);

        return response()->json([
            'message' => 'Interest deleted successfully.',
        ]);
    }
}
