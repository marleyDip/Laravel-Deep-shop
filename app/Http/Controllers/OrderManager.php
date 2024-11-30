<?php

namespace App\Http\Controllers;

use App\Models\orders;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Stripe\StripeClient;
use Stripe\Webhook;


class OrderManager extends Controller
{
    function showCheckout()
    {
        return view('checkout');
    }

    function checkoutPost(Request $request)
    {
        $request->validate([
            'address' => 'required',
            'pincode' => 'required',
            'phone' => 'required',
        ]);

        $cartItems = DB::table("cart")
            ->join('products', 'cart.product_id',
                '=', 'products.id')
            ->select(
                "cart.product_id",
                DB::raw("count(*) as quantity"),
                'products.price',
                "products.title",

            )
            ->where("cart.user_id", auth()->user()->id)
            ->groupBy(
                "cart.product_id",
                "products.price",
                "products.title",
            )
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect(route('cart.show'))->with('error', 'No products found');
        }

        $productIds = [];
        $quantities = [];
        $totalPrice = 0;
        $lineItems = [];

        foreach ($cartItems as $cartItem) {
            $productIds[] = $cartItem->product_id;
            $quantities[] = $cartItem->quantity;
            $totalPrice += $cartItem->price * $cartItem->quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'bdt',
                    'product_data' => [
                        'name' => $cartItem->title,
                    ],
                    'unit_amount' => $cartItem->price * 100,
                ],
                'quantity' => $cartItem->quantity,
            ];
        }

        $order = new Orders();

        $order->user_id = auth()->user()->id;

        $order->address = $request->address;
        $order->pincode = $request->pincode;
        $order->phone = $request->phone;

        $order->product_id = json_encode($productIds);
        $order->quantity = json_encode($quantities);
        $order->total_price = json_encode($totalPrice);


        if ($order->save()) {

            DB::table("cart")
                ->where("user_id", auth()->user()->id)
                ->delete();

            $stripe = new StripeClient(config("app.STRIPE_KEY"));

            $checkoutSession = $stripe->checkout->sessions->create([
                'success_url' => route('payment.success',
                    ['order_id' => $order->id]),
                'cancel_url' => route('payment.error'),
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => auth()->user()->email,
                'metadata' => [
                    'order_id' => $order->id
                ]
            ]);

            return redirect($checkoutSession->url);


        }
        return redirect(route('cart.show'))
            ->with('error', 'Order not placed');
    }

    function paymentSuccess($order_id)
    {
        return "success" . $order_id;
    }

    function paymentError()
    {
        return "error";
    }

    function webhookStripe(Request $request)
    {
        $endpointSecret = config("app.STRIPE_WEBHOOK_SECRET");
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe_Signature');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e){
            return response()->json(['error' => 'Invalid Signature'], 400);
        }

        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id;
            $paymentId = $session->payment_intent;

            $order = Orders::find($orderId);

            if ($order){
                $order->payment_id = $paymentId;
                $order->status = 'payment_completed';
                $order->save();
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    function orderHistory()
    {
        $orders = Orders::where("user_id", auth()->user()->id)->orderBy('id', 'DESC')->paginate(5);

        $orders = $orders->map(function ($order) {
            $productIds = json_decode($order->product_id, True);
            $quantities = json_decode($order->quantity, True);

            $products = Products::whereIn('id', $productIds)->get();

            $order->product_details = $products->map(function ($product)
            use ($quantities, $productIds) {
                $index = array_search($product->id, $productIds);
                return [
                    'name' => $product->title,
                    'quantity' => $quantities[$index] ?? 0,
                    'price' => $product->price,
                    'slug' => $product->slug,
                    'image' => $product->image,
                ];
            });

            return $order;

        });

        return view('history', compact('orders'));
    }
}

