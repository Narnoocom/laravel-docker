<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveManagerModel extends Model
{
    use HasFactory;

    protected $table = 'leave_manager';

    // Define the relationship with LeaveTypeManager
    public function leaveTypeManager()
    {
        return $this->belongsTo(LeaveTypeManager::class);
    }

    // Define the relationship with StaffMember
    public function staffMember()
    {
        return $this->belongsTo(StaffMember::class);
    }
}
