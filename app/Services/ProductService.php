<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductType;

class ProductService
{
    protected $model = Product::class;

    public function indexProduct(array $filters = [], array $orderBy = ['created_at' => 'desc'], int $limit = 10)
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
                    $query->orWhereHas('productType', function ($query) use ($filters) {
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

    public function saveProduct(array $data, int $id = null)
    {
        $product = new $this->model;

        // hanya jika ada id
        if (!is_null($id)) {
            $product = $this->showProduct($id);
        }

        $product->fill($data);

        $product->save();

        return $product;
    }

    public function showProduct(int $id)
    {
        return $this->model::findOrFail($id);
    }

    public function destroyProduct(int $id)
    {
        $product = $this->showProduct($id);

        $product->delete();

        return $product;
    }

    public function listProduct()
    {
        return $this->model::all()->pluck('name', 'id');
    }

    public function listProductType()
    {
        return ProductType::all()->pluck('name', 'id');
    }
}
