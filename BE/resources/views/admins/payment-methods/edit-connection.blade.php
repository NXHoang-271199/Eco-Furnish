@php
    $fields = [];

    if ($method->name === 'MoMo') {
        $fields = [
            'partner_code' => 'Partner Code',
            'access_key' => 'Access Key',
            'secret_key' => 'Secret Key',
        ];
    } elseif ($method->name === 'VNPAY') {
        $fields = [
            'vnp_TmnCode' => 'VNPAY Tmn Code',
            'vnp_HashSecret' => 'VNPAY Hash Secret',
        ];
    }
@endphp
<form method="POST" action="{{ route('payment-methods.update_connection', $method->id) }}">
    @csrf
    @foreach ($fields as $name => $label)
        <div class="mb-3">
            <label for="{{ $name }}" class="form-label">{{ $label }}</label>
            <input type="text" name="{{ $name }}" id="{{ $name }}" class="form-control"
                value="{{ $method->config[$name] ?? '' }}" required>
        </div>
    @endforeach
    <button type="submit" class="btn btn-primary">Cập nhật kết nối</button>
</form>
