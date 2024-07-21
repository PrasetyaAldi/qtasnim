@extends('layouts.app')

@section('title', $title ?? 'Home')

@section('actions')
    <div>
        @if (isset($can_create) && $can_create)
            <a href="{{ route("$resource.create") }}" class="btn btn-primary fw-medium"><i class="fas fa-plus"></i> Tambah
                {{ $title }}</a>
        @endif
    </div>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- tambahkan filter --}}
            <form action="{{ $index ?? route($resource . '.index') }}" method="get" class="mb-3">
                <div class="row justify-content-end align-items-center">
                    @if (!isset($is_compare))
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="key" class="form-control input-sm">
                                        <option value="">--- Semua ---</option>
                                        @foreach ($columns as $column)
                                            @if (!isset($column['filter']) || $column['filter'] === false)
                                                @continue
                                            @endif
                                            <option value="{{ $column['col'] }}"
                                                {{ request('key') == $column['col'] ? 'selected' : '' }}>
                                                {{ $column['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Cari {{ $title }}"
                                            name="value" value="{{ request('value') }}">
                                        <button class="btn btn-success" type="submit"><i
                                                class="fas fa-search"></i></button>
                                        <a href="{{ route($resource . '.index') }}" class="btn btn-primary"><i
                                                class="fas fa-rotate"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="col-md-6">
                            <label for="start_date" class="fw-medium">Tanggal Awal</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date') ?? Carbon::now()->subMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-5">
                            <label for="end_date" class="fw-medium">Tanggal Awal</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date') ?? Carbon::now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-1 mx-auto">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                        <hr class="mt-4">
                    @endif
                </div>
            </form>
            <table class="table">
                <thead>
                    <th>No.</th>
                    @foreach ($columns as $column)
                        <th id="{{ $column['col'] }}" class="cell_sorting">{{ $column['label'] }}
                            @php
                                $currentOrderBy = isset($_GET['order_by']) ? $_GET['order_by'] : '';
                                $currentOrderType = isset($_GET['order_type']) ? $_GET['order_type'] : '';
                            @endphp
                            @if ($currentOrderBy === $column['col'])
                                @if ($currentOrderType === 'asc')
                                    <i class="fas fa-sort-up"></i>
                                @else
                                    <i class="fas fa-sort-down"></i>
                                @endif
                            @endif
                        </th>
                    @endforeach
                    @if (!empty($buttonLinear) || !isset($is_compare))
                        <th>Aksi</th>
                    @endif
                </thead>
                <tbody>
                    @forelse ($data as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            @foreach ($columns as $column)
                                @php
                                    $value =
                                        strpos($column['col'], '.') !== false
                                            ? data_get($item, $column['col'])
                                            : $item->{$column['col']};

                                    // hanya jika type data adalah date
                                    if (isset($column['type']) && $column['type'] === 'date') {
                                        $value = Carbon::parse($value)->format('d F Y');
                                    }
                                @endphp
                                <td>{{ $value }}</td>
                            @endforeach
                            @if (!isset($is_compare) || !empty($buttonLinear))
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route($resource . '.edit', $item->id) }}"
                                            class="btn btn-primary btn-sm "><i class="fas fa-pen-to-square"></i></a>

                                        <form action="{{ route($resource . '.destroy', $item->id) }}" method="post"
                                            class="form-delete ms-2" id="form-delete-{{ $item->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-button :type="'submit'" :icon="'fas fa-trash'" :state="'danger'"
                                                :size="'sm'"></x-button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + (!empty($buttonLinear) ? 2 : 1) }}" class="text-center">Data
                                Kosong</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ !empty($data) ? $data->links() : '' }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.cell_sorting').forEach(element => {
            element.addEventListener('click', () => {
                const column = element.getAttribute('id');
                let currentUrl = new URL(window.location.href);
                let searchParams = currentUrl.searchParams;

                // Determine the sorting order (toggle between 'asc' and 'desc')
                let currentOrderBy = searchParams.get('order_by');
                let currentOrderType = searchParams.get('order_type');
                let newOrderType = (currentOrderBy === column && currentOrderType === 'asc') ? 'desc' :
                    'asc';

                // Set the sorting order
                searchParams.set('order_by', column);
                searchParams.set('order_type', newOrderType);

                // Update the URL and reload the page
                currentUrl.search = searchParams.toString();
                window.location.href = currentUrl.toString();
            });
        });

        // hanya jika form delete ke submit
        document.querySelectorAll('.form-delete').forEach(form => {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const confirmed = confirm('Apakah anda yakin akan menghapus data ini?');
                if (confirmed) {
                    this.submit();
                }
            });
        });

        @if (!isset($is_compare))
            document.addEventListener('DOMContentLoaded', function() {
                // jika ada query string key dan valuenya ada kata _date nya
                const key = document.querySelector('select[name="key"]').value;
                const input = document.querySelector('input[name="value"]');
                if (key.includes('_date')) {
                    input.type = 'date';
                }
            });

            // hanya jika select id=key diubah dan valuenya ada kata _date nya
            document.querySelector('select[name="key"]').addEventListener('change', function() {
                const value = this.value;
                const input = document.querySelector('input[name="value"]');
                if (value.includes('_date')) {
                    input.type = 'date';
                } else {
                    input.type = 'text';
                }
            });
        @endif
        // hanya jika is_compare
        @if (isset($is_compare) && $is_compare)
            // hanya jika form filter di submit
            document.querySelector('form').addEventListener('submit', function(event) {
                event.preventDefault();
                const startDate = document.querySelector('input[name="start_date"]').value;
                const endDate = document.querySelector('input[name="end_date"]').value;

                if (startDate === '' || endDate === '') {
                    alert('Tanggal awal dan akhir harus diisi');
                    return;
                }

                if (startDate > endDate) {
                    alert('Tanggal awal harus lebih kecil dari tanggal akhir');
                    return;
                }

                this.submit();
            });
        @endif
    </script>
@endsection
