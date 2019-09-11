<?php

namespace App\Http\Controllers;

use App\Product;
use App\Http\Resources\Test;
use Illuminate\Http\Request;
use App\Http\Resources\Product as ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        if (request()->get('q')) {
            return ProductResource::collection(Product::where('name', 'like', '%' . request()->get('q') . '%')->orderBy('created_at', 'desc')->paginate(1));
        }

        return ProductResource::collection(Product::orderBy('created_at', 'desc')->paginate(1));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'amount' => 'required|numeric'
        ]);

        $product = Product::create($request->all());
        return new ProductResource($product);
    }

    public function show($id)
    {
        $product = Product::find($id);
        return new ProductResource($product);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required|numeric',
            'amount' => 'required|numeric'
        ]);

        $product = Product::find($id);
        $product->update($request->all());
        return new ProductResource($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return new ProductResource($product);
    }
}