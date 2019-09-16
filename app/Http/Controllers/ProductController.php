<?php

namespace App\Http\Controllers;

use App\Product;
use App\Http\Resources\Test;
use Illuminate\Http\Request;
use App\Http\Resources\Product as ProductResource;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = new Product();

        if ($request->keyword) {
            $products = $products->orWhere('name', 'like', '%' . $request->keyword . '%');
        }
        
        if ($request->price) {
            $products = $products->whereIn('price', $request->price);
        } 
        
        if ($request->amount) {
            $products = $products->whereIn('amount', $request->amount);
        } 

        $products = $products->orderBy('created_at', 'desc')->paginate(4);

        return ProductResource::collection($products);
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
    
    public function submit(Request $request)
    {
        return $request->amount;
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