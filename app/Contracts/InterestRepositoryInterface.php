<?php

namespace App\Contracts;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Collection;

interface InterestRepositoryInterface
{
    public function all(): Collection;

    public function findOrFail(int $id): Interest;

    public function create(array $attributes): Interest;

    public function update(Interest $interest, array $attributes): Interest;

    public function delete(Interest $interest): bool;
}

