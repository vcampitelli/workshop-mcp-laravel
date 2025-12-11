<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class OrderController extends Controller
{
    public function index(Request $request): Collection
    {
        $query = Order::query();

        $filters = $request->validate([
            'after' => ['nullable', 'date'],
            'before' => ['nullable', 'date'],
        ]);

        if (!empty($filters['after'])) {
            $query->where('created_at', '>=', $filters['after']);
        }

        if (!empty($filters['before'])) {
            $query->where('created_at', '<=', $filters['before']);
        }

        return $query
            // @TODO
//            ->with(['status', 'user'])
            ->get();
    }

    public function create() {}

    public function store(Request $request) {}

    public function show(Order $order) {}

    public function edit(Order $order) {}

    public function update(Request $request, Order $order) {}

    public function destroy(Order $order) {}

    public function updateStatus(Order $order) {}
}
