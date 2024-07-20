<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ProductService;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, TransactionService $transactionService)
    {
        $data['title'] = 'Transaksi';
        $data['resource'] = 'transactions';
        $orderBy = $request->has('order_by') ? [$request->order_by => $request->order_type] : ['created_at' => 'desc'];

        $filters = [];

        if ($request->has('value')) {
            $value = $request->input('value');
            $key = $request->input('key');

            if (!empty($key)) {
                $filters[$key] = $value;
            } else {
                $filters[] = $value;
            }
        }

        try {
            $data['data'] = $transactionService->indexTransaction($filters, $orderBy);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['columns'] = [
            ['col' => 'product.name', 'label' => 'Nama Barang', 'filter' => true],
            ['col' => 'product.productType.name', 'label' => 'Nama Jenis Barang', 'filter' => true],
            ['col' => 'quantity', 'label' => 'Jumlah Barang', 'filter' => true],
            ['col' => 'transaction_date', 'label' => 'Tanggal Transaksi', 'type' => 'date', 'filter' => true]
        ];

        $data['can_create'] = true;
        $data['buttonLinear'] = [
            ['type' => 'anchor', 'icon' => 'fa-solid fa-pen-to-square', 'state' => 'primary', 'size' => 'sm', 'tooltip' => 'Edit', 'action' => 'edit', 'resource' => 'products'],
            ['type' => 'button', 'icon' => 'fa-solid fa-trash', 'state' => 'danger', 'size' => 'sm', 'tooltip' => 'Hapus', 'action' => 'delete', 'resource' => 'products'],
        ];

        return view('templates.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['title'] = 'Tambah Transaksi';

        $data['resource'] = 'transactions';
        $productService = new ProductService;
        $options = $productService->listProduct();

        $data['columns'] = [
            ['col' => 'product_id', 'label' => 'Nama Barang', 'type' => 'select', 'options' => ['' => '--- Pilih Barang ---'] + $options->toArray(), 'required' => true],
            ['col' => 'quantity', 'label' => 'Jumlah Barang', 'type' => 'number', 'placeholder' => 'Masukkan Jumlah Barang', 'required' => true],
            ['col' => 'transaction_date', 'label' => 'Tanggal Transaksi', 'type' => 'date', 'required' => true]
        ];

        $data['action'] = route('transactions.store');
        $data['method'] = 'POST';
        $data['edit'] = true;
        return view('templates.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TransactionService $transactionService)
    {
        $data = $request->all();
        try {
            $transactionService->saveTransaction($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage())->withInput();
        }

        return redirect()->route('transactions.index')->with('success', 'Berhasil menyimpan data.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id, TransactionService $transactionService)
    {
        $data['title'] = 'Detail Transaksi';
        $data['resource'] = 'transactions';
        $productService = new ProductService;

        try {
            $transaction = $transactionService->showTransaction($id);
            $options = $productService->listProduct();

            $data['columns'] = [
                ['col' => 'product_id', 'label' => 'Nama Barang', 'type' => 'select', 'value' => $transaction->product_id, 'options' => ['' => '--- Pilih Barang ---'] + $options->toArray(), 'required' => true],
                ['col' => 'quantity', 'label' => 'Jumlah Barang', 'type' => 'number', 'value' => $transaction->quantity, 'placeholder' => 'Masukkan Jumlah Barang', 'required' => true],
                ['col' => 'transaction_date', 'label' => 'Tanggal Transaksi', 'type' => 'date', 'value' => $transaction->transaction_date, 'required' => true],
            ];
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['action'] = null;
        $data['method'] = null;
        $data['edit'] = false;
        return view('templates.form', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, TransactionService $transactionService)
    {
        $data['title'] = 'Perbarui Transaksi';
        $data['resource'] = 'transactions';
        $productService = new ProductService;

        try {
            $transaction = $transactionService->showTransaction($id);
            $options = $productService->listProduct();

            $data['columns'] = [
                ['col' => 'product_id', 'label' => 'Nama Barang', 'type' => 'select', 'value' => $transaction->product_id, 'options' => ['' => '--- Pilih Barang ---'] + $options->toArray(), 'required' => true],
                ['col' => 'quantity', 'label' => 'Jumlah Barang', 'type' => 'number', 'value' => $transaction->quantity, 'placeholder' => 'Masukkan Jumlah Barang', 'required' => true],
                ['col' => 'transaction_date', 'label' => 'Tanggal Transaksi', 'type' => 'date', 'value' => $transaction->transaction_date, 'required' => true],
            ];
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['action'] = route('transactions.update', $id);
        $data['method'] = 'PUT';
        $data['edit'] = true;

        return view('templates.form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, TransactionService $transactionService)
    {
        $data = $request->all();

        try {
            $transactionService->updateTransaction($data, $id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data. ' . $e->getMessage())->withInput();
        }

        return redirect()->route('transactions.index')->with('success', 'Berhasil memperbarui data.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, TransactionService $transactionService)
    {
        try {
            $transactionService->destroyTransaction($id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('transactions.index')->with('success', 'Berhasil Menghapus data.');
    }

    /**
     * Compare total sales by product type 
     */
    public function compare(Request $request, TransactionService $transactionService)
    {
        $dataCompare = $request->all();

        $data['title'] = 'Perbandingan Penjualan';
        $data['resource'] = 'transactions';
        $data['can_create'] = false;

        $data['columns'] = [
            ['col' => 'name', 'label' => 'Nama Jenis Barang'],
            ['col' => 'total_quantity', 'label' => 'Total Barang Terjual'],
        ];

        $orderBy = $request->has('order_by') ? [$request->order_by => $request->order_type] : ['total_quantity' => 'desc'];

        $filters = [
            'start_date' => $request->start_date ?? Carbon::now()->subMonth()->format('Y-m-d'),
            'end_date' => $request->end_date ?? Carbon::now()->format('Y-m-d')
        ];

        try {
            $data['data'] = $transactionService->compareSales($filters, $orderBy);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['is_compare'] = true;
        $data['index'] = route('transactions.compare');

        return view('templates.index', $data);
    }
}
