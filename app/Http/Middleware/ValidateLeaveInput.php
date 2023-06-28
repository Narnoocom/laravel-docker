<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class ValidateLeaveInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next): Response
    {   

        try {
            $data = json_decode( $request->getContent(), true );
             //Check to make sure all the required data is present.
            $params = ['startDate','endDate','reason','staffMemberId','leaveTypeManagerId'];
            foreach($params as $each){
                if(!isset($data[$each])){
                    throw new Exception("Please check your parameters");
                }
            }
            //Check that the start date is greater than today
            if(!$this->checkStartDate($data['startDate'])){
                throw new Exception("Start date needs to be greater than today");
            }
            //Check that the end date is after the start date.
            if(!$this->checkEndDate($data['startDate'],$data['endDate'])){
                throw new Exception("End date needs to be greater than start date");
            }
            //Check that reason is not empty
            if(empty($data['reason'])){
                throw new Exception("Please provide a reason");
            }
            if(!$this->checkReasonLength($data['reason'])){
                throw new Exception("The reason text is too long.");
            }

            //Check that staff memberId is not empty
            if(empty($data['staffMemberId'])){
                throw new Exception("Please provide a staff member id");
            }
            //Check that leave type manager id is not empty
            if(empty($data['leaveTypeManagerId'])){
                throw new Exception("Please provide a leave type id");
            }
            if($this->checkOverlappingDates($data['startDate'],$data['endDate'])){
                throw new Exception("These dates overlap with an already scheduled leave request");
            }
           

            return $next($request);

        } catch (\Exception $e ) {

            $error = [];
            $error['success'] = (boolean) false;
            $error['error'] = $e->getMessage();
            return response()->json($error);
        }
        
    }

    /**
     * We want to check for overlapping dates.
     * - assumption is that if the start date falls between any other leave request 
     *   then it would be considered overlapping.
     */
    private function checkOverlappingDates($startDate,$endDate){
        try {
            
            $query = DB::table('leave_manager')
                        ->whereBetween('start_date',[$startDate, $endDate])
                        ->orWhereBetween('end_date',[$startDate, $endDate])
                        ->get();

            return ( count($query) > 0 ) ? TRUE : FALSE;

        } catch (\Illuminate\Database\QueryException $ex) {
            return false;//$ex->getMessage();
        }

    }


    /**
     * Check reason length
     */
    private function checkReasonLength($string){
        $lenght = 50; // could be an env variable.
        if(strlen( trim($string) ) > $lenght){
            return false;
        }else{
            return true;
        }
    }

    /**
     * Check start date
     */
    private function checkStartDate($date){

        $now = date('Y-m-d');
        $startDate = date( 'Y-m-d', strtotime($date) );

        if($startDate < $now){
            return false;
        }else{
            return true;
        }

    }
    /**
     * Checks that the end date is before the start date
     */
    private function checkEndDate($start,$end){
        $startDate = date( 'Y-m-d', strtotime($start) );
        $endDate = date( 'Y-m-d', strtotime($end) );

        if($endDate < $startDate){
            return false;
        }else{
            return true;
        }
    }
}
