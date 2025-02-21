<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BreadcrumbServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $routeName = Route::currentRouteName();
            $parameters = request()->route()->parameters();
            $breadcrumbs = $this->generateBreadcrumbs($routeName, $parameters);

            $view->with('breadcrumbs', $breadcrumbs);
        });
    }

    /**
     * Generate breadcrumbs based on the route name.
     */
    private function generateBreadcrumbs($routeName, $parameters = [])
    {
        $breadcrumbMap = [
            'dashboard' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')]
            ],

            // User Routes
            'users.index' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Người Dùng', 'url' => route('users.index')]
            ],
            'users.create' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Người Dùng', 'url' => route('users.index')],
                ['name' => 'Thêm Mới', 'url' => '']
            ],
            'users.edit' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Người Dùng', 'url' => route('users.index')],
                ['name' => 'Chỉnh Sửa', 'url' => '']
            ],

            // Post Routes
            'posts.index' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Bài Viết', 'url' => route('posts.index')]
            ],
            'posts.create' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Bài Viết', 'url' => route('posts.index')],
                ['name' => 'Thêm Mới', 'url' => '']
            ],
            'posts.edit' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Bài Viết', 'url' => route('posts.index')],
                ['name' => 'Chỉnh Sửa', 'url' => '']
            ],

            // Category Post Routes
            'category-posts.index' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Chuyên Mục Bài Viết', 'url' => route('category-posts.index')]
            ],
            'category-posts.create' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Chuyên Mục Bài Viết', 'url' => route('category-posts.index')],
                ['name' => 'Thêm Mới', 'url' => '']
            ],
            'category-posts.edit' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Chuyên Mục Bài Viết', 'url' => route('category-posts.index')],
                ['name' => 'Chỉnh Sửa', 'url' => '']
            ],
            
            // Voucher Routes
            'vouchers.index' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Vouchers', 'url' => route('vouchers.index')]
            ],
            'vouchers.create' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Vouchers', 'url' => route('vouchers.index')],
                ['name' => 'Thêm Mới', 'url' => '']
            ],
            'vouchers.edit' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Vouchers', 'url' => route('vouchers.index')],
                ['name' => 'Chỉnh Sửa', 'url' => '']
            ],

            // Upload Image
            'upload.image' => [
                ['name' => 'Dashboard', 'url' => route('dashboard')],
                ['name' => 'Tải Ảnh Lên', 'url' => '']
            ],
        ];

        // Kiểm tra xem route có trong map không, nếu không thì tạo breadcrumb động
        return $breadcrumbMap[$routeName] ?? $this->generateBreadcrumbsFromUrl();
    }

    /**
     * Generate breadcrumbs dynamically from the URL segments.
     */
    private function generateBreadcrumbsFromUrl()
    {
        $segments = request()->segments();
        $breadcrumbs = [];
        $url = '';

        // Danh sách dịch URL segment sang tiếng Việt
        $translations = [
            'admin' => 'Dashboards',
            'users' => 'Người dùng',
            'posts' => 'Bài viết',
            'category-posts' => 'Chuyên mục bài viết',
            'create' => 'Thêm mới',
            'edit' => 'Chỉnh sửa',
            'upload-image' => 'Tải ảnh lên',
        ];

        foreach ($segments as $segment) {
            $url .= '/' . $segment;
            $name = $translations[$segment] ?? ucfirst(str_replace('-', ' ', $segment)); // Dịch nếu có
    
            // Nếu segment là số (ID), kiểm tra và lấy tiêu đề bài viết
            if (is_numeric($segment) && request()->is('admin/posts/*')) {
                $post = \App\Models\Post::find($segment);
                if ($post) {
                    $name = $post->title; // Lấy tiêu đề bài viết
                }
            }
            if (is_numeric($segment) && request()->is('admin/users/*')) {
                $user = \App\Models\User::find($segment);
                if ($user) {
                    $name = $user->name; // Lấy tên người dùng
                }
            }
            if (is_numeric($segment) && request()->is('admin/vouchers/*')) {
                $voucher = \App\Models\Voucher::find($segment);
                if ($voucher) {
                    $name = $voucher->code; // Lấy mã voucher
                }
            }
    
            $breadcrumbs[] = [
                'name' => $name,
                'url' => url($url),
            ];
        }

        return $breadcrumbs;
    }
}
