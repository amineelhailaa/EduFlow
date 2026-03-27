<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInterestRequest;
use App\Http\Requests\Api\V1\UpdateInterestRequest;
use App\Models\Interest;
use App\Services\InterestService;
use Illuminate\Http\JsonResponse;

class InterestController extends Controller
{
    public function __construct(
        private InterestService $interestService,
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json($this->interestService->all());
    }

    public function store(StoreInterestRequest $request): JsonResponse
    {
        $interest = $this->interestService->create($request->validated());

        return response()->json([
            'message' => 'Interest created successfully.',
            'interest' => $interest,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->interestService->findOrFail($id));
    }

    public function update(UpdateInterestRequest $request, Interest $interest): JsonResponse
    {
        $interest = $this->interestService->update($interest, $request->validated());

        return response()->json([
            'message' => 'Interest updated successfully.',
            'interest' => $interest,
        ]);
    }

    public function destroy(Interest $interest): JsonResponse
    {
        $this->interestService->delete($interest);

        return response()->json([
            'message' => 'Interest deleted successfully.',
        ]);
    }
}
