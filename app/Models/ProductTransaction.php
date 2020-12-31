<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    use HasFactory;

    protected $table = 'product_transaction';

    protected $guarded = [];

    public function product(){
        return $this->belongTo(Product::class);
    }
}
