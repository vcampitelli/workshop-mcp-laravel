<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'Pagamento pendente',
            'Em processamento',
            'Confirmado',
            'Enviado',
            'Saiu para entrega',
            'Entregue',
            'Cancelado',
            'Reembolsado',
            'Falha',
            'Em espera',
        ];

        OrderStatus::insert(array_map(fn (string $name) => [
            'name'       => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ], $statuses));
    }
}
