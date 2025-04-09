<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WalletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Giả sử có 5 user có sẵn
        for ($i = 1; $i <= 5; $i++) {
            $walletId = DB::table('wallets')->insertGetId([
                'user_id' => $i,
                'balance' => rand(100000, 1000000), // random từ 100k đến 1 triệu
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mỗi ví có 2 giao dịch mẫu
            for ($j = 0; $j < 2; $j++) {
                DB::table('wallet_transactions')->insert([
                    'wallet_id' => $walletId,
                    'amount' => rand(50000, 200000), // random từ 50k đến 200k
                    'type' => ['nap_tien', 'hoan_tien'][rand(0, 1)],
                    'payment_method_id' => rand(1, 3), // giả sử có sẵn 3 phương thức
                    'transaction_ref' => Str::uuid(),
                    'description' => 'Giao dịch mẫu ' . ($j + 1),
                    'status' => ['cho_thanh_toan', 'thanh_cong', 'that_bai', 'da_huy'][rand(0, 3)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
