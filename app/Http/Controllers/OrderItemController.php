<?php
namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{

    public function index(Request $request)
    {
        try {
            $orderItems = OrderItem::with(['order', 'product'])->get();
            return response()->json(['data' => $orderItems]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $orderItem = OrderItem::create([
                'order_id' => $request->order_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'total' => $request->total,
            ]);
            DB::commit();

            return response()->json(['result' => true, 'order_item' => $orderItem], 201);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $orderItem = OrderItem::findOrFail($id); // Find the order item by ID
            return response()->json(['data' => $orderItem]);
        } catch (\Exception $th) {
            return response()->json(['error' => 'Order item not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'quantity' => 'required|integer|min:1',
                'price' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();
            $orderItem = OrderItem::findOrFail($id);
            $orderItem->update([
                'quantity' => $request->quantity,
                'price' => $request->price,
                'total' => $request->total,
            ]);
            DB::commit();

            return response()->json(['result' => true, 'order_item' => $orderItem], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $orderItem = OrderItem::findOrFail($id);
            $orderItem->delete();
            DB::commit();
            return response()->json(['message' => 'Order item deleted successfully'], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
