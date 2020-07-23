<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Connotes extends Model
{
    protected $table = 'connotes';
    public $timestamps = true;

    public function transactions()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id', 'transaction_id');
    }

    public function koliData()
    {
        return $this->hasMany(KoliData::class,'connote_id','connote_id');
    }
}
