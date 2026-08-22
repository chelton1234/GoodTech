<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'Telemóveis e acessórios'],
            ['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'Computadores portáteis e acessórios'],
            ['name' => 'Áudio', 'slug' => 'audio', 'description' => 'Fones de ouvido, colunas e acessórios de áudio'],
            ['name' => 'Acessórios', 'slug' => 'acessorios', 'description' => 'Capas, carregadores e outros acessórios'],
            ['name' => 'Gaming', 'slug' => 'gaming', 'description' => 'Produtos para gaming e e-sports'],
            ['name' => 'Casa Inteligente', 'slug' => 'casa-inteligente', 'description' => 'Dispositivos smart home e IoT'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
