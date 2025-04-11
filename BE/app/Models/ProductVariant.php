<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'variant_details',
        'sku',
        'price',
        'discount_price',
        'quantity',
        'status'
    ];
    protected $casts = [
        'price' => 'float',
        'discount_price' => 'float',
        'status' => 'integer',
        'variant_details' => 'json',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class)->withTrashed();
    }

    public function variantValue()
    {
        return $this->belongsTo(VariantValue::class)->withTrashed();
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    // public function getVariantDetailsAttribute()
    // {
    //     $details = json_decode($this->attributes['variant_details'], true);
    //     if (!$details) return null;

    //     $formattedDetails = [];
    //     foreach ($details as $variantId => $variantValueId) {
    //         $variant = Variant::find($variantId);
    //         $variantValue = VariantValue::find($variantValueId);

    //         if ($variant && $variantValue) {
    //             $formattedDetails[] = [
    //                 'name' => $variant->name,
    //                 'value' => $variantValue->value
    //             ];
    //         }
    //     }
    //     return $formattedDetails;
    // }
    public function getVariantDetailsAttribute()
    {
        try {
            \Log::info("Đang xử lý variant_details cho variant ID: " . $this->id);
            
            $details = json_decode($this->attributes['variant_details'] ?? '{}', true);
            \Log::info("Dữ liệu variant_details gốc:", ['details' => $details]);
            
            if (!$details || !is_array($details)) {
                \Log::warning("variant_details không hợp lệ hoặc rỗng");
                return [];
            }

            $formattedDetails = [];
            
            // Kiểm tra nếu đã là mảng các đối tượng có name và value
            if (isset($details[0]) && isset($details[0]['name']) && isset($details[0]['value'])) {
                \Log::info("variant_details đã ở định dạng name-value");
                return $details;
            }
            
            // Nếu là đối tượng với cặp khóa-giá trị {variantId: variantValueId}
            foreach ($details as $variantId => $variantValueId) {
                $variant = Variant::find($variantId);
                $variantValue = VariantValue::find($variantValueId);

                if ($variant && $variantValue) {
                    $formattedDetails[] = [
                        'name' => $variant->name,
                        'value' => $variantValue->value
                    ];
                } else {
                    \Log::warning("Không tìm thấy variant hoặc variant value", [
                        'variant_id' => $variantId,
                        'variant_value_id' => $variantValueId
                    ]);
                }
            }

            \Log::info("Kết quả xử lý variant_details:", ['formatted_details' => $formattedDetails]);
            return $formattedDetails;
        } catch (\Exception $e) {
            \Log::error("Lỗi trong getVariantDetailsAttribute: " . $e->getMessage(), [
                'variant_id' => $this->id,
                'error' => $e->getTraceAsString()
            ]);
            return [];
        }
    }
}
