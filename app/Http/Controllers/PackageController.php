<?php
namespace App\Http\Controllers;

use App\ApiCode;
use App\Connotes;
use App\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $transactions = Transactions::where('user_id',$user->id)
            ->orderBy('id','desc')
            ->paginate(env('PAGINATION')?:2)
            ->toArray();

        if ($transactions){
            return RB::success($transactions, ApiCode::SUCCESS_OK);
        }
        return RB::error(ApiCode::SUCCESS_NO_CONTENT, ['error' => 'No Data'],['error_message'=>'No Data']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->postRule());

        if ($validator->fails()){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Post Data'],
                ['error_message'=>$validator->messages()->toArray()]
            );
        }
//        Validate Total Koli Data
        if(count($request->koli_data)!=$request->connote['connote_total_package']){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Post Data'],
                ['error_message'=>"Connotes Total Package Didn't match Koli Data"]
            );
        }

        $user = auth()->user();
        $customer = $user->customer_id()->first();
        DB::beginTransaction();
        try {
//        Insert Transaction Data
            $transactionID = DB::table('transactions')->insertGetId($this->generateTransactionData($request,$user));
            $transaction = Transactions::find($transactionID);

//            Insert Connotes Data
            $connoteID = DB::table('connotes')->insertGetId($this->generateConnotesData($request,$transaction));
            $connote = Connotes::find($connoteID);

//            Insert Koli Data
            DB::table('koli_data')->insert($this->generateKoliData($request->koli_data,$connote));

//            Insert Locations Data
            DB::table('locations')->insert($this->generateLocationData($customer->customer_name,'Origin',
                $transaction->transaction_code,$transaction->transaction_id));

//            Insert Origin Data
            DB::table('origin_data')->insertGetId($this->generateOriginData($request,$transaction));

//            Insert Destination Data
            DB::table('destination_data')->insertGetId($this->generateDestinationData($request,$transaction));

//            Commit if no error
            DB::commit();
            return RB::success($transaction, ApiCode::SUCCESS_OK);

        } catch (\Exception $e) {
//            Rollback if something wrong with query
            DB::rollback();
            return RB::error(ApiCode::CLIENT_BAD_REQUEST, ['error' => 'Failed Post Data'],['error_message'=>$e->getMessage()]);
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
            $transactions->customer_attributes=['nama_sales' => $user->name,'top' => $transactions->top,'jenis_pelanggan'=>$transactions->jenis_pelanggan];
//            Get Connotes Data
            $transactions->connotes = $transactions->connotes->first();
//            Get Koli Data
            $transactions->koli_data =  $transactions->connotes()->first()->koliData->all();
//            Get Origin Data
            $transactions->origin_data =  $transactions->origin_data->first();
//            Get Destination Data
            $transactions->destination_data =  $transactions->destination_data->first();

            $transactions->currentLocation->first();

            return RB::success($transactions, ApiCode::SUCCESS_OK);
        }
        return RB::error(ApiCode::SUCCESS_NO_CONTENT, ['error' => 'No Data'],['error_message'=>'No Data']);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->postRule(true));

        if ($validator->fails()){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Post Data'],
                ['error_message'=>$validator->messages()->toArray()]
            );
        }

        $user = auth()->user();
        $customer = $user->customer_id()->first();
        $transaction = Transactions::find($id);
        if (!$transaction){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Post Data'],
                ['error_message' => "Failed delete Data"]
            );
        }
        DB::beginTransaction();
        try {
//        Update Transaction Data
            DB::table('transactions')->where('id',$id)->update($this->generateTransactionData($request,$user,
                true));

//            Update Connotes Data
            $connoteID = $transaction->connotes->id;
            DB::table('connotes')->where('id',$connoteID)->update
            ($this->generateConnotesData($request,
                $transaction,true));

//            Update Origin Data
            DB::table('origin_data')->where('id',$transaction->origin_data->id)->update($this->generateOriginData
            ($request,$transaction,true));

//            Update Destination Data
            DB::table('destination_data')->where('id',$transaction->destination_data->id)->update
            ($this->generateDestinationData($request,$transaction,true));

//            Commit if no error
            DB::commit();
            return RB::success($transaction, ApiCode::SUCCESS_OK);

        } catch (\Exception $e) {
//            Rollback if something wrong with query
            DB::rollback();
            return RB::error(ApiCode::CLIENT_BAD_REQUEST, ['error' => 'Failed Post Data'],['error_message'=>$e->getMessage()]);
        }
        return RB::error(ApiCode::CLIENT_BAD_REQUEST, ['error' => 'Failed Post Data'],['error_message'=>'Failed Post Data']);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function updateTracking(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'=>['required'],
            'type'=>['required'],
            'code'=>['required']
        ]);

        if ($validator->fails()){
            return RB::error(
                ApiCode::CLIENT_PRECONDITION_FAILED,
                ['error' => 'Invalid Post Data'],
                ['error_message'=>$validator->messages()->toArray()]
            );
        }

        $agent = auth()->user()->hasRole('Admin');
        if (!$agent){

            return RB::error(ApiCode::CLIENT_FORBIDDEN, ['error' => 'Forbidden'],['error_message'=>'Forbidden! Only Agent can access this API!']);
        }
        DB::beginTransaction();
        try {
            $transaction = Transactions::find($id);

//            Insert Location Data
            DB::table('locations')->insert($this->generateLocationData($request->name,$request->type,
                $request->code,$transaction->transaction_id));

//            Commit if no error
            DB::commit();
            return RB::success($transaction, ApiCode::SUCCESS_OK);

        } catch (\Exception $e) {
//            Rollback if something wrong with query
            DB::rollback();
            return RB::error(ApiCode::CLIENT_BAD_REQUEST, ['error' => 'Failed Post Data'],['error_message'=>$e->getMessage()]);
        }
        return RB::error(ApiCode::CLIENT_BAD_REQUEST, ['error' => 'Failed Post Data'],['error_message'=>'Failed Post Data']);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transactions  $transactions
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $transaction = Transactions::find($id);
        if ($transaction) {
            if ($transaction->delete()) {
                return RB::success($transaction, ApiCode::SUCCESS_OK);
            } else {
                return RB::error(
                    ApiCode::CLIENT_PRECONDITION_FAILED,
                    ['error' => 'Invalid Post Data'],
                    ['error_message' => "Failed delete Data"]
                );
            }
        }

        return RB::error(
            ApiCode::CLIENT_NOT_FOUND,
            ['error' => 'Transaction Data Not Found'],
            ['error_message' => "Transaction Data Not Found"]
        );
    }
    protected function postRule($update=false){
        $ret =[
//            Transaction Validation
            'transaction_amount' => ['required'],
            'transaction_payment_type' => ['required','integer'],
            'transaction_cash_amount' => ['required','integer'],
            'transaction_cash_change' => ['required','integer'],
            'transaction_discount' => ['required','integer'],
            'transaction_code' => ['required'],
            'transaction_order' => ['required','integer'],
            'transaction_payment_type_name' => ['required'],
            'top' => ['required'],
            'jenis_pelanggan' => ['required'],

//          Connotes Validation
            'connote.connote_number' => ['required','integer'],
            'connote.connote_service' => ['required'],
            'connote.connote_service_price' => ['required','integer'],
            'connote.connote_amount' => ['required','integer'],
            'connote.connote_booking_code' => ['required'],
            'connote.connote_order' => ['required','integer'],
            'connote.connote_state' =>  ['required'],
            'connote.connote_state_id' =>  ['required','integer'],
            'connote.zone_code_from' =>  ['required'],
            'connote.zone_code_to' =>  ['required'],
            'connote.actual_weight' => ['required','integer'],
            'connote.volume_weight' => ['required','integer'],
            'connote.chargeable_weight' => ['required','integer'],
            'connote.organization_id' =>  ['required','integer','exists:App\Organizations,id'],
            'connote.location_id' =>  ['required'],
            'connote.connote_total_package' =>  ['required'],
            'connote.connote_surcharge_amount' =>  ['required'],
            'connote.connote_sla_day' =>  ['required'],
            'connote.location_name' =>  ['required'],
            'connote.location_type' => ['required'],
            'connote.source_tariff_db' => ['required'],
            'connote.id_source_tariff' => ['required'],

//            validate koli
            'koli_data' => ['required'],

//            Validate Origin Data
            'origin_data'=>['required'],
            'origin_data.customer_name'=>['required'],
            'origin_data.customer_address'=>['required'],
            'origin_data.customer_phone'=>['required'],
            'origin_data.customer_zip_code'=>['required'],
            'origin_data.zone_code'=>['required'],
            'origin_data.organization_id'=>['required','integer'],
            'origin_data.location_id'=>['required'],


            'destination_data'=>['required'],
            'destination_data.customer_name'=>['required'],
            'destination_data.customer_address'=>['required'],
            'destination_data.customer_phone'=>['required'],
            'destination_data.customer_zip_code'=>['required'],
            'destination_data.zone_code'=>['required'],
            'destination_data.organization_id'=>['required','integer'],
            'destination_data.location_id'=>['required'],
        ];
        if ($update){
            unset($ret['koli_data']);
        }
        return $ret;
    }

    protected function generateConnotesData($request, $transaction,$update=false){

        $uuidConnotes = (String) Str::uuid();
        $id=sprintf("%03d", $transaction->id);
        $AWB = "AWB".$id.date('dmY');
        $ret =[
            "connote_id"=> $uuidConnotes,
            "connote_number"=> $request->connote['connote_number'],
            "connote_service"=> $request->connote['connote_service'],
            "connote_service_price"=> $request->connote['connote_service_price'],
            "connote_amount"=> $request->connote['connote_amount'],
            "connote_code"=>  $AWB,
            "connote_booking_code"=> $request->connote['connote_booking_code'],
            "connote_order"=>  $request->connote['connote_order'],
            "connote_state"=>  $request->connote['connote_state'],
            "connote_state_id"=>  $request->connote['connote_state_id'],
            "zone_code_from"=>  $request->connote['zone_code_from'],
            "zone_code_to"=>  $request->connote['zone_code_to'],
            "surcharge_amount"=>  $request->connote['surcharge_amount'],
            "transaction_id"=> $transaction->transaction_id,
            "actual_weight"=> $request->connote['actual_weight'],
            "volume_weight"=> $request->connote['volume_weight'],
            "chargeable_weight"=> $request->connote['chargeable_weight'],
            "organization_id"=>  $request->connote['organization_id'],
            "location_id"=>  $request->connote['location_id'],
            "connote_total_package"=>  $request->connote['connote_total_package'],
            "connote_surcharge_amount"=>  $request->connote['connote_surcharge_amount'],
            "connote_sla_day"=>  $request->connote['connote_sla_day'],
            "location_name"=>  $request->connote['location_name'],
            "location_type"=> $request->connote['location_type'],
            "source_tariff_db"=> $request->connote['source_tariff_db'],
            "id_source_tariff"=> $request->connote['id_source_tariff'],
            "pod"=> $request->connote['pod'],
            "history"=>  json_encode($request->connote['history']),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        if ($update){
            unset($ret['created_at']);
            unset($ret['transaction_id']);
        }
        return  $ret;
    }

    protected function generateTransactionData($request,$user,$update=false){
        $ret = [
            'transaction_id' => (String) Str::uuid(),
            'user_id' => $user->id,
            'transaction_amount' => $request->transaction_amount,
            'transaction_discount' => $request->transaction_discount,
            'transaction_additional_field' => $request->transaction_additional_field,
            'transaction_payment_type' => $request->transaction_payment_type,
            'transaction_cash_amount' => $request->transaction_cash_amount,
            'transaction_cash_change' => $request->transaction_cash_change,
            'transaction_state' => $request->transaction_state,
            'transaction_code' => $request->transaction_code,
            'transaction_order' => $request->transaction_order,
            'transaction_payment_type_name' => $request->transaction_payment_type_name,
            'top' => $request->top,
            'jenis_pelanggan' => $request->jenis_pelanggan,
            'custom_field' => json_encode($request->custom_field),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
        if ($update){
            unset($ret['created_at']);
            unset($ret['transaction_id']);
        }
        return $ret ;
    }
    protected function generateLocationData($name ,$type,$code, $transactionID, $update=false){
        $ret = [
            'location_id' => (String) Str::uuid(),
            'name' => $name,
            'type' => $type,
            'code' => $code,
            'transaction_id' => $transactionID,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ] ;
        if ($update){
            unset($ret['created_at']);
            unset($ret['transaction_id']);
        }
        return $ret;
    }
    protected function generateOriginData($request,$transaction,$update=false){
        $ret = [
            'transaction_id'=>$transaction->transaction_id,
            'customer_name'=>$request->origin_data['customer_name'],
            'customer_address'=>$request->origin_data['customer_address'],
            'customer_email'=>$request->origin_data['customer_email'],
            'customer_phone'=>$request->origin_data['customer_phone'],
            'customer_address_detail'=>$request->origin_data['customer_address_detail'],
            'customer_zip_code'=>$request->origin_data['customer_zip_code'],
            'zone_code'=>$request->origin_data['zone_code'],
            'organization_id'=>$request->origin_data['organization_id'],
            'location_id'=>(String) Str::uuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        if ($update){
            unset($ret['created_at']);
            unset($ret['transaction_id']);
        }
        return  $ret;
    }
    protected function generateDestinationData($request,$transaction, $update=false){
        $ret = [
            'transaction_id'=>$transaction->transaction_id,
            'customer_name'=>$request->destination_data['customer_name'],
            'customer_address'=>$request->destination_data['customer_address'],
            'customer_email'=>$request->destination_data['customer_email'],
            'customer_phone'=>$request->destination_data['customer_phone'],
            'customer_address_detail'=>$request->destination_data['customer_address_detail'],
            'customer_zip_code'=>$request->destination_data['customer_zip_code'],
            'zone_code'=>$request->destination_data['zone_code'],
            'organization_id'=>$request->destination_data['organization_id'],
            'location_id'=>(String) Str::uuid(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ] ;
        if ($update){
            unset($ret['created_at']);
            unset($ret['transaction_id']);
        }
        return $ret;
    }
    protected function generateKoliData($request, $connote,$update=false){

        $ret = [];
        foreach ($request as $key=>$val){
            $koliAwb =$key+1;
            $ret[$key]["koli_length"]= $val["koli_length"];
            $ret[$key]["awb_url"]= env("AWB_URL").$connote->connote_code.".".$koliAwb;
            $ret[$key]["koli_chargeable_weight"]= $val["koli_chargeable_weight"];
            $ret[$key]["koli_width"]= $val["koli_width"];
            $ret[$key]["koli_surcharge"]= json_encode($val["koli_surcharge"]);
            $ret[$key]["koli_height"]= $val["koli_height"];
            $ret[$key]["koli_description"]= $val["koli_description"];
            $ret[$key]["koli_formula_id"]= $val["koli_formula_id"];
            $ret[$key]["connote_id"]= $connote->connote_id;
            $ret[$key]["koli_volume"]= $val["koli_volume"];
            $ret[$key]["koli_weight"]= $val["koli_weight"];
            $ret[$key]["koli_id"]= (String) Str::uuid();
            $ret[$key]["koli_custom_field"]= json_encode($val["koli_custom_field"]);
            $ret[$key]["koli_code"]= $connote->connote_code.".".$koliAwb;
            if (!$update) {
                $ret[$key]["created_at"] = Carbon::now();
            }
            $ret[$key]["updated_at"] = Carbon::now();
        }
        return $ret;

    }
}
