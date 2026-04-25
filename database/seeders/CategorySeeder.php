<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = json_decode(
            File::get(base_path('dev-guide/reference/tier2DDC.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        foreach ($categories as $index => $category) {
            Category::updateOrCreate(
                ['code' => (string) $category['number']],
                [
                    'label' => $category['name'],
                    'sort_order' => $index + 1,
                    'source_version' => 'tier2DDC',
                ],
            );
        }
    }
}
