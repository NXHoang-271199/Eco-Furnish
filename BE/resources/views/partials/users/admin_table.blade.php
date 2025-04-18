<table class="table table-nowrap mb-0">
    <thead>
        <tr class="text-muted">
            <th scope="col">#</th>
            <th scope="col">Ảnh</th>
            <th scope="col">Tên</th>
            <th scope="col">Email</th>
            <th scope="col">Vai trò</th>
            <th scope="col">Ngày tham gia</th>
            <th scope="col">Trạng thái</th>
            @if (Auth::user()->hasPermission('update-users'))
                <th scope="col" class="text-end">Thao tác</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($listUsers as $user)
            @if ($user->role->slug === 'admin' || $user->role->slug === 'staff')
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        @if ($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" 
                                alt="ảnh {{ $user->name }}"
                                class="avatar-md"
                                width="40" height="40"
                                style="object-fit: cover;">
                        @else
                            <img src="{{ asset('assets/admins/images/users/avatarUser.png') }}" 
                                alt="ảnh mặc định"
                                class="avatar-md"
                                width="40" height="40"
                                style="object-fit: cover;">
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="fs-14 mb-0">{{ $user->name }}</h5>
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <span class="badge bg-primary-subtle">
                            {{ $user->role->name }}
                        </span>
                    </td>
                    <td>{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        @if ($user->is_active)
                            <span class="badge bg-success-subtle">Hoạt động</span>
                        @else
                            <span class="badge bg-danger-subtle">Đã khóa</span>
                        @endif
                    </td>
                    @if (Auth::user()->hasPermission('update-users'))
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                @if (Auth::user()->isAdmin())
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-soft-primary">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                @endif
                                @if (Auth::user()->isAdmin() || auth()->id() === $user->id)
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-soft-primary">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                @endif
                                @if (Auth::user()->isAdmin() && auth()->id() !== $user->id)
                                    <button class="btn btn-sm btn-soft-danger"
                                        onclick="deleteUser({{ $user->id }})">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
