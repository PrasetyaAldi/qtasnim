<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, StockService $stockService)
    {

        $data['title'] = 'Stok';
        $data['resource'] = 'stocks';
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

        $data['data'] = $stockService->indexStock($filters, $orderBy);

        $data['columns'] = [
            ['col' => 'product.name', 'label' => 'Nama Barang', 'filter' => true],
            ['col' => 'quantity', 'label' => 'Stok', 'filter' => true]
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
        $data['title'] = 'Tambah Stok';

        $data['resource'] = 'stocks';

        $productService = new ProductService;
        $options = $productService->listProduct();

        $data['columns'] = [
            ['col' => 'product_id', 'label' => 'Nama Barang', 'type' => 'select', 'options' => ['' => '--- Pilih Barang ---'] + $options->toArray(), 'required' => true],
            ['col' => 'quantity', 'label' => 'Stock', 'type' => 'number', 'placeholder' => 'Masukkan Stok Barang', 'required' => true],
        ];

        $data['action'] = route('stocks.store');
        $data['method'] = 'POST';
        $data['edit'] = true;

        return view('templates.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, StockService $stockService)
    {
        $data = $request->all();

        try {
            $stockService->saveStock($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan data. ' . $e->getMessage());
        }

        return redirect()->route('stocks.index')->with('success', 'Berhasil menambahkan data.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id, StockService $stockService)
    {
        $data['title'] = 'Detail Stok';
        $data['resource'] = 'stocks';
        $productService = new ProductService;

        try {
            $stock = $stockService->showStock($id);
            $options = $productService->listProduct();

            $data['columns'] = [
                ['col' => 'product_id', 'label' => 'Nama Barang', 'type' => 'select', 'options' => ['' => '--- Pilih Barang ---'] + $options->toArray(), 'required' => true, 'value' => $stock->product_id],
                ['col' => 'quantity', 'label' => 'Stock', 'type' => 'number', 'placeholder' => 'Masukkan Stok Barang', 'required' => true, 'value' => $stock->quantity],
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
    public function edit($id, StockService $stockService)
    {
        $data['title'] = 'Perbarui Stock';
        $data['resource'] = 'stocks';
        $productService = new ProductService;

        try {
            $stock = $stockService->showStock($id);
            $options = $productService->listProduct();

            $data['columns'] = [
                ['col' => 'product_id', 'label' => 'Nama Barang', 'type' => 'select', 'options' => ['' => '--- Pilih Barang ---'] + $options->toArray(), 'required' => true, 'value' => $stock->product_id],
                ['col' => 'quantity', 'label' => 'Stock', 'type' => 'number', 'placeholder' => 'Masukkan Stok Barang', 'required' => true, 'value' => $stock->quantity],
            ];
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['action'] = route('stocks.update', $id);
        $data['method'] = 'PUT';
        $data['edit'] = true;

        return view('templates.form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, StockService $stockService)
    {
        $data = $request->all();

        try {
            $stockService->saveStock($data, $id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data. ' . $e->getMessage());
        }

        return redirect()->route('stocks.index')->with('success', 'Berhasil memperbarui data.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, StockService $stockService)
    {
        try {
            $stockService->destroyStock($id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('stocks.index')->with('success', 'Berhasil Menghapus data.');
    }
}
