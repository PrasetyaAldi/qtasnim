<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['title'] = 'Barang';
        $data['resource'] = 'products';
        $productService = new ProductService;
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

        $data['data'] = $productService->indexProduct($filters, $orderBy);

        $data['columns'] = [
            ['col' => 'productType.name', 'label' => 'Jenis Barang', 'filter' => true],
            ['col' => 'name', 'label' => 'Nama Barang', 'filter' => true]
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
    public function create(ProductService $productService)
    {
        $data['title'] = 'Tambah Barang';

        $data['resource'] = 'products';

        $options = $productService->listProductType();

        $data['columns'] = [
            ['col' => 'product_type_id', 'label' => 'Jenis Barang', 'type' => 'select', 'options' => ['' => '--- Pilih Jenis Barang ---'] + $options->toArray(), 'value' => 'name', 'key' => 'id', 'required' => true],
            ['col' => 'name', 'label' => 'Nama Barang', 'type' => 'text', 'placeholder' => 'Masukkan Nama Barang', 'required' => true],
        ];

        $data['action'] = route('products.store');
        $data['method'] = 'POST';
        $data['edit'] = true;
        return view('templates.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProductService $productService)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|max:100'
        ]);

        // hanya jika ada error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $productService->saveProduct($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Berhasil menyimpan data.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id, ProductService $productService)
    {
        $data['title'] = 'Detail Barang';
        $data['resource'] = 'products';

        try {
            $product = $productService->showProduct($id);
            $options = $productService->listProductType();

            $data['columns'] = [
                ['col' => 'product_type_id', 'label' => 'Jenis Barang', 'type' => 'select', 'value' => $product->product_type_id, 'options' => ['' => '--- Pilih Jenis Barang ---'] + $options->toArray(), 'required' => true],
                ['col' => 'name', 'label' => 'Nama Barang', 'type' => 'text', 'value' => $product->name, 'placeholder' => 'Masukkan Nama Barang', 'required' => true],
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
    public function edit($id, ProductService $productService)
    {
        $data['title'] = 'Perbarui Barang';
        $data['resource'] = 'products';

        try {
            $product = $productService->showProduct($id);
            $options = $productService->listProductType();

            $data['columns'] = [
                ['col' => 'product_type_id', 'label' => 'Jenis Barang', 'type' => 'select', 'value' => $product->product_type_id, 'options' => ['' => '--- Pilih Jenis Barang ---'] + $options->toArray(), 'required' => true],
                ['col' => 'name', 'label' => 'Nama Barang', 'type' => 'text', 'value' => $product->name, 'placeholder' => 'Masukkan Nama Barang', 'required' => true],
            ];
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['action'] = route('products.update', $id);
        $data['method'] = 'PUT';
        $data['edit'] = true;

        return view('templates.form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, ProductService $productService)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|max:100'
        ]);

        // hanya jika ada error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $productService->saveProduct($data, $id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data. ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Berhasil memperbarui data.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, ProductService $productService)
    {
        try {
            $productService->destroyProduct($id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Berhasil Menghapus data.');
    }
}
