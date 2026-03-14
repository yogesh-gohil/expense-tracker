<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public const TYPE_EXPENSE = 'EXPENSE';

    public const TYPE_INCOME = 'INCOME';

    protected $guarded = ['id'];

    protected $appends = ['amount'];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAmountAttribute()
    {
        return $this->expenses()->sum('amount');
    }
}
