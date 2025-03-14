# Câu lệnh

-   composer install => Cài đặt lại tất cả các thư viện hệ thống sử dụng
-   composer update => Cập nhật lại các thư viện hệ thống sử dụng // chỉ chạy khi đã có Folder vendor
-   php artisan serve => Chạy dự án
-   php artisan key:generate => Gender ra APP_KEY trong file env

# Tạo và làm việc với migration

## Migration -> thao tác (CRUD TABLE) -> CSDL (Mỗi file chỉ được thực hiện 1 thao tác)

-   php artisan make:migration create_flights_table => Tạo 1 file migration mới

-   php artisan migrate => Chạy các migration
-   php artisan migrate:rollback => Rollback lại thao tác cuối cùng của migration

*   php artisan migrate:rollback --step=5 => Rollback lại 5 lần trước đó
*   php artisan migrate:rollback --batch=5 => Rollback lại bước số 5

-   php artisan migrate:reset => Rollback lại tất cả các thao tác của migration
-   php artisan migrate:refresh => Rollback lại tất cả các thao tác của migration sau đó tự động chạy migrate

# Tạo và làm việc với Seeder

-   php artisan make:seeder UserSeeder => Tạo 1 file để tạo dữ liệu mẫu
-   php artisan db:seed => Chạy toàn bộ seeder
-   php artisan db:seed --class=UserSeeder => Chạy 1 class seeder

# Tạo Controller

-   php artisan make:controller TenController => Tạo 1 controller
-   php artisan make:controller File\TenController => Tạo 1 controller trong file
-   php artisan make:controller TenController --resource => Tạo controller resource
-   php artisan make:controller TenController --api => tạo controller api

# Tạo Model

-   php artisan make:model TenModel --all => Tạo tất cả
-   php artisan make:resource TenResource => Tạo resource

# Tạo Link từ storage

-   php artisan storage:link => tạo link đến public

# Quy tắc đặt tên bảng

Tên bảng là danh từ số nhiều (Tiếng việt)
VD:

-   san_phams tên trường liên kết bảng danh_mucs: danh_muc_id
-   danh_mucs
-   don-hangs

# Tìm hiểu factory

# Câu lệnh git
