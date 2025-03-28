<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProductVariant;

class UpdateProductVariantsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-product-variants-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chuyển đổi dữ liệu product_variants từ cấu trúc cũ (variant_id và variant_value_id) sang cấu trúc mới (variant_details JSON)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu chuyển đổi dữ liệu bảng product_variants...');
        
        // Lấy tất cả các product_variants có dữ liệu trong trường variant_id và variant_value_id
        // nhưng chưa có dữ liệu trong trường variant_details
        $oldVariants = DB::table('product_variants')
            ->whereNotNull('variant_id')
            ->whereNotNull('variant_value_id')
            ->whereNull('variant_details')
            ->get();
            
        $this->info("Tìm thấy {$oldVariants->count()} biến thể cần cập nhật.");
        
        $count = 0;
        
        foreach ($oldVariants as $variant) {
            // Tạo dữ liệu variant_details mới
            $variantDetails = [
                $variant->variant_id => $variant->variant_value_id
            ];
            
            // Cập nhật vào cơ sở dữ liệu
            DB::table('product_variants')
                ->where('id', $variant->id)
                ->update([
                    'variant_details' => json_encode($variantDetails)
                ]);
                
            $count++;
            
            if ($count % 50 == 0) {
                $this->info("Đã cập nhật {$count} biến thể.");
            }
        }
        
        $this->info("Hoàn thành chuyển đổi dữ liệu. Đã cập nhật {$count} biến thể.");
    }
}
