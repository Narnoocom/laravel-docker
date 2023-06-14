<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeManager extends Model
{
    use HasFactory;

    protected $table = 'leave_type_manager';

    // Define the relationship with LeaveManager
    public function leaveManager()
    {
        return $this->hasOne(LeaveManagerModel::class);
    }
}
