<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\Property;
use Illuminate\Http\Request;

use App\DataTables\PropertiesDataTable;
use View;
use Validator;
use Redirect;
use Storage;
use File;
use Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function getProperties(PropertiesDataTable $dataTable)
    {
        return $dataTable->render('property.index');
    }

    public function index()
    {
        //
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
        $user = Auth::user()->landlords->is_upgraded;
        // dd($user);
        if ($user == 1) {

            $validator = Validator::make($request->all(), Property::$rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator);
            }

            if ($validator->passes()) {
                $path = Storage::putFileAs('images/property', $request->file('imagePath'), $request->file('imagePath')->getClientOriginalName());

                $request->merge(["imagePath" => $request->file('imagePath')->getClientOriginalName()]);

                if ($file = $request->hasFile('imagePath')) {
                    $props = new Property;
                    $props->landlord_id = Auth::user()->landlords->id;
                    $props->area = $request->area;
                    $props->garage = $request->garage;
                    $props->bathroom = $request->garage;
                    $props->bedroom = $request->bedroom;
                    $props->rent = $request->rent;
                    $props->city = $request->city;
                    $props->state = $request->state;
                    $props->address = $request->address;
                    $props->description = $request->description;

                    $file = $request->file('imagePath');
                    $fileName = $file->getClientOriginalName();
                    $destinationPath = public_path() . '/images';
                    $props->imagePath = 'images/' . $fileName;
                    $props->save();
                    $file->move($destinationPath, $fileName);

                    return redirect()->back();
                }

            }
        } else {
            return redirect()->back()->with('warning', 'Need to upgrade');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function show(Property $property)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // dd($id);
        $property = Property::find($id);

        $landlord = Landlord::with('properties')->where('id', $property->landlord_id)->get();

        return view('property.edit', compact('property', 'landlord'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $props = Property::find($id);
        
        if ($file = $request->hasFile('imagePath')) {
            $path = Storage::putFileAs('images/property', $request->file('imagePath'), $request->file('imagePath')->getClientOriginalName());
            $request->merge(["imagePath" => $request->file('imagePath')->getClientOriginalName()]);
            
            $props->area = $request->area;
            $props->garage = $request->garage;
            $props->bathroom = $request->garage;
            $props->bedroom = $request->bedroom;
            $props->rent = $request->rent;
            $props->city = $request->city;
            $props->state = $request->state;
            $props->address = $request->address;
            $props->description = $request->description;

            $file = $request->file('imagePath');
            $fileName = $file->getClientOriginalName();
            $destinationPath = public_path() . '/images';
            $props->imagePath = 'images/' . $fileName;
            $props->update();
            $file->move($destinationPath, $fileName);

            return redirect()->back()->with('success', 'Property is successfully updated!');
        } else {
            $props->area = $request->area;
            $props->garage = $request->garage;
            $props->bathroom = $request->garage;
            $props->bedroom = $request->bedroom;
            $props->rent = $request->rent;
            $props->city = $request->city;
            $props->state = $request->state;
            $props->address = $request->address;
            $props->description = $request->description;
            $props->update();
            return redirect()->back()->with('success', 'Property is successfully updated!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $property = Property::findOrFail($id);
        $property->forceDelete();

        return Redirect::route('getProperties')->with('danger', 'Property has been Deleted!');
    }

    public function deactivate($id)
    {
        $property = Property::findOrFail($id);
        $property->delete();
        // dd($customer);

        return Redirect::route('getProperties')->with('warning', 'Property has been Deactivated!');
    }

    public function restore($id)
    {
        $property = Property::withTrashed()->find($id);
        $property->restore();
        return Redirect::route('getProperties')->with('success', 'Property has been Restored!');
    }


    //// Dashboard
    public function getDashboard()
    {
        $properties = Property::withTrashed()
        ->where([
            ['is_taken', '=', 0],
            ['is_approved', '=', 1],
        ])
        ->get();

        return view('homepage', compact('properties'));
    }

    public function approval(Request $request, $id)
    {
       $property = Property::withTrashed()->find($id);
       $property->is_approved = 1;
       $property->update();

       return redirect()->back()->with('success', 'Request post has been approved');
    }

    public function taken(Request $request, $id)
    {
       $property = Property::withTrashed()->find($id);
       $property->is_taken = 1;
       $property->update();

       return redirect()->back()->with('success', 'Your property is already taken');
    }

    public function available(Request $request, $id)
    {
       $property = Property::withTrashed()->find($id);
       $property->is_taken = 0;
       $property->update();

       return redirect()->back()->with('success', 'Your property is now available');
    }
}