<?php

namespace App\Http\Livewire;

use App\Models\Product as ProductModel;
use Livewire\Component;
use Carbon\Carbon;
use Livewire\WithPagination;
use DB;

class Cart extends Component
{
    public $search;

    use WithPagination;
    public $payment = 0;

    public $tax = "0%";
    
    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
         $products = ProductModel::where('name', 'like', '%'.$this->search.'%')->orderBy('created_at', 'DESC')->paginate(8);

         $condition = new \Darryldecode\Cart\CartCondition([
            'name' => 'pajak',
            'type' => 'tax',
            'target' => 'total',
            'value' => $this->tax,
            'order' => 1
         ]);

         \Cart::session(Auth()->id())->condition($condition);
         $items = \Cart::session(Auth()->id())->getContent()->sortBy(function ($cart){
             return $cart->attributes->get('added_at');
         });

         if(\Cart::isEmpty()){
             $cartData = [];
         }else{
             foreach($items as $item){
                 $cart[] = [
                     'rowId' => $item->id,
                     'name' => $item->name,
                     'qty' => $item->quantity,
                     'pricesingle' => $item->price,
                     'price' => $item->getPriceSum()
                 ];
             }
             $cartData = collect($cart);
         }

         $sub_total = \Cart::session(Auth()->id())->getSubTotal();
         $total = \Cart::session(Auth()->id())->getTotal();

         $newCondition =  \Cart::session(Auth()->id())->getCondition('pajak');
         $pajak = $newCondition->getCalculatedValue($sub_total);

         $summary = [
             'sub_total' => $sub_total,
             'pajak' => $pajak,
             'total' => $total
         ];

        return view('livewire.cart', [
            'products' => $products,
            'carts' => $cartData,
            'summary' => $summary
        ]);
    }
    public function addItem($id){
        $rowId = "Cart".$id;
        $cart = \Cart::session(Auth()->id())->getContent();
        $cekItemId = $cart->whereIn('id', $rowId);
        
        $idProduct = substr($rowId, 4,5);
        $product = ProductModel::find($idProduct);

        if($cekItemId->isNotEmpty()){
            if($product->qty ==  $cekItemId[$rowId]->quantity){
            session()->flash('error', 'jumlah item kurang');
            }else{
                \Cart::session(Auth()->id())->update($rowId, [
                    'quantity' => [
                        'relative' => true,
                        'value' => 1
                    ]
                ]);
            }
        }else{
            $product = ProductModel::findOrFail($id);
            \Cart::session(Auth()->id())->add([
                'id' => "Cart".$product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
                'attributes' => [
                'added_at' => Carbon::now()
                ]
            ]);
        }
    }
    public function enableTax(){
        $this->tax = "+10%";
    }
    public function disableTax(){
        $this->tax = "0%";
    }
    public function increaseItem($rowId){
        $idProduct = substr($rowId, 4,5);
        $product = ProductModel::find($idProduct);
        $cart = \Cart::session(Auth()->id())->getContent();

        $checkItem = $cart->whereIn('id', $rowId);

        if($product->qty == $checkItem[$rowId]->quantity){
            session()->flash('error', 'jumlah item kurang');
        }else{
            \Cart::session(Auth()->id())->update($rowId, [
            'quantity' => [
                'relative' => true,
                'value' => 1
            ]
           ]);
        }

        
    }
    public function decreaseItem($rowId){
    //   
     $idProduct = substr($rowId, 4,5);
        $product = ProductModel::find($idProduct);
        $cart = \Cart::session(Auth()->id())->getContent();

        $checkItem = $cart->whereIn('id', $rowId);

        if($checkItem[$rowId]->quantity == 1){
           \Cart::session(Auth()->id())->remove($rowId);
        }else{
            \Cart::session(Auth()->id())->update($rowId, [
                'quantity' => [
                    'relative' => true,
                    'value' => -1
            ]
            ]);
        }
        
    }
    public function removeItem($rowId){
         \Cart::session(Auth()->id())->remove($rowId);
    }
    
    public function bayar(){
        $cart_total = \Cart::session(Auth()->id())->getTotal();
        $bayar = $this->payment;
        $cashback = (int)$bayar -  (int)$cart_Total;
        $idProduct = substr($rowId, 4,5);

        if($cashback >= 0){
            DB::beginTransaction();
            try {
                $allCart = \Cart::session(Auth()->id())->getContent();

                $fiterCart = $allCart->map(function($item){
                    return[
                        'id' => substr($rowId, 4,5),
                        'quantity' => $item->quantity
                    ];
                });
                foreach ($filterCart as $cart) {
                    $product = ProductModel::find($cart['id']);
                    if($product->qty === 0){
                        return session()->flash('error', 'jumlah item kurang');
                    }

                    $product->decrement('qty', $cart['quantity']);
                }

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                 return session()->flash('error', 'ada error');
            }
        }
    }
}
