<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveTypeManager;
use App\Http\Resources\LeaveTypeResourceCollection;

class LeaveTypeManagerController extends Controller
{
    public function getLeaveTypes(){

        $query = [];

        try {
            //Get all our staff members
            $sql = LeaveTypeManager::all();
            
            return new LeaveTypeResourceCollection($sql);

        } catch (\Illuminate\Database\QueryException $ex) {
            
            //Throw a database error and return false
            $query['success'] = (boolean) false;
            $query['error'] = (string) $ex->getMessage();
            
            return response()->json($query);
        }

    }
}
