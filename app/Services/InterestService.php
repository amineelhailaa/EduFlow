<?php

namespace App\Services;

use App\Contracts\InterestRepositoryInterface;
use App\Models\Interest;
use Illuminate\Database\Eloquent\Collection;

class InterestService
{
    public function __construct(
        private InterestRepositoryInterface $interestRepository,
    ) {
    }

    public function all(): Collection
    {
        return $this->interestRepository->all();
    }

    public function findOrFail(int $id): Interest
    {
        return $this->interestRepository->findOrFail($id);
    }

    public function create(array $attributes): Interest
    {
        return $this->interestRepository->create($attributes);
    }

    public function update(Interest $interest, array $attributes): Interest
    {
        return $this->interestRepository->update($interest, $attributes);
    }

    public function delete(Interest $interest): bool
    {
        return $this->interestRepository->delete($interest);
    }
}

