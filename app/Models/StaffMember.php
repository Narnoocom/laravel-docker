<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffMember extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'staff_member';
    /**
     * 
     * I left timestamps out of this table considering it was required.
     * 
     */
    public $timestamps = false;

    // Define the relationship with LeaveManager
    public function leaveManager()
    {
        return $this->hasMany(LeaveManagerModel::class);
    }
}
