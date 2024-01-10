<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();

            if ($request->has('category')) {
                $query->where('category', $request->input('category'));
            }

            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->input('min_price'));
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->input('max_price'));
            }

            $products = $query->get();

            return response()->json(['data' => $products], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:200',
            'price' => 'required|integer',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        // Cek jika validasi gagal
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Proses menyimpan produk
        try {
            $product = new Product([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'category' => $request->input('category'),
            ]);

            // Menyimpan gambar jika ada
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/products', $imageName);
                $product->image = $imageName;
            }

            $product->save();

            return response()->json(['message' => 'Product created successfully', 'data' => $product], 201);
        } catch (\Exception $e) {
            // Tangani kesalahan jika terjadi
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        if ($product) {
            return response()->json($product, 200);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:200',
            'price' => 'required|integer',
            'category' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        // Cek jika validasi gagal
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Retrieve the product from the database using the provided $id
            $product = Product::find($id);

            // Check if the product was found
            if ($product) {
                // Update the product with the request data
                $product->update([
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'price' => $request->input('price'),
                    'category' => $request->input('category'),
                ]);

                // Update the image if provided in the request
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/products', $imageName);

                    // Delete the old image if exists
                    if ($product->image) {
                        Storage::delete('public/products/' . $product->image);
                    }

                    $product->image = $imageName;
                }

                $product->save();

                return response()->json(['message' => 'Product updated successfully', 'data' => $product], 200);
            } else {
                // Return a 404 Not Found HTTP status code if the product was not found
                return response()->json(['message' => 'Product not found'], 404);
            }
        } catch (\Exception $e) {
            // Return a 500 Internal Server Error if an exception occurs
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $product = Product::find($id);
            if ($product) {
                $product->delete();
                return response()->json(['message' => 'Product deleted successfully'], 204);
            } else {
                return response()->json(['message' => 'Product not found'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
