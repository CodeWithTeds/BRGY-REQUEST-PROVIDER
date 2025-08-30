<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 *  @template TModel of Model
 */
abstract class Repository
{
    /**
     * construction property promotion
     * avoid boiler plate
     */
    public function __construct(protected Model $model) {}

    public function model(): Model
    {
        return $this->model;
    }

    public function query(): Builder
    {
        return $this->model->query();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array|string $relations = []): LengthAwarePaginator
    {
        return $this->query()->with($relations)->paginate($perPage, $columns);
    }

    public function find(int|string $id, array $columns = ['*'], array|string $relations = []): ?Model
    {
        return $this->query()->with($relations)->find($id, $columns);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function updateOrCreate(array $attributes, array $values): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function transaction(\Closure $callback)
    {
        return DB::transaction($callback);
    }

    public function createWithRelations(array $data, array $relations = []): Model
    {
        return DB::transaction(function () use ($data, $relations) {
            $model = $this->create($data);
            foreach ($relations as $relation => $relationData) {
                if (method_exists($model, $relation)) {
                    $model->$relation()->create($relationData);
                }
            }
            return $model;
        });
    }

    public function updateOrCreateWithRelations(array $attributes, array $values, array $relations = []): Model
    {
        return DB::transaction(function () use ($attributes, $values, $relations) {
            $model = $this->updateOrCreate($attributes, $values);
            foreach ($relations as $relation => $relationData) {
                if (method_exists($model, $relation)) {
                    $model->$relation()->updateOrCreate(
                        array_intersect_key($relationData, $attributes),
                        array_diff_key($relationData, $attributes)
                    );
                }
            }
            return $model;
        });
    }


}
