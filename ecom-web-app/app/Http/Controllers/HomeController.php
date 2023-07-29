<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;

use Session;

use Stripe;


class HomeController extends Controller
{

    public function index(){

        return view('home.userpage');
    }

    public function redirect(){

        $usertype=Auth::user()->usertype;
        if($usertype=='1'){
            return view('admin.home');

        }
        else{
            return view('home.userpage');
        }
    }

    public function view_shop(){

        $product=Product::all();
        return view('home.shop',compact('product'));
    }

    public function contact(){

        return view('home.contact');
    }

    public function about(){

        return view('home.about');
    }

    public function blog(){

        return view('home.blog');
    }
    

    public function product_details($id){

        $product=Product::find($id);

        return view('home.product_details',compact('product'));
    }

    public function add_cart(Request $request,$id){
        if(Auth::id()){

            $user=Auth::User();
            $product=product::find($id);
            $cart=new cart;
            $cart->name=$user->name;
            $cart->email=$user->email;
            $cart->phone=$user->phone;
            $cart->address=$user->address;
            $cart->user_id=$user->id;

            $cart->product_title=$product->title;
            $cart->price=$product->price * $request->quantity;
            $cart->image=$product->image;
            $cart->product_id=$product->id;
            $cart->quantity=$request->quantity;

            $cart->save();

            return redirect()->back();

        }
        else{
            return redirect('login');
        }
    }

 
    public function view_cart()
    {
        
         $cart = [];
    
        if (Auth::id()) {
            $id = Auth::user()->id;
            $cart = Cart::where('user_id', '=', $id)->get();
            return view('home.cart', ['cart' => $cart]);
        } else {
            return redirect('login');
        }

            return view('home.cart');
        }
    

    public function remove_cart($id){
        $cart=cart::find($id);
        $cart->delete();
        return redirect()->back();

    }

    public function cash_on(){

        $user=Auth::user();
        $userid=$user->id;
        $data=cart::where('user_id','=',$userid)->get();

        foreach($data as $data){

        $order=new order;
        $order->name=$data->name;
        $order->email=$data->email;
        $order->phone=$data->phone;
        $order->address=$data->address;
        $order->user_id=$data->user_id;

        $order->product_title=$data->product_title;
        $order->price=$data->price ;
        $order->quantity=$data->quantity;
        $order->image=$data->image;
        $order->product_id=$data->product_id;

        $order->payment_status='cash on delivery';
        $order->delivery_status='processing';

        $order->save();

        $cart_id=$data->id;
        $cart=cart::find($cart_id);
        $cart->delete();


        }

        return view('home.cash_on');
    }


    public function stripe($totalprice){

        return view('home.stripe',compact('totalprice'));
    }

    public function stripePost(Request $request,$totalprice)

    {

        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

    

        Stripe\Charge::create ([

                "amount" => $totalprice ,

                "currency" => "usd",

                "source" => $request->stripeToken,

                "description" => "Thanks For Payment" 

        ]);

         $user=Auth::user();
        $userid=$user->id;
        $data=cart::where('user_id','=',$userid)->get();

        foreach($data as $data){

        $order=new order;
        $order->name=$data->name;
        $order->email=$data->email;
        $order->phone=$data->phone;
        $order->address=$data->address;
        $order->user_id=$data->user_id;

        $order->product_title=$data->product_title;
        $order->price=$data->price ;
        $order->quantity=$data->quantity;
        $order->image=$data->image;
        $order->product_id=$data->product_id;

        $order->payment_status='Paid';
        $order->delivery_status='processing';

        $order->save();

        $cart_id=$data->id;
        $cart=cart::find($cart_id);
        $cart->delete();


        }


        Session()->flash('success', 'Payment successful!');

              

        return view('home.cash_on');

    }
}


