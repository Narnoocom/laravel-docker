<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffMember;
use App\Http\Resources\EmployeesResource;

class StaffController extends Controller
{
    //return a list of the staff members

    public function getAllStaff(){
        $query = []; 

        try {
            //Get all our staff members
            $staff = StaffMember::paginate();

            return new EmployeesResource($staff);

        } catch (\Illuminate\Database\QueryException $ex) {
            
            $query['success'] = (boolean) false;
            $query['error'] = (string) $ex->getMessage();
            
            return response()->json($query);
        }
        
        
    }

}
