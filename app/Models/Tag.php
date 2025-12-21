<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Tag Model
 *
 * Basic properties:
 * - @property int $id
 * - @property string $name
 * - @property string $slug
 */
class Tag extends Model
{
  use HasFactory;

  protected $table = 'tags';

  protected $fillable = [
    'name',
    'slug',
  ];

  /**
   * The orders that belong to the tag.
   *
   * @return BelongsToMany
   */
  public function orders(): BelongsToMany
  {
    return $this->belongsToMany(Order::class)->withTimestamps();
  }

  /**
   * Get the tag's ID.
   *
   * @return int
   */
  public function id(): int
  {
    return $this->getAttribute('id');
  }

  /**
   * Get the tag's name.
   *
   * @return string
   */
  public function name(): string
  {
    return $this->getAttribute('name');
  }

  /**
   * Get the tag's slug.
   *
   * @return string
   */
  public function slug(): string
  {
    return $this->getAttribute('slug');
  }
}
