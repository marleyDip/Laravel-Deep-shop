@extends("layouts.default")
@section("title", "Ecom - Order History")
@section("content")
    <main class="container" style="max-width: 900px">
        <section>
            <div class="row">

                @if (session()->has("success"))
                    <div class="alert alert-success">
                        {{session()->get("success")}}
                    </div>
                @endif

                @if (session("error"))
                    <div class="alert alert-danger">
                        {{session("error")}}
                    </div>
                @endif

                @foreach($orders as $order)
                    <div class="col-12">

                        <div class="card mb-3" style="max-width:700px;">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="{{ $order->product_details[0]['image'] }}"
                                         class="img-fluid rounded-start"
                                         alt="Product Image">
                                </div>

                                <div class="col-md-8">
                                    <div class="card-body">

                                        <h5 class="cart-title">Order #{{ $order->id }}</h5>
                                        <p class="cart-text">Payment ID: {{ $order->payment_id }}</p>
                                        <p class="cart-text ">Total Price: ৳{{ $order->total_price }} </p>
                                        <h6>Products:</h6>

                                        <ul>
                                            @foreach($order->product_details as $product)

                                                <li>
                                                    <a href="{{route("products.details", $product['slug'])}}">
                                                        {{ $product['name'] }}</a>
                                                    - Quantity: {{ $product['quantity'] }} - Price: ৳{{ $product['price'] }}
                                                </li>

                                            @endforeach
                                        </ul>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>

        </section>
    </main>
@endsection
