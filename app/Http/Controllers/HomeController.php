<?php

namespace App\Http\Controllers;

use App\Models\AmOrder;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
     public function index()
    {
        $order_count = AmOrder::count();
        $product_count = Product::count();

        return view('home', compact('order_count', 'product_count'));
    }
}
