<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $model = Transaction::class;

    public function indexTransaction(array $filters = [], array $orderBy = ['created_at' => 'desc'], int $limit = 10)
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
                        // hanya jika key mengandung kata '_date'
                        if (strpos($key, '_date')) {
                            $filterDate = Carbon::parse($value);
                            $query->whereDate($key, $filterDate);
                        } else {
                            $query->where($key, 'ilike', "%" . $value . "%");
                        }
                    }
                }
            } else {
                // Jika filter hanya berisi nilai tanpa key
                $columns = (new $this->model)->getFillable();
                $query->where(function ($query) use ($columns, $filters) {
                    foreach ($columns as $column) {
                        // hanya jika column mengandung kata '_date'
                        if (strpos($column, '_date')) {
                            $filterDate = Carbon::parse($filters[0]);
                            $query->orWhereDate($column, $filterDate);
                        } else {
                            $query->orWhere($column, 'ilike', "%" . $filters[0] . "%");
                        }
                    }
                    $query->orWhereHas('product', function ($query) use ($filters) {
                        $query->where('name', 'ilike', "%" . $filters[0] . "%");
                    });
                    $query->orWhereHas('Product.ProductType', function ($query) use ($filters) {
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

    public function saveTransaction(array $data)
    {
        $transaction = new $this->model;

        DB::beginTransaction();

        // get stock of product and lock table stock
        $stock = Stock::where('product_id', $data['product_id'])->lockForUpdate()->first();

        // hanya jika stock tidak tersedia dan jumlah yang diminta melebihi stock
        if (!$stock || $stock->quantity < $data['quantity']) {
            DB::rollBack();
            throw new \Exception('Stock tidak tersedia');
        }

        $stock->update([
            'quantity' => $stock->quantity - $data['quantity']
        ]);

        $transaction->fill($data);

        $transaction->save();

        DB::commit();

        return $transaction;
    }

    public function updateTransaction(array $data, $id)
    {
        // get transaction
        $transaction = $this->showTransaction($id);

        DB::beginTransaction();

        // update stock lama
        $oldStock = Stock::where('product_id', $transaction->product_id)->lockForUpdate()->first();
        $oldStock->update([
            'quantity' => $oldStock->quantity + $transaction->quantity
        ]);

        // ambil stock dari barang yang baru
        $stock = Stock::where('product_id', $data['product_id'])->lockForUpdate()->first();

        // hanya jika stock tidak tersedia dan jumlah yang diminta melebihi stock
        if (!$stock || $stock->quantity < $data['quantity']) {
            DB::rollBack();
            throw new \Exception('Stock tidak tersedia');
        }

        $stock->update([
            'quantity' => $stock->quantity - $data['quantity']
        ]);

        $transaction->fill($data);

        $transaction->save();

        DB::commit();

        return $transaction;
    }

    public function showTransaction(int $id)
    {
        return $this->model::findOrFail($id);
    }

    public function destroyTransaction(int $id)
    {
        $transaction = $this->showTransaction($id);

        DB::beginTransaction();

        // update stock
        $stock = Stock::where('product_id', $transaction->product_id)->lockForUpdate()->first();

        $stock->update([
            'quantity' => $stock->quantity + $transaction->quantity
        ]);

        $transaction->delete();

        DB::commit();

        return $transaction;
    }

    public function compareSales(array $filters = [], array $orderBy = ['total_quantity' => 'desc'], int $limit = 10)
    {
        $filters['start_date'] = $filters['start_date'] ?? Carbon::now()->subMonth()->format('Y-m-d'); // defaultnya adalah satu bulan sebelum sekarang
        $filters['end_date'] = $filters['end_date'] ?? Carbon::now()->format('Y-m-d'); // defaultnya adalah sekarang

        // get product type and sum quantity of transaction
        $query = $this->model::query();

        $query->select('product_types.name', DB::raw('SUM(transactions.quantity) as total_quantity'))
            ->join('products', 'products.id', '=', 'transactions.product_id')
            ->join('product_types', 'product_types.id', '=', 'products.product_type_id')
            ->groupBy('product_types.name');

        $query->whereBetween('transactions.transaction_date', [$filters['start_date'], $filters['end_date']]);

        // order by
        foreach ($orderBy as $key => $value) {
            $query->orderBy($key, $value);
        }

        $data = $query->paginate($limit);

        return $data;
    }
}
