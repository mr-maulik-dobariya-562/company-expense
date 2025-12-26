<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        {{-- <li class="nav-item">
            <a class="btn btn-sm btn-primary" href="{{ route('notify-mrn-emails') }}" role="button">
                MRN Reminder <i class="fas fa-bell" ></i>
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="btn btn-sm btn-primary" href="{{ route('notify-payment-emails') }}" role="button">
                Payment Reminder <i class="fas fa-bell" ></i>
            </a>
        </li> --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('logout') }}" role="button">
                Logout
            </a>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
