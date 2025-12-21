<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
  use HasFactory;

  protected $table = 'tags';

  protected $fillable = [
    'name',
    'slug',
  ];

  public function orders(): BelongsToMany
  {
    return $this->belongsToMany(Order::class)->withTimestamps();
  }

  public function id(): int
  {
    return $this->getAttribute('id');
  }

  public function name(): string
  {
    return $this->getAttribute('name');
  }

  public function slug(): string
  {
    return $this->getAttribute('slug');
  }
}
