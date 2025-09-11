<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use DB;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $settingData = Setting::where('type', $request->platform)->where('status', 1)->get();
        $selectData=[];
        foreach ($settingData as $setting) {
         
            if (!empty($setting->data_source)) {
                    $source = explode('-', $setting->data_source);
                    if (count($source) === 3) {
                        [$table, $fieldStr, $selectStr] = $source;
                        $field = explode(',', $fieldStr);
                        $select = explode(',', $selectStr);
                        
                        if (count($field) === 2 && (count($select) === 1 || count($select) === 2)) {
                            $query = DB::table($table);
                           
                            if ($field[0] !== '*') {
                                $query->where($field[0], $field[1]);
                            }
                            
                            if (count($select) === 1) {
                                $result = $query->pluck($select[0]);
                            } else {
                                $result = $query->pluck($select[0], $select[1]);
                            }
                            
                           $selectData[$setting->code]=$result;
                        
                        } else {
                           
                        }
                    } else {
                        
                    }
                }
            if (($setting->field_type == 'select' || $setting->field_type == 'multiple') && empty($setting->dataset)) {
                $setting->dataset = json_encode([]); 
            }

        }

        return view('settings.form')->with([
            'settingData' => $settingData,
            'dataSets'=>$selectData,
            'platform' => ucfirst($request->platform),
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        foreach ($request->except(['_token', 'platform']) as $key => $row) {
            Setting::where('code', $key)->update(['value' => $row]);
        }

        return redirect()->route('setting.index',['platform' => strtolower($request->platform)])->with(['success_message' => 'Settings Updated Successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
