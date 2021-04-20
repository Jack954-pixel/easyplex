<?php

namespace App\Http\Controllers;

use App\User;
use App\Plan;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Subscription;
use App\Http\Requests\PlanRequest;
use Laravel\Cashier\Cashier;


class PlanController extends Controller
{


    const STATUS = "status";
    const MESSAGE = "message";
    const VIEWS = "views";


    public function data()
    {


        return response()->json(Plan::all(), 200);

    }



    public function all() {

        $subscriptions = Subscription::query()->active()->get();
        

        return response()->json($subscriptions, 200);
    }



    public function paypal() {

        $users = User::whereNotNull('transaction_id')
        ->orderByDesc('created_at')
        ->get();

        
        return response()->json($users, 200);
    }



    
    public function find() {

        $user = Auth::user();
        $user = Cashier::findBillable($stripeId);

    
        return response()->json($users, 200);
    }




    public function plans()
    {

     $plans = Plan::orderByDesc('id')->get();

     return response()->json(['plans' => $plans], 200);

    }




    public function show($planId)
    {

    return response()->json($planId, 200);

    }




    public function store(PlanRequest $request)
    {


        if (isset($request->plan)) {


            

            $plan = new Plan();
        
            $plan->fill($request->plan);
            $plan->save();

            
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully created',
                'body' => $plan
            ];
        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be created',
            ];

        }

        return response()->json($data, $data[self::STATUS]);
    }


    public function destroy(Plan $ads)
    {
        if ($ads != null) {
            $ads->delete();
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully deleted',
            ];
        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be deleted',
            ];
        }

        return response()->json($data, $data[self::STATUS]);
    }


    public function update(PlanRequest $request, Plan $plan)


    {

        if ($plan != null) {


            $plan->fill($request->plan);
            $plan->save();
            $data = [
                self::STATUS => 200,
                self::MESSAGE => 'successfully updated',
                'body' => $plan
            ];


        } else {
            $data = [
                self::STATUS => 400,
                self::MESSAGE => 'could not be updated',
            ];
        }


        return response()->json($data, $data[self::STATUS]);
    }

}
