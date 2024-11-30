<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">

        <a class="navbar-brand" href="{{route("home")}}">Deep Shop</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarText">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="{{route("home")}}">Home</a>
                </li>

                @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{route("cart.show")}}">Cart</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{route("order.history")}}">Orders</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{route("logout")}}">Logout</a>
                    </li>
                @endauth

            </ul>
        </div>
    </div>
</nav>
