<?php

namespace App\Http\Controllers;

use App\Services\ProductTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['title'] = 'Jenis Barang';
        $data['resource'] = 'product-types';
        $productService = new ProductTypeService;
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

        $data['data'] = $productService->indexProductType($filters, $orderBy);

        $data['columns'] = [
            ['col' => 'name', 'label' => 'Nama Jenis Barang', 'filter' => true]
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
        $data['title'] = 'Tambah Barang';

        $data['resource'] = 'products';

        $data['columns'] = [
            ['col' => 'name', 'label' => 'Nama Jenis Barang', 'type' => 'text', 'placeholder' => 'Masukkan Nama Jenis Barang', 'required' => true],
        ];

        $data['action'] = route('product-types.store');
        $data['method'] = 'POST';
        $data['edit'] = true;
        return view('templates.form', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ProductTypeService $productTypeService)
    {
        $data = $request->all();

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100'
        ]);

        // hanya jika ada error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $productTypeService->saveProductType($data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data. ' . $e->getMessage());
        }

        return redirect()->route('product-types.index')->with('success', 'Berhasil menyimpan data.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id, ProductTypeService $productTypeService)
    {
        $data['title'] = 'Detail Jenis Barang';
        $data['resource'] = 'product-types';

        try {
            $productType = $productTypeService->showProductType($id);

            $data['columns'] = [
                ['col' => 'name', 'label' => 'Nama Jenis Barang', 'type' => 'text', 'value' => $productType->name, 'placeholder' => 'Masukkan Nama Barang', 'required' => true],
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
    public function edit($id, ProductTypeService $productTypeService)
    {
        $data['title'] = 'Perbarui Jenis Barang';
        $data['resource'] = 'product-types';

        try {
            $productType = $productTypeService->showProductType($id);

            $data['columns'] = [
                ['col' => 'name', 'label' => 'Nama Jenis Barang', 'type' => 'text', 'value' => $productType->name, 'placeholder' => 'Masukkan Nama Barang', 'required' => true],
            ];
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menampilkan data. ' . $e->getMessage());
        }

        $data['action'] = route('product-types.update', $id);
        $data['method'] = 'PUT';
        $data['edit'] = true;

        return view('templates.form', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, ProductTypeService $productTypeService)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:100'
        ]);

        // hanya jika ada error
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $productTypeService->saveProductType($data, $id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui data. ' . $e->getMessage());
        }

        return redirect()->route('product-types.index')->with('success', 'Berhasil memperbarui data.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, ProductTypeService $productTypeService)
    {
        try {
            $productTypeService->destroyProductType($id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data. ' . $e->getMessage());
        }

        return redirect()->route('product-types.index')->with('success', 'Berhasil Menghapus data.');
    }
}
