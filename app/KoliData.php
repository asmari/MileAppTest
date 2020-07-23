<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KoliData extends Model
{
    protected $table = 'koli_data';
    public $timestamps = true;
    public function connotes()
    {
        return $this->belongsTo(Connotes::class, 'connote_id', 'connote_id');
    }
}
