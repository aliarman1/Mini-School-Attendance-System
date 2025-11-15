<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'capacity',
        'description',
    ];

    /**
     * Get the students for this class.
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
