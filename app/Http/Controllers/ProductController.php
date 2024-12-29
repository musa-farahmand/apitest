<?php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = Product::with('orderItems')->get();
            return response()->json([
                'data' => $products
            ]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'price' => 'required',
            ], [
                'name.required' => 'Name is required',
                'price.required' => 'Price is required',
            ]);

            DB::beginTransaction();
            $product = Product::create([
                'name' => $request->name,
                'price' => $request->price,
            ]);
            DB::commit();
            return response()->json(['result' => true, 'product' => $product], 201);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'name' => 'required|string',
                'price' => 'required|numeric',
            ], [
                'name.required' => 'Name is required',
                'price.required' => 'Price is required',
            ]);

            DB::beginTransaction();
            $product = Product::findOrFail($id);
            $product->update([
                'name' => $request->name,
                'price' => $request->price,

            ]);
            DB::commit();
            return response()->json($product, 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $product = Product::findOrFail($id);
            $product->delete();
            DB::commit();
            return response()->json(['message' => 'Product deleted successfully'], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);
            return response()->json($product);
        } catch (\Exception $th) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    }
}
