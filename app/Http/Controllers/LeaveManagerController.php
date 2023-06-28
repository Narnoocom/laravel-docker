<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveManagerModel;
use App\Http\Resources\LeaveManagerResource;
use App\Models\LeaveTypeManager;
use DateTime;
use DateInterval;
use DatePeriod;
use Exception;

class LeaveManagerController extends Controller
{
    /**
     * Return a list of all the leave items
     * 
     * @comment: I am just leaving the pagination at the full amount for load testing. Coming from my docker container on my laptop is slow regardless of database volumes.
     * Ideally the datatable in the react app would be paging the results here.
     * - https://react-data-table-component.netlify.app/?path=/docs/pagination-remote--remote
     */
    public function getAllLeave( Request $request ){
        $query = [];
        
        $perPage = $request->input('per_page', 10);
        if (!is_numeric($perPage)) {
            $perPage = 10;
        } 

        try {

            $leaveManagers = LeaveManagerModel::join('leave_type_manager', 'leave_manager.leave_type_manager_id', '=', 'leave_type_manager.id')
            ->join('staff_member', 'leave_manager.staff_member_id', '=', 'staff_member.id')
            ->select('leave_manager.id as leave_manager_id','leave_manager.created_at','leave_manager.start_date','leave_manager.end_date','leave_manager.leave_days','leave_manager.reason','leave_manager.updated_at','staff_member.id as staff_member_id','staff_member.first_name','staff_member.last_name','leave_type_manager.label as type','leave_type_manager.id as type_manager_id')
            ->orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc')
            ->paginate(10000);
            //->paginate($perPage); - commentted out for load testing all results into the datatable

            return new LeaveManagerResource($leaveManagers);
            

        } catch (\Illuminate\Database\QueryException $ex) {
            
            $query['success'] = (boolean) false;
            $query['error'] = (string) $ex->getMessage();
            
            return response()->json($query);
        }
        
        
    }

    /**
     * Store the leave request
     */
    public function store( Request $request ){

        $query = [];

        try {

            $leave = new LeaveManagerModel;
                
            $leave->staff_member_id = $request->staffMemberId;
            $leave->start_date = date('Y-m-d',strtotime( $request->startDate ) );
            $leave->end_date   = date('Y-m-d',strtotime( $request->endDate ) );
            $leave->reason = trim($request->reason);
            $leave->leave_type_manager_id = $request->leaveTypeManagerId;
            $leave->leave_days = $this->dayCount($request->startDate,$request->endDate);

            $leave->save();


            $query['success'] = (boolean) true;
            $query['data'] = (string) 'Leave successfully stored';

            return response()->json($query);

        } catch (\Illuminate\Database\QueryException $ex) {
                
            $query['success'] = (boolean) false;
            $query['error'] = (string) $ex->getMessage();
            
            return response()->json($query);
        }

    }

    /**
     * Update the leave request
     */
    public function update( Request $request ){

        $query = [];

        try {

            $leave = new LeaveManagerModel;
                
            $leave = LeaveManagerModel::find($request->leaveManagerId);
            
            ( !empty($request->startDate) ) ? $leave->start_date = date('Y-m-d',strtotime( $request->startDate ) ) : null;
            ( !empty($request->endDate)) ? $leave->end_date = date('Y-m-d',strtotime( $request->endDate ) ) : null;
            ( !empty($request->reason)) ? $leave->reason = trim($request->reason) : null;
            ( !empty($request->reasonType)) ?  $leave->leave_type_manager_id = $request->reasonType : null;
            
            if(!empty($request->startDate) && !empty($request->endDate)){
                $leave->leave_days = $this->dayCount($request->startDate,$request->endDate);
            }
            

            $leave->save();

            $query['success'] = (boolean) true;
            $query['data'] = (string) 'Leave successfully stored';

            return response()->json($query);

        } catch (\Illuminate\Database\QueryException $ex) {
                
            $query['success'] = (boolean) false;
            $query['error'] = (string) $ex->getMessage();
            
            return response()->json($query);
        }

    }

    /**
     * Calculate the leave amount for the request
     */
    public function calculateLeave( $startDate, $endDate ){

        try {
            
            $query = [];

            if( empty($startDate) || empty($endDate) ){
                throw new Exception("Please provide a start date and an end date");
            }
            
            $calculate = $this->dayCount($startDate,$endDate);

            $query['success'] = (boolean) true;
            $query['data'] = (int) $calculate;
            
            return response()->json($query);

        
        } catch (\Exception $e) {

            $error = [];
            $error['success'] = (boolean) false;
            $error['error'] = $e->getMessage();
            return response()->json($error);
        }

    }


    /**
     * Search the leave table
     * Date
     * Date Range
     * Staff member
     */
    public function search( Request $request ){

        try {
            
            $leaveManagers = LeaveManagerModel::join('leave_type_manager', 'leave_manager.leave_type_manager_id', '=', 'leave_type_manager.id')
            ->join('staff_member', 'leave_manager.staff_member_id', '=', 'staff_member.id')
            ->select('leave_manager.created_at','leave_manager.start_date','leave_manager.end_date','leave_manager.leave_days','leave_manager.reason','leave_manager.updated_at','staff_member.id as staff_member_id','staff_member.first_name','staff_member.last_name','leave_type_manager.label as type');
            
            if ( $request->filled('staffMemberId') ) {
                $leaveManagers = $leaveManagers->where('staff_member.id',$request->input('staffMemberId'));
                $params['staffMemberId'] = $request->input('staffMemberId');
            }

            if ( $request->filled('startDate') ) {
                $leaveManagers = $leaveManagers->where('leave_manager.start_date',$request->input('startDate'));
                $params['startDate'] = $request->input('startDate');
            }
            
            $result = $leaveManagers->paginate(25);
            
            return response()->json($result);

        } catch (\Exception $e) {
            
            $error = [];
            $error['success'] = (boolean) false;
            $error['error'] = $e->getMessage();
            return response()->json($error);

        }

    }


    //Determine if there are any weekends and remove these from the count
    private function dayCount($start,$end){

        $period = new DatePeriod(
            new DateTime($start),
            new DateInterval('P1D'),
            new DateTime($end)
        );
        
        //Total number of days inclusive of the first day
        $totalCount = iterator_count($period)+1;
        
        //Calculate the number of weekend days and exclude these
        $weekendCount = 0;
        foreach ($period as $key => $value) {
            if ($value->format('N') >= 6) {
                $weekendCount++;
            }  
        }

        //Return the total count of days off minus weekends
        return $totalCount - $weekendCount;

    }
}
