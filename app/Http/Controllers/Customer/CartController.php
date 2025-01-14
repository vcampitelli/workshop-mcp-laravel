<?php

namespace App\Http\Controllers\Customer;

use App\Models\Cart;
use App\Models\Product;
use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function index() {}

    public function store(Product $product) {}

    public function destroy(Cart $cart) {}
}
