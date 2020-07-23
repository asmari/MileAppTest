<?php
namespace App\Http\Controllers;

use App\ApiCode;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class PackageController extends Controller{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();

//        Get all Transaction by user_id
        $transactions = Transactions::where('user_id',$user->id)->paginate(env('PAGINATION')?:10)->toArray();
        if ($transactions){
            return RB::success($transactions, ApiCode::SUCCESS_OK);
        }
        return RB::error(ApiCode::SUCCESS_NO_CONTENT, ['error' => 'No Data'],['error_message'=>'No Data']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_amount' => ['required'],
            'transaction_payment_type' => ['required'],
            'transaction_cash_amount' => ['required'],
            'transaction_cash_change' => ['required'],
            'transaction_discount' => ['required'],
            'transaction_code' => ['required'],
            'transaction_order' => ['required'],
            'transaction_payment_type_name' => ['required'],
        ]);

        if ($validator->fails()){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Login Details'],
                ['error_message'=>$validator->messages()->toArray()]
            );
        }


        $uuid = (String) Str::uuid();
        $user = auth()->user();

//        Insert Transaction Data
        $transaction = new Transactions();
        $transaction->transaction_id = $uuid;
        $transaction->user_id = $user->id;
        $transaction->transaction_amount = $request->transaction_amount;
        $transaction->transaction_discount = $request->transaction_discount;
        $transaction->transaction_additional_field = $request->transaction_additional_field;
        $transaction->transaction_payment_type = $request->transaction_payment_type;
        $transaction->transaction_cash_amount = $request->transaction_cash_amount;
        $transaction->transaction_cash_change = $request->transaction_cash_change;
        $transaction->transaction_state = $request->transaction_state;
        $transaction->transaction_code = $request->transaction_code;
        $transaction->transaction_order = $request->transaction_order;
        $transaction->transaction_payment_type_name = $request->transaction_payment_type_name;
        $transaction->save();
        if ($transaction){
            return RB::success($transaction, ApiCode::SUCCESS_OK);
        }
        return RB::error(ApiCode::CLIENT_BAD_REQUEST, ['error' => 'Failed Post Data'],['error_message'=>'Failed Post Data']);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return void
     */
    public function show($id)
    {
        $user = auth()->user();

//        Get Transaction data by ID
        $transactions = Transactions::where('id',$id)->first();
        if ($transactions){
            if ($transactions->user_id!=$user->id){
                return RB::error(ApiCode::CLIENT_FORBIDDEN, ['error' => 'No Data'],['error_message'=>'Forbidden!']);
            }
            $customer = $user->customer_id()->first();
            $transactions->customer_name = $customer->customer_name;
            $transactions->customer_code = $customer->customer_code;
            $transactions->customer_attributes=['Nama_Sales' => $user->name,'TOP' => $user->email,'Jenis_Pelanggan'=>'B2B'];

            return RB::success($transactions, ApiCode::SUCCESS_OK);
        }
        return RB::error(ApiCode::SUCCESS_NO_CONTENT, ['error' => 'No Data'],['error_message'=>'No Data']);
    }
}
