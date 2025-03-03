@extends('layouts.admin')

@section('title')
    Chi tiết vai trò
@endsection

@section('CSS')
    <style>
        #team-member-list.grid-view .member-item {
            display: inline-block;
            width: 30%;
        }

        #team-member-list.list-view .member-item {
            display: block;
            width: 100%;
        }
    </style>
@endsection

@section('JS')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div id="teamlist">
                    <div class="team-list row list-view-filter" id="team-member-list">
                        @foreach ($users as $user)
                            <div class="col">
                                <div class="card team-box">
                                    <div class="team-cover">
                                        <img src="{{ asset('assets/images/small/img-2.jpg') }}" alt=""
                                            class="img-fluid">
                                    </div>

                                    <div class="card-body p-4">
                                        <div class="row align-items-center team-row">
                                            <div class="col-lg-3 col">
                                                <div class="team-profile-img">
                                                    <div class="avatar-lg img-thumbnail rounded-circle flex-shrink-0">
                                                        <img src="{{ Storage::url($user->avatar) }}" alt=""
                                                            class="member-img img-fluid d-block rounded-circle">
                                                    </div>
                                                    <div class="team-content">
                                                        <a class="member-name" data-bs-toggle="offcanvas"
                                                            href="#member-overview" aria-controls="member-overview">
                                                            <h5 class="fs-16 mb-1">{{ $user->name }}</h5>
                                                        </a>
                                                        <p class="text-muted member-designation mb-0">
                                                            {{ $user->role->name }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col">
                                                <div class="row text-muted text-center">
                                                    <div class="col-6">
                                                        <h5 class="mb-1 tasks-num">Ngày tạo</h5>
                                                        <p class="text-muted mb-0">{{ $user->created_at }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2 col">
                                                <div class="text-end">
                                                    <a href="{{ route('users.show', $user->id) }}"
                                                        class="btn btn-light view-btn">Xem chi tiết</a>
                                                </div>
                                            </div>
                                            <div class="col-3 team-settings">
                                                <div class="row">
                                                    <div class="col text-end dropdown">
                                                        <a class="dropdown-item remove-list" href="#removeMemberModal"
                                                            data-bs-toggle="modal" data-remove-id="{{ $user->id }}">
                                                            <i
                                                                class="ri-delete-bin-5-line me-2 align-bottom text-muted"></i>Remove
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
