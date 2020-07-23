<?php

namespace App\Http\Controllers;

use App\ApiCode;
use App\Organizations;
use App\Transactions;
use Illuminate\Http\Request;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class OrganizationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organizations = Organizations::paginate(env('PAGINATION')?:10)->toArray();
        if ($organizations){
            return RB::success($organizations, ApiCode::SUCCESS_OK);
        }
        return RB::error(ApiCode::SUCCESS_NO_CONTENT, ['error' => 'No Data'],['error_message'=>'No Data']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Organizations  $organizations
     * @return \Illuminate\Http\Response
     */
    public function show(Organizations $organizations)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Organizations  $organizations
     * @return \Illuminate\Http\Response
     */
    public function edit(Organizations $organizations)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Organizations  $organizations
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Organizations $organizations)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Organizations  $organizations
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organizations $organizations)
    {
        //
    }
}
