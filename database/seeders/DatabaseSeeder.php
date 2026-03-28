<?php

namespace Database\Seeders;

use App\Models\ProductModel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProductModels();
    }

    private function seedProductModels(): void
    {
        $models = [
            // ── Box ──────────────────────────────────────────────────────────────
            [
                'name'           => 'Cardboard Box Small',
                'slug'           => 'cardboard-box-small',
                'category'       => 'box',
                'description'    => 'Kotak karton kecil serbaguna, cocok untuk produk gift, makanan, atau elektronik kecil.',
                'thumbnail_path' => 'models/thumbnails/box-small.png',
                'model_3d_path'  => 'models/3d/box-small.glb',
                'dimensions'     => ['width' => 10, 'height' => 8, 'depth' => 5],
                'printable_areas'=> [
                    ['name' => 'Front', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 600],
                    ['name' => 'Back',  'x' => 0, 'y' => 0, 'w' => 800, 'h' => 600],
                    ['name' => 'Top',   'x' => 0, 'y' => 0, 'w' => 400, 'h' => 300],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 1,
            ],
            [
                'name'           => 'Cardboard Box Medium',
                'slug'           => 'cardboard-box-medium',
                'category'       => 'box',
                'description'    => 'Kotak karton medium, ideal untuk packaging retail dan produk kosmetik.',
                'thumbnail_path' => 'models/thumbnails/box-medium.png',
                'model_3d_path'  => 'models/3d/box-medium.glb',
                'dimensions'     => ['width' => 15, 'height' => 12, 'depth' => 8],
                'printable_areas'=> [
                    ['name' => 'Front', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 600],
                    ['name' => 'Back',  'x' => 0, 'y' => 0, 'w' => 800, 'h' => 600],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 2,
            ],
            [
                'name'           => 'Luxury Gift Box',
                'slug'           => 'luxury-gift-box',
                'category'       => 'box',
                'description'    => 'Kotak hadiah mewah dengan penutup magnet, premium finish.',
                'thumbnail_path' => 'models/thumbnails/box-luxury.png',
                'model_3d_path'  => 'models/3d/box-luxury.glb',
                'dimensions'     => ['width' => 20, 'height' => 10, 'depth' => 15],
                'printable_areas'=> [
                    ['name' => 'Lid Top',  'x' => 0, 'y' => 0, 'w' => 800, 'h' => 600],
                    ['name' => 'Lid Side', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 200],
                ],
                'is_active'      => true,
                'is_premium'     => true,
                'sort_order'     => 3,
            ],

            // ── Bottle ───────────────────────────────────────────────────────────
            [
                'name'           => 'Plastic Bottle 500ml',
                'slug'           => 'bottle-500ml',
                'category'       => 'bottle',
                'description'    => 'Botol plastik 500ml, cocok untuk minuman, air mineral, dan saus.',
                'thumbnail_path' => 'models/thumbnails/bottle-500ml.png',
                'model_3d_path'  => 'models/3d/bottle-500ml.glb',
                'dimensions'     => ['width' => 7, 'height' => 22, 'depth' => 7],
                'printable_areas'=> [
                    ['name' => 'Label Front', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 400],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 4,
            ],
            [
                'name'           => 'Glass Bottle 250ml',
                'slug'           => 'glass-bottle-250ml',
                'category'       => 'bottle',
                'description'    => 'Botol kaca 250ml premium, untuk skincare, minyak esensial, atau minuman artisanal.',
                'thumbnail_path' => 'models/thumbnails/bottle-glass.png',
                'model_3d_path'  => 'models/3d/bottle-glass.glb',
                'dimensions'     => ['width' => 5.5, 'height' => 18, 'depth' => 5.5],
                'printable_areas'=> [
                    ['name' => 'Label', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 400],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 5,
            ],
            [
                'name'           => 'Cosmetic Pump Bottle',
                'slug'           => 'cosmetic-pump-bottle',
                'category'       => 'bottle',
                'description'    => 'Botol pump kosmetik elegan, ideal untuk lotion, serum, dan sabun cair.',
                'thumbnail_path' => 'models/thumbnails/bottle-pump.png',
                'model_3d_path'  => 'models/3d/bottle-pump.glb',
                'dimensions'     => ['width' => 6, 'height' => 24, 'depth' => 6],
                'printable_areas'=> [
                    ['name' => 'Body Label', 'x' => 0, 'y' => 0, 'w' => 600, 'h' => 400],
                ],
                'is_active'      => true,
                'is_premium'     => true,
                'sort_order'     => 6,
            ],

            // ── Pouch ────────────────────────────────────────────────────────────
            [
                'name'           => 'Stand-Up Pouch',
                'slug'           => 'stand-up-pouch',
                'category'       => 'pouch',
                'description'    => 'Kantong berdiri dengan zip-lock, cocok untuk kopi, snack, dan dry food.',
                'thumbnail_path' => 'models/thumbnails/pouch-standup.png',
                'model_3d_path'  => 'models/3d/pouch-standup.glb',
                'dimensions'     => ['width' => 14, 'height' => 22, 'depth' => 7],
                'printable_areas'=> [
                    ['name' => 'Front', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 900],
                    ['name' => 'Back',  'x' => 0, 'y' => 0, 'w' => 800, 'h' => 900],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 7,
            ],
            [
                'name'           => 'Flat Bottom Pouch',
                'slug'           => 'flat-bottom-pouch',
                'category'       => 'pouch',
                'description'    => 'Pouch dasar datar premium, tampil lebih stabil dan elegan di rak.',
                'thumbnail_path' => 'models/thumbnails/pouch-flat.png',
                'model_3d_path'  => 'models/3d/pouch-flat.glb',
                'dimensions'     => ['width' => 16, 'height' => 24, 'depth' => 8],
                'printable_areas'=> [
                    ['name' => 'Front', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 900],
                ],
                'is_active'      => true,
                'is_premium'     => true,
                'sort_order'     => 8,
            ],

            // ── Can ──────────────────────────────────────────────────────────────
            [
                'name'           => 'Aluminum Can 330ml',
                'slug'           => 'aluminum-can-330ml',
                'category'       => 'can',
                'description'    => 'Kaleng aluminium 330ml standar, cocok untuk minuman berenergi dan soda.',
                'thumbnail_path' => 'models/thumbnails/can-330ml.png',
                'model_3d_path'  => 'models/3d/can-330ml.glb',
                'dimensions'     => ['width' => 6.6, 'height' => 11.5, 'depth' => 6.6],
                'printable_areas'=> [
                    ['name' => 'Wrap', 'x' => 0, 'y' => 0, 'w' => 1200, 'h' => 400],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 9,
            ],

            // ── Tube ─────────────────────────────────────────────────────────────
            [
                'name'           => 'Cosmetic Tube 100ml',
                'slug'           => 'cosmetic-tube-100ml',
                'category'       => 'tube',
                'description'    => 'Tube plastik 100ml untuk produk kosmetik: sunscreen, lotion, pasta gigi.',
                'thumbnail_path' => 'models/thumbnails/tube-100ml.png',
                'model_3d_path'  => 'models/3d/tube-100ml.glb',
                'dimensions'     => ['width' => 4, 'height' => 16, 'depth' => 4],
                'printable_areas'=> [
                    ['name' => 'Body', 'x' => 0, 'y' => 0, 'w' => 800, 'h' => 300],
                ],
                'is_active'      => true,
                'is_premium'     => false,
                'sort_order'     => 10,
            ],
        ];

        foreach ($models as $model) {
            ProductModel::updateOrCreate(['slug' => $model['slug']], $model);
        }

        $this->command->info('✓ Product models seeded: ' . count($models) . ' items');
    }
}