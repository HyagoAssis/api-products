<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = Category::query()->pluck('id', 'name');

        foreach ($this->products() as $categoryName => $products) {
            $categoryId = $categoryIds[$categoryName] ?? $categoryIds->first();

            foreach ($products as $product) {
                Product::factory()->create([
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'category_id' => $categoryId,
                ]);
            }
        }
    }

    /**
     * Plausible products grouped by category name (5 per category).
     *
     * @return array<string, array<int, array{name: string, description: string}>>
     */
    private function products(): array
    {
        return [
            'Eletrônicos' => [
                ['name' => 'Smartphone Galaxy A55 128GB', 'description' => 'Celular com tela AMOLED de 6.6", câmera tripla de 50MP e bateria de 5000mAh.'],
                ['name' => 'Smart TV LED 50" 4K UHD', 'description' => 'Televisor com resolução 4K, HDR e sistema operacional com apps de streaming integrados.'],
                ['name' => 'Fone de Ouvido Bluetooth TWS', 'description' => 'Fones sem fio com cancelamento de ruído e estojo carregador de longa duração.'],
                ['name' => 'Caixa de Som Portátil 20W', 'description' => 'Speaker Bluetooth à prova d\'água com até 12 horas de reprodução.'],
                ['name' => 'Smartwatch Fitness GPS', 'description' => 'Relógio inteligente com monitor cardíaco, GPS e medição de oxigênio no sangue.'],
            ],
            'Informática' => [
                ['name' => 'Notebook Ultrafino 15.6" i5 8GB', 'description' => 'Notebook com processador Intel Core i5, 8GB de RAM e SSD de 256GB.'],
                ['name' => 'Mouse Gamer 7 Botões RGB', 'description' => 'Mouse óptico com 12000 DPI ajustáveis e iluminação RGB personalizável.'],
                ['name' => 'Teclado Mecânico ABNT2', 'description' => 'Teclado mecânico com switches azuis, anti-ghosting e layout brasileiro.'],
                ['name' => 'Monitor 24" Full HD IPS', 'description' => 'Monitor com painel IPS, 75Hz e ajuste de altura para maior conforto.'],
                ['name' => 'SSD NVMe 1TB', 'description' => 'Unidade de estado sólido M.2 com velocidade de leitura de até 3500MB/s.'],
            ],
            'Eletrodomésticos' => [
                ['name' => 'Geladeira Frost Free 375L', 'description' => 'Refrigerador duplex com tecnologia frost free e compartimento extra frio.'],
                ['name' => 'Máquina de Lavar 12kg', 'description' => 'Lavadora automática com 12 programas de lavagem e dispenser inteligente.'],
                ['name' => 'Micro-ondas 30L Inox', 'description' => 'Forno micro-ondas com 10 níveis de potência e função descongelar.'],
                ['name' => 'Air Fryer 4L Digital', 'description' => 'Fritadeira sem óleo com painel digital e cesto antiaderente removível.'],
                ['name' => 'Aspirador de Pó Vertical 2 em 1', 'description' => 'Aspirador sem fio portátil com filtro HEPA e bateria recarregável.'],
            ],
            'Móveis e Decoração' => [
                ['name' => 'Sofá Retrátil 3 Lugares', 'description' => 'Sofá com assento retrátil e reclinável, revestido em tecido suede.'],
                ['name' => 'Mesa de Jantar 6 Lugares', 'description' => 'Mesa em MDF com tampo de vidro temperado e acabamento amadeirado.'],
                ['name' => 'Estante para Livros 5 Prateleiras', 'description' => 'Estante versátil em MDP resistente, ideal para livros e objetos decorativos.'],
                ['name' => 'Luminária de Chão Moderna', 'description' => 'Luminária de piso com haste ajustável e cúpula em tecido.'],
                ['name' => 'Kit 3 Quadros Decorativos', 'description' => 'Conjunto de quadros com moldura em MDF e impressão de alta qualidade.'],
            ],
            'Moda e Vestuário' => [
                ['name' => 'Camiseta Básica Algodão', 'description' => 'Camiseta unissex 100% algodão com gola redonda e caimento confortável.'],
                ['name' => 'Calça Jeans Slim Masculina', 'description' => 'Calça jeans com elastano, modelagem slim e lavagem escura.'],
                ['name' => 'Tênis Casual Unissex', 'description' => 'Tênis leve com solado emborrachado e cabedal respirável.'],
                ['name' => 'Jaqueta Corta-Vento', 'description' => 'Jaqueta impermeável com capuz ajustável e bolsos com zíper.'],
                ['name' => 'Vestido Midi Floral', 'description' => 'Vestido de manga curta com estampa floral e tecido leve para o verão.'],
            ],
            'Beleza e Cuidados Pessoais' => [
                ['name' => 'Kit Shampoo e Condicionador 400ml', 'description' => 'Dupla de hidratação profunda para cabelos secos e danificados.'],
                ['name' => 'Perfume Eau de Parfum 100ml', 'description' => 'Fragrância amadeirada de longa fixação para o dia a dia.'],
                ['name' => 'Creme Hidratante Facial FPS 30', 'description' => 'Hidratante facial com proteção solar e ácido hialurônico.'],
                ['name' => 'Secador de Cabelos 2000W', 'description' => 'Secador com tecnologia íon, duas velocidades e três temperaturas.'],
                ['name' => 'Barbeador Elétrico Recarregável', 'description' => 'Barbeador à prova d\'água com lâminas de aço e bateria de longa duração.'],
            ],
            'Alimentos e Bebidas' => [
                ['name' => 'Café Torrado e Moído 500g', 'description' => 'Café 100% arábica de torra média com aroma intenso e sabor encorpado.'],
                ['name' => 'Azeite de Oliva Extra Virgem 500ml', 'description' => 'Azeite extra virgem com baixa acidez, ideal para saladas e finalizações.'],
                ['name' => 'Chocolate Amargo 70% 100g', 'description' => 'Barra de chocolate com 70% de cacau, sem adição de glúten.'],
                ['name' => 'Chá Verde em Sachês (25un)', 'description' => 'Chá verde natural em sachês individuais para uma bebida antioxidante.'],
                ['name' => 'Mix de Castanhas 500g', 'description' => 'Blend de castanhas e nozes torradas sem sal, fonte de energia saudável.'],
            ],
            'Esporte e Lazer' => [
                ['name' => 'Bola de Futebol Campo Oficial', 'description' => 'Bola tamanho oficial com costura reforçada e câmara de butil.'],
                ['name' => 'Kit Halteres Ajustáveis 20kg', 'description' => 'Par de halteres com anilhas removíveis para treino em casa.'],
                ['name' => 'Bicicleta Aro 29 21 Marchas', 'description' => 'Bike mountain bike com quadro de alumínio e freios a disco.'],
                ['name' => 'Tapete de Yoga Antiderrapante', 'description' => 'Tapete emborrachado de 6mm com superfície antiderrapante e alça de transporte.'],
                ['name' => 'Barraca de Camping 4 Pessoas', 'description' => 'Barraca impermeável de montagem rápida com mosquiteiro e bolsa.'],
            ],
            'Livros e Papelaria' => [
                ['name' => 'Livro "O Poder do Hábito"', 'description' => 'Best-seller sobre a ciência da formação de hábitos e mudança de comportamento.'],
                ['name' => 'Caderno Universitário 10 Matérias', 'description' => 'Caderno espiral com 200 folhas pautadas e capa dura resistente.'],
                ['name' => 'Kit Canetas Esferográficas (12un)', 'description' => 'Conjunto de canetas em cores variadas com ponta média de 1.0mm.'],
                ['name' => 'Agenda Planner 2025', 'description' => 'Agenda com visão semanal, espaço para metas e capa em couro sintético.'],
                ['name' => 'Estojo Escolar Duplo', 'description' => 'Estojo com dois compartimentos e zíper reforçado para material escolar.'],
            ],
            'Brinquedos e Games' => [
                ['name' => 'Console de Videogame 1TB', 'description' => 'Console de última geração com 1TB de armazenamento e controle sem fio.'],
                ['name' => 'Controle Sem Fio Extra', 'description' => 'Controle wireless com bateria recarregável e vibração dupla.'],
                ['name' => 'Blocos de Montar 500 Peças', 'description' => 'Conjunto de blocos coloridos compatíveis para construção criativa.'],
                ['name' => 'Quebra-Cabeça 1000 Peças', 'description' => 'Quebra-cabeça com paisagem panorâmica e peças em papelão reforçado.'],
                ['name' => 'Carrinho de Controle Remoto', 'description' => 'Carro off-road 4x4 com controle remoto de longo alcance e tração nas quatro rodas.'],
            ],
        ];
    }
}
