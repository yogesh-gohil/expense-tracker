<?php

namespace App\Models;

use App\Builder\IncomeBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    /** @use HasFactory<\Database\Factories\IncomeFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function newEloquentBuilder($builder)
    {
        return new IncomeBuilder($builder);
    }
}
