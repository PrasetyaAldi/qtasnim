<?php

namespace App\Services;

use App\Models\Stock;

class StockService
{
    protected $model = Stock::class;

    public function indexStock(array $filters = [], array $orderBy = ['created_at' => 'desc'], int $limit = 10)
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
                    $query->orWhereHas('product', function ($query) use ($filters) {
                        $query->where('name', 'ilike', "%" . $filters[0] . "%");
                    });
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

    public function saveStock(array $data, int $id = null)
    {
        $stock = new $this->model;

        // hanya jika ada id
        if (!is_null($id)) {
            $stock = $this->showStock($id);
        }

        $stock->fill($data);

        $stock->save();

        return $stock;
    }

    public function showStock(int $id)
    {
        return $this->model::findOrFail($id);
    }

    public function destroyStock(int $id)
    {
        $stock = $this->showStock($id);

        $stock->delete();

        return $stock;
    }
}
