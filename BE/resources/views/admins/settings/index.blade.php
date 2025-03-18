@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#website-info" role="tab">
                            <i class="fas fa-info-circle me-1 align-middle"></i> Thông tin Website
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#smtp-settings" role="tab">
                            <i class="fas fa-envelope-open-text me-1 align-middle"></i> Cấu hình SMTP
                        </a>
                    </li>
                    
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane" id="smtp-settings" role="tabpanel">
                        <form action="{{ url('/admin/settings/smtp/update') }}" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="smtp_host" class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                               value="{{ config('mail.mailers.smtp.host') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                               value="{{ config('mail.mailers.smtp.port') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="smtp_username" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                               value="{{ config('mail.mailers.smtp.username') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="smtp_password" class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                               value="{{ config('mail.mailers.smtp.password') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="mail_from_address" class="form-label">Mail From Address</label>
                                        <input type="email" class="form-control" id="mail_from_address" name="mail_from_address" 
                                               value="{{ config('mail.from.address') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="mail_from_name" class="form-label">Mail From Name</label>
                                        <input type="text" class="form-control" id="mail_from_name" name="mail_from_name" 
                                               value="{{ config('mail.from.name') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="smtp_encryption" name="smtp_encryption" value="tls" 
                                                   {{ config('mail.mailers.smtp.encryption') == 'tls' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="smtp_encryption">Enable TLS Encryption</label>
                                            <!-- Thêm một input ẩn để đảm bảo giá trị null được gửi khi checkbox không được chọn -->
                                            <input type="hidden" name="smtp_encryption_hidden" value="null">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Lưu cài đặt</button>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane active" id="website-info" role="tabpanel">
                        <form action="{{ url('/admin/settings/website/update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="website_name" class="form-label">Tên Website</label>
                                        <input type="text" class="form-control" id="website_name" name="website_name" 
                                               value="{{ config('app.name') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="website_description" class="form-label">Mô tả Website</label>
                                        <textarea class="form-control" id="website_description" name="website_description" 
                                                  rows="3">{{ config('app.description') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="website_address" class="form-label">Địa chỉ</label>
                                        <input type="text" class="form-control" id="website_address" name="website_address" 
                                               value="{{ config('app.address') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="website_phone" class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control" id="website_phone" name="website_phone" 
                                               value="{{ config('app.phone') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="website_email" class="form-label">Email liên hệ</label>
                                        <input type="email" class="form-control" id="website_email" name="website_email" 
                                               value="{{ config('app.email') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="website_logo" class="form-label">Logo Website</label>
                                        <input type="file" class="form-control" id="website_logo" name="website_logo" 
                                               accept="image/*">
                                        @if(config('app.logo'))
                                            <div class="mt-2">
                                                <img src="{{ asset(config('app.logo')) }}" alt="Current Logo" 
                                                     style="max-height: 50px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="website_footer" class="form-label">Footer Content</label>
                                        <textarea class="form-control" id="website_footer" name="website_footer" 
                                                  rows="3">{{ config('app.footer') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Lưu thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Kích hoạt tabs
        var triggerTabList = [].slice.call(document.querySelectorAll('.nav-tabs-custom a'))
        triggerTabList.forEach(function(triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function(event) {
                event.preventDefault()
                tabTrigger.show()
            })
        })
    });
</script>
@endsection