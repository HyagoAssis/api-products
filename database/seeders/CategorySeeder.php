<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Eletrônicos', 'description' => 'Smartphones, notebooks, TVs e demais aparelhos eletrônicos.'],
            ['name' => 'Informática', 'description' => 'Computadores, periféricos e acessórios de informática.'],
            ['name' => 'Eletrodomésticos', 'description' => 'Geladeiras, fogões, máquinas de lavar e outros itens para a casa.'],
            ['name' => 'Móveis e Decoração', 'description' => 'Móveis, objetos decorativos e utilidades para o lar.'],
            ['name' => 'Moda e Vestuário', 'description' => 'Roupas, calçados e acessórios para todos os estilos.'],
            ['name' => 'Beleza e Cuidados Pessoais', 'description' => 'Cosméticos, perfumes e produtos de higiene pessoal.'],
            ['name' => 'Alimentos e Bebidas', 'description' => 'Mercearia, bebidas e produtos alimentícios em geral.'],
            ['name' => 'Esporte e Lazer', 'description' => 'Equipamentos esportivos, fitness e artigos de lazer.'],
            ['name' => 'Livros e Papelaria', 'description' => 'Livros, materiais escolares e artigos de papelaria.'],
            ['name' => 'Brinquedos e Games', 'description' => 'Brinquedos, jogos e consoles para todas as idades.'],
        ];

        foreach ($categories as $category) {
            Category::factory()->create($category);
        }
    }
}
