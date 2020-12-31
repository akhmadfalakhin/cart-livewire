<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header mb-3">
                <div class="row ">
                    <div class="col-md-5">
                        <h3 class="font-weight-bold">Products List</h3>
                    </div>
                    <div class="col-md-7 " style="display: flex">
                        <i style="font-size: 30px" class='bx bx-search-alt-2 mr-1'></i>
                        <input wire:model="search" type="text" class="form-control" placeholder="Search">
                    </div>
                </div>
            </div>
            <div class="card-body">
                
                <div class="row">
                    @forelse ($products as $product)
                        <div class="col-md-3 mb-3">
                            <div class="card"  wire:click="addItem({{ $product->id }})" style="cursor: pointer">
                                    <img style="object-fit: containt;height:150px; width: 100%"  src="{{ asset('storage/images/'.$product->image) }}" alt="product">
                                    <button wire:click="addItem({{ $product->id }})" class="btn btn-primary btn-sm " style="position: absolute;top:0;right:0;padding:5px 10px">
                                       <i class="fas fa-cart-plus fa-lg"></i>
                                    </button>
                                    <h6 class="text-center font-weight-bold">{{ $product->name }}</h6>
                                    <h6 class="text-center text-muted font-weight-bold">Rp {{ number_format($product->price,2,',','.')  }}</h6>
                            </div>
                        </div>
                    @empty
                    <div class="col-sm-12 mt-5 mb-5">
                        <h3 class="text-center text-primary">Product Not Found</h3>
                    </div>
                    @endforelse
                </div>
            </div>
            <div style="display: flex; justify-content: center" >
            {{ $products->links() }}
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="font-weight-bold">Cart </h3>
            </div>
            <div class="card-body">
                @if (session()->has('error'))
                <p class="text-danger font-weight-bolder">    
                    {{ session('error') }}
                </p>
                @endif
                <table class="table table-sm table-bordered table-hover">
                    <thead class="bg-white">
                        <tr>
                            <th class="font-weight-bold">No</th>
                            <th class="font-weight-bold">Nama</th>
                            <th class="font-weight-bold">Qty</th>
                            <th class="font-weight-bold">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($carts as $index=>$cart)
                            <tr>
                                <td>
                                    {{ $index + 1 }} <br>
                                    <span style="cursor: pointer;" wire:click="removeItem('{{$cart['rowId']}}')" class="text-gray rounded"> <i class='p-1 bx bx-trash' ></i></span>
                                </td>
                                <td>
                                    <a href="#" class="font-weight-bold" style="color: #333; text-decoration: none;">{{ $cart['name'] }}</a>
                                    <br>
                                    Rp {{ number_format($cart['pricesingle'],2,',','.')  }}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" wire:click="decreaseItem('{{$cart['rowId']}}')" style="display: inline;padding:0.3rem 0.4rem!important"><i class="fas fa-minus"></i></button>
                                    {{ $cart['qty'] }}
                                    <button class="btn btn-sm btn-primary"  wire:click="increaseItem('{{$cart['rowId']}}')" style="display: inline;padding:0.3rem 0.4rem!important"><i class="fas fa-plus"></i></button>
                                </td>
                                <td>Rp {{ number_format($cart['price'],2,',','.')  }}</td>
                            </tr>
                        @empty
                            <td colspan="4"><h6 class="text-center">Empty Cart</h6></td>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card mt-3">
            <div class="card-body">
                <h4 class="font-weight-bold">Cart Summary</h4>
                <h5 class="font-weight-bold">Sub Total: Rp {{number_format($summary['sub_total'],2,',','.')  }}</h5>
                <h5 class="font-weight-bold">Pajak: Rp {{number_format($summary['pajak'],2,',','.')  }}</h5>
                <h5 class="font-weight-bold">Totall: Rp {{number_format($summary['total'],2,',','.')  }}</h5>
                <div class="row">
                    <div class="col-sm-6">
                        <button wire:click="enableTax" class="btn btn-primary btn-block btn-sm">Add Pajak</button>
                    </div>
                    <div class="col-sm-6">
                        <button wire:click="disableTax" class="btn btn-info btn-block btn-sm">Remove Pajak</button>
                    </div>
                </div>
                <div class="form-group mt-4">
                    <input wire:model="payment" type="number" class="form-control " id="payment" placeholder="Input Customer payment amount">
                    <input type="hidden" id="total" value="{{ $summary['total'] }}">
                </div>
                <form action="" wire::submit.prevent="bayar">
                    <div>
                        <label for="">payment</label>
                        <h1 id="paymentText" wire:ignore>Rp. 0</h1>
                    </div>
                    <div>
                        <label for="">Cashback</label>
                        <h1 id="cashbackText" wire:ignore>Rp. 0 </h1>
                    </div>
                    <div class="mt-2">
                        <button type="submit" wire:ignore id="saveTransaction" disabled class="btn btn-success btn-block"><i class="fas fa-save"></i> Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('script-custom')
    <script>
       payment.oninput = () => {
           const paymentAmount = document.getElementById("payment").value
           const totalAmount = document.getElementById("total").value

           const cashback = paymentAmount - totalAmount

           document.getElementById("cashbackText").innerHTML = `Rp ${rupiah(cashback)},00`
           document.getElementById("paymentText").innerHTML = `Rp ${rupiah(paymentAmount)},00`

           const saveButton = document.getElementById("saveTransaction")

           if(cashback < 0){
               saveTransaction.disabled = true
           }else{
               saveTransaction.disabled = false
           }
       }

       const rupiah = (angka) => {
           const numberString = angka.toString()
           const split = numberString.split(',')
           const sisa = split[0].length % 3
           let rupiah = split[0].substr(0, sisa)
           const ribuan = split[0].substr(sisa).match(/\d{1,3}/gi)

           if(ribuan){
               const separator = sisa ? '.' : ''
               rupiah += separator + ribuan.join('.')
            
           }
           return split[1] != undefined ? rupiah + ',' + split[1] :rupiah
       }
    </script>
@endpush