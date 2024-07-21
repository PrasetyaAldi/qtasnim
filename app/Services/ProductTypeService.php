<?php

namespace App\Services;

use App\Models\ProductType;

class ProductTypeService
{
    protected $model = ProductType::class;

    public function indexProductType(array $filters = [], array $orderBy = ['created_at' => 'desc'], int $limit = 10)
    {
        $query = $this->model::query();

        // Jika filter tidak kosong
        if (!empty($filters)) {
            // Jika filter memiliki key-value pair
            $hasKey = array_keys($filters) !== range(0, count($filters) - 1);

            if ($hasKey) {
                foreach ($filters as $key => $value) {
                    if (strpos($key, '.') !== false) {
                        $relations = explode('.', $key);
                        $column = array_pop($relations); // Ambil kolom terakhir
                        $relations = implode('.', $relations); // Gabungkan kembali relasi yang tersisa
                        $query->whereHas($relations, function ($q) use ($relations, $column, $value) {
                            $q->where($column, 'ilike', "%" . $value . "%");
                        });
                    } else {
                        $query->where($key, 'ilike', "%" . $value . "%");
                    }
                }
            } else {
                // Jika filter hanya berisi nilai tanpa key
                $columns = (new $this->model)->getFillable();
                $query->where(function ($query) use ($columns, $filters) {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'ilike', "%" . $filters[0] . "%");
                    }
                });
            }
        }

        // Jika ada key dalam array orderBy, tambahkan kondisi orderBy
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $value) {
                if (strpos($key, '.')) {
                    [$relation, $column] = explode('.', $key);
                    $query->whereHas($relation, function ($q) use ($column, $value) {
                        $q->orderBy($column, $value);
                    });
                } else {
                    $query->orderBy($key, $value);
                }
            }
        }

        $data = $query->paginate($limit);

        return $data;
    }

    public function saveProductType(array $data, int $id = null)
    {
        $productType = new $this->model;

        // hanya jika ada id
        if (!is_null($id)) {
            $productType = $this->showProductType($id);
        }

        $productType->fill($data);

        $productType->save();

        return $productType;
    }

    public function showProductType(int $id)
    {
        return $this->model::findOrFail($id);
    }

    public function destroyProductType(int $id)
    {
        $productType = $this->showProductType($id);

        $productType->delete();

        return $productType;
    }
}
