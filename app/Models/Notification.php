<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
  protected $fillable = [
    'title',
    'message',
    'button_name',
    'button_url'
  ];
}
