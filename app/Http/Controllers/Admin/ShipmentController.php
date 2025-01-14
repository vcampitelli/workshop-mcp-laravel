<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\OrderShipment;
use App\Http\Controllers\Controller;

class ShipmentController extends Controller
{
    public function index() {}

    public function create() {}

    public function store(Request $request) {}

    public function show(OrderShipment $orderShipment) {}

    public function edit(OrderShipment $orderShipment) {}

    public function update(Request $request, OrderShipment $orderShipment) {}

    public function destroy(OrderShipment $orderShipment) {}
}
