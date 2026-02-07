<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeLine extends Model {
  protected $fillable=['recipe_id','item_id','qty','unit_id'];
  public function item(){ return $this->belongsTo(Item::class); }
}
