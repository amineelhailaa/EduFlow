<?php

namespace App\Repositories;

use App\Contracts\InterestRepositoryInterface;
use App\Models\Interest;
use Illuminate\Database\Eloquent\Collection;

class InterestRepository implements InterestRepositoryInterface
{
    public function all(): Collection
    {
        return Interest::query()->get();
    }

    public function findOrFail(int $id): Interest
    {
        return Interest::query()->findOrFail($id);
    }

    public function create(array $attributes): Interest
    {
        return Interest::query()->create($attributes);
    }

    public function update(Interest $interest, array $attributes): Interest
    {
        $interest->update($attributes);

        return $interest->refresh();
    }

    public function delete(Interest $interest): bool
    {
        return (bool) $interest->delete();
    }
}

