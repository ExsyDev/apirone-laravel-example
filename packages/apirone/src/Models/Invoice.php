<?php

namespace Apirone\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * @var string[]
     */
    protected $guarded = ['id'];

    /**
     * @var string[]
     */
    protected $casts = [
        'details' => 'json',
        'meta' => 'json'
    ];

    public function getTable()
    {
        return config('apirone.table_prefix') . 'apirone_invoice';
    }
}
