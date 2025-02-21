@extends('layouts.admin')

@section('title', 'Thêm biến thể mới')

@section('CSS')
 
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Thêm biến thể</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('variants.index') }}">Biến thể</a></li>
                        <li class="breadcrumb-item active">Thêm biến thể</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="variantForm" action="{{ route('variants.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Tên biến thể <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-end mb-3">
                            <button type="submit" class="btn btn-primary w-sm">Thêm biến thể</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
   @include('partials.variant.create_js')
@endsection 