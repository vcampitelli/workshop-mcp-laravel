<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
    public function index() {}

    public function create() {}

    public function store(Request $request) {}

    public function show(ProductCategory $productCategory) {}

    public function edit(ProductCategory $productCategory) {}

    public function update(Request $request, ProductCategory $productCategory) {}

    public function destroy(ProductCategory $productCategory) {}
}
