<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $smartphones = Category::where('slug', 'smartphones')->first();
        $laptops = Category::where('slug', 'laptops')->first();
        $audio = Category::where('slug', 'audio')->first();
        $acessorios = Category::where('slug', 'acessorios')->first();
        $gaming = Category::where('slug', 'gaming')->first();
        $casa = Category::where('slug', 'casa-inteligente')->first();

        $products = [
            [
                'category_id' => $smartphones->id,
                'name' => 'Samsung Galaxy S24 Ultra',
                'slug' => 'samsung-galaxy-s24-ultra',
                'description' => 'O smartphone mais avançado da Samsung com câmera de 200MP e S Pen integrado.',
                'price' => 45999.00,
                'compare_price' => 49999.00,
                'stock' => 15,
                'is_featured' => true,
            ],
            [
                'category_id' => $smartphones->id,
                'name' => 'iPhone 15 Pro Max',
                'slug' => 'iphone-15-pro-max',
                'description' => 'O iPhone mais poderoso com chip A17 Pro e câmera de 48MP.',
                'price' => 52999.00,
                'stock' => 10,
                'is_featured' => true,
            ],
            [
                'category_id' => $smartphones->id,
                'name' => 'Xiaomi Redmi Note 13 Pro',
                'slug' => 'xiaomi-redmi-note-13-pro',
                'description' => 'Excelente relação qualidade-preço com câmera de 200MP.',
                'price' => 12999.00,
                'compare_price' => 14999.00,
                'stock' => 25,
            ],
            [
                'category_id' => $laptops->id,
                'name' => 'MacBook Air M3',
                'slug' => 'macbook-air-m3',
                'description' => 'Ultrabook potente com chip M3, 16GB RAM e 512GB SSD.',
                'price' => 64999.00,
                'stock' => 8,
                'is_featured' => true,
            ],
            [
                'category_id' => $laptops->id,
                'name' => 'Dell XPS 15',
                'slug' => 'dell-xps-15',
                'description' => 'Laptop premium com tela OLED 15.6" e processador Intel i7.',
                'price' => 52999.00,
                'compare_price' => 57999.00,
                'stock' => 6,
            ],
            [
                'category_id' => $audio->id,
                'name' => 'Sony WH-1000XM5',
                'slug' => 'sony-wh-1000xm5',
                'description' => 'Fones de ouvido com cancelamento de ruído líder da indústria.',
                'price' => 18999.00,
                'stock' => 20,
                'is_featured' => true,
            ],
            [
                'category_id' => $audio->id,
                'name' => 'JBL Charge 5',
                'slug' => 'jbl-charge-5',
                'description' => 'Coluna portátil à prova de água com 20 horas de bateria.',
                'price' => 7999.00,
                'compare_price' => 8999.00,
                'stock' => 30,
            ],
            [
                'category_id' => $acessorios->id,
                'name' => 'Carregador GaN 65W',
                'slug' => 'carregador-gan-65w',
                'description' => 'Carregador rápido compacto GaN com USB-C PD 65W.',
                'price' => 2999.00,
                'stock' => 50,
            ],
            [
                'category_id' => $gaming->id,
                'name' => 'PlayStation 5',
                'slug' => 'playstation-5',
                'description' => 'Consola de última geração com SSD ultra-rápido.',
                'price' => 29999.00,
                'stock' => 12,
                'is_featured' => true,
            ],
            [
                'category_id' => $gaming->id,
                'name' => 'Controle DualSense',
                'slug' => 'controle-dualsense',
                'description' => 'Controle sem fio para PS5 com gatilhos adaptáveis.',
                'price' => 3999.00,
                'stock' => 25,
            ],
            [
                'category_id' => $casa->id,
                'name' => 'Google Nest Hub (2nd Gen)',
                'slug' => 'google-nest-hub',
                'description' => 'Ecrã inteligente para controlar a sua casa.',
                'price' => 8999.00,
                'stock' => 15,
                'is_featured' => true,
            ],
            [
                'category_id' => $casa->id,
                'name' => 'Philips Hue Starter Kit',
                'slug' => 'philips-hue-starter-kit',
                'description' => 'Kit inicial de iluminação inteligente com 3 lâmpadas e ponte.',
                'price' => 12999.00,
                'compare_price' => 14999.00,
                'stock' => 10,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
