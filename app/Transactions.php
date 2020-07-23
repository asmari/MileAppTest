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
    public function origin_data()
    {
        return $this->hasOne(OriginData::class,'transaction_id','transaction_id');
    }
    public function destination_data()
    {
        return $this->hasOne(DestinationData::class,'transaction_id','transaction_id');
    }
    public function location()
    {
        return $this->hasMany(Locations::class,'transaction_id','transaction_id');
    }
    public function currentLocation()
    {
        return $this->hasOne(Locations::class,'transaction_id','transaction_id')->latest();
    }
}
