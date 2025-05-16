<?php

declare(strict_types=1);

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface RepositoryContract
{
    public function save(Model $model): void;
}
