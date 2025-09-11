<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable=['type','site','field_type', 'dataset','data_source', 'code', 'title', 'placeholder', 'value', 'status'];

}
