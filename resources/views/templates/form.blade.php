@extends('layouts.app')

@section('title', $title ?? 'Home')

@section('actions')
    <div>
        <a href="{{ route("$resource.index") }}" class="btn btn-secondary"> <i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
@endsection

@section('content')
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @method($method)
                        @foreach ($columns as $column)
                            @if (in_array($column['type'], ['text', 'number', 'email', 'date']))
                                <x-input :type="$column['type']" :name="$column['col']" :label="$column['label']" :value="old($column['col'], @$column['value'])"
                                    :placeholder="@$column['placeholder']" :required="@$column['required']" :disabled="@$column['disabled']" :readonly="@$column['readonly']"
                                    :edit="$edit" />
                            @elseif($column['type'] == 'select')
                                <x-select :name="$column['col']" :label="$column['label']" :options="$column['options']" :value="old($column['col'], @$column['value'])"
                                    :required="$column['required']" :edit="$edit" />
                            @endif
                        @endforeach
                        @if ($edit)
                            <div class="d-flex justify-content-end">
                                <x-button :type="'submit'" :state="'primary'">Submit</x-button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
