<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'student_id',
        'class_id',
        'section_id',
        'photo',
    ];

    /**
     * Get the class that the student belongs to.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the section that the student belongs to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the attendances for the student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get attendance percentage for a specific date range
     */
    public function getAttendancePercentage($startDate = null, $endDate = null)
    {
        $query = $this->attendances();
        
        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('date', '<=', $endDate);
        }
        
        $totalDays = $query->count();
        $presentDays = $query->where('status', 'present')->count();
        
        return $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;
    }
}
