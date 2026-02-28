<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = ['name', 'birth_date', 'country'];

    public function books()
    {
        return $this->belongsToMany(Book::class);
    }
}
