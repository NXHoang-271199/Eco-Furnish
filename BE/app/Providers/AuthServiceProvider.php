<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use App\Policies\PostPolicy;
use App\Policies\UserPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\VoucherPolicy;
use App\Policies\DashboardPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Post::class => PostPolicy::class,
        User::class => UserPolicy::class,
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        Voucher::class => VoucherPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Đăng ký Gate cho Dashboard
        Gate::define('view-dashboard', [DashboardPolicy::class, 'view']);
        Gate::define('view-revenue', [DashboardPolicy::class, 'viewRevenue']);
        Gate::define('view-user-stats', [DashboardPolicy::class, 'viewUserStats']);
        Gate::define('view-product-stats', [DashboardPolicy::class, 'viewProductStats']);
        Gate::define('view-order-stats', [DashboardPolicy::class, 'viewOrderStats']);
        
        // Đăng ký Gate cho các quyền khác nếu cần
    }
}
