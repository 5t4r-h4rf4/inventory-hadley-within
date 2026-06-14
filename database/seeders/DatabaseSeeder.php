<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductDemand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@inventory.com'],
            [
                'name' => 'Admin Inventory',
                'password' => Hash::make('admin123'),
            ]
        );

        // 2. Create Initial Products
        $products = [
            [
                'kode_produk' => 'PRD-001',
                'nama_produk' => 'PIRING BUAH RB',
                'harga_produk' => 327000.00,
            ],
            [
                'kode_produk' => 'PRD-002',
                'nama_produk' => 'PIRING MAWAR RB',
                'harga_produk' => 339000.00,
            ],
            [
                'kode_produk' => 'PRD-003',
                'nama_produk' => 'PIRING 9 SOFT',
                'harga_produk' => 425000.00,
            ],
            [
                'kode_produk' => 'PRD-004',
                'nama_produk' => 'PIRING 9 DX',
                'harga_produk' => 612000.00,
            ],
            [
                'kode_produk' => 'PRD-005',
                'nama_produk' => 'HANGER SOFT RB',
                'harga_produk' => 392000.00,
            ],
            [
                'kode_produk' => 'PRD-006',
                'nama_produk' => 'HANGER DX RB',
                'harga_produk' => 480000.00,
            ],
        ];

        $seededProducts = [];
        foreach ($products as $prd) {
            $seededProducts[$prd['kode_produk']] = Product::updateOrCreate(
                ['kode_produk' => $prd['kode_produk']],
                [
                    'nama_produk' => $prd['nama_produk'],
                    'harga_produk' => $prd['harga_produk'],
                ]
            );
        }

        // 3. Seed Monthly Demands
        $demandsData = [
            'PRD-001' => [24, 27, 52, 75, 29, 147, 98, 30, 144, 163, 91, 101],
            'PRD-002' => [25, 0, 83, 0, 34, 93, 0, 71, 61, 120, 29, 35],
            'PRD-003' => [0, 14, 38, 0, 0, 73, 8, 27, 74, 74, 64, 0],
            'PRD-004' => [15, 41, 0, 29, 31, 29, 29, 20, 77, 100, 0, 0],
            'PRD-005' => [112, 100, 59, 146, 132, 139, 92, 57, 55, 183, 92, 125],
            'PRD-006' => [82, 90, 97, 65, 64, 56, 49, 57, 120, 17, 101, 83],
        ];

        $months = [
            '2024-12-01', '2025-01-01', '2025-02-01', '2025-03-01',
            '2025-04-01', '2025-05-01', '2025-06-01', '2025-07-01',
            '2025-08-01', '2025-09-01', '2025-10-01', '2025-11-01'
        ];

        foreach ($demandsData as $code => $values) {
            $product = $seededProducts[$code] ?? null;
            if ($product) {
                foreach ($values as $index => $val) {
                    ProductDemand::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'periode' => Carbon::parse($months[$index]),
                        ],
                        [
                            'jumlah_permintaan' => $val,
                        ]
                    );
                }
            }
        }
    }
}
