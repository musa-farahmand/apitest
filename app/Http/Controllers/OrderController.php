<?php
namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        try {
            $orders = Order::with(['orderItems', 'user'])->get();
            return response()->json(['data' => $orders]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        try {

            $request->validate([
                'user_id' => 'required|exists:users,id',
                'status' => 'required',
                'total_amount' => 'required|numeric',
            ]);

            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $request->user_id,
                'status' => $request->status,
                'total_amount' => $request->total_amount,
            ]);
            DB::commit();

            return response()->json(['result' => true, 'order' => $order], 201);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }


    public function show($id)
    {
        try {
            $order = Order::findOrFail($id);
            return response()->json(['data' => $order]);
        } catch (\Exception $th) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    }


    public function update(Request $request, $id)
    {
        try {

            $request->validate([
                'status' => 'required|string',
                'total_amount' => 'required|numeric',
            ]);

            DB::beginTransaction();
            $order = Order::findOrFail($id);
            $order->update([
                'status' => $request->status,
                'total_amount' => $request->total_amount,
            ]);
            DB::commit();

            return response()->json(['result' => true, 'order' => $order], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $order = Order::findOrFail($id);
            $order->delete();
            DB::commit();
            return response()->json(['message' => 'Order deleted successfully'], 200);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
