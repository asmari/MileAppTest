<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $table = 'transactions';
    public $timestamps = true;
    public function connotes()
    {
        return $this->hasOne(Connotes::class,'transaction_id','transaction_id');
    }
}
