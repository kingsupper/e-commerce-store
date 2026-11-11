<?php
namespace App\Http\Controllers\Api;


use App\Enums\OrderStatus;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;



class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()->with('items.product')->latest()->get();
        return OrderResource::collection($orders);
    }

    // 2. عرض تفاصيل طلب معين
    public function show(Request $request, Order $order)
    {
        // تأمين: التأكد إن الطلب بتاع اليوزر ده
        abort_if($order->user_id !== $request->user()->id, 404);

        return new OrderResource($order->load('items.product'));
    }

    // 3. عملية الـ Checkout (التحويل من سلة لطلب)
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $user->cart()->with('items.product')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'السلة فارغة، لا يمكن إتمام الطلب.'], 400);
        }

        try {
            return DB::transaction(function () use ($user, $cart, $request) {

                $total = 0;
                $prices = [];

                // أولاً: التحقق من المخزون وحساب الإجمالي
                foreach ($cart->items as $item) {
                    $product = \App\Models\Product::lockForUpdate()->find($item->product_id);

                    // ملاحظة: إنت هنا مستخدم stock_quantity
                    if (!$product || $item->quantity > $product->stock_quantity) {
                        $name = $item->product->name ?? '';
                        throw new \Exception("الكمية المطلوبة من المنتج ({$name}) غير متوفرة في المخزون.");
                    }

                    $prices[$item->id] = $product->sale_price ?? $product->price;
                    $total += $item->quantity * $prices[$item->id];
                }


                $order = $user->orders()->create([
                    'address_id' => $request->address_id,
                    'total' => $total,
                    'status' => 'pending',
                ]);


                $order->payment()->create([
                    'payment_method' => 'cash_on_delivery',
                    'amount' => $total,
                    'status' => 'pending',
                ]);


                foreach ($cart->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $prices[$item->id],
                    ]);

                    \App\Models\Product::where('id', $item->product_id)
                        ->decrement('stock_quantity', $item->quantity); 
                }

                // خامساً: تفريغ السلة
                $cart->items()->delete();

                return (new OrderResource($order->load('items.product')))
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }



    public function cancel($id)
    {
        // 1. بندور على الطلب
        $order = Order::with('items.product')->findOrFail($id);

        // 2. نتأكد إن الطلب لسه "قيد الانتظار" باستخدام الـ Enum اللي عملناه
        if ($order->status !== OrderStatus::PENDING->value) {
            return response()->json([
                'message' => 'لا يمكن إلغاء هذا الطلب، لقد تم تجهيزه أو شحنه بالفعل.'
            ], 400);
        }

        // 3. نستخدم Transaction عشان نضمن إن العمليات تنجح كلها مع بعض
        DB::transaction(function () use ($order) {

            // 4. نرجع الكميات للمخزن تاني
            foreach ($order->items as $item) {
                // لو اسم العلاقة في الموديل اسمه حاجة تانية غير product، غيرها هنا
                $product = $item->product;
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
                }
            }

            // 5. نغير حالة الطلب لـ ملغي
            $order->update(['status' => OrderStatus::CANCELLED->value]);
        });

        return response()->json([
            'message' => 'تم إلغاء الطلب وإرجاع المنتجات للمخزن بنجاح.'
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:processing,shipped,delivered'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'تم تحديث حالة الطلب بنجاح.']);
    }
}
