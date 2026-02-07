<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model {
  protected $fillable=['name','price_default','is_active'];
  public function recipe(){ return $this->hasOne(Recipe::class); }
}
