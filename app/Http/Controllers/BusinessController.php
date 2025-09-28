<?php

namespace App\Http\Controllers;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $perPage = request()->query('per_page', 25);
        $businesses = Business::with('owner')->paginate((int)$perPage);
        return response()->json($businesses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors '=> $validator->errors()], 422);
        }

        // Handle product image upload 
        // if ($request->hasFile('logo')) {
        //     $logoPath = $request->file('logo')->store('logos', 'public');
        //     $validator->validated()['logo'] = $logoPath; // Add logo path to validated data
        // }

        $business = Business::create($validator->validated());
        return response()->json($business, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $business = Business::with('owner')->findorFail($id);
        return response()->json($business);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $business = Business::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'owner_id' => 'sometimes|required|exists:users,id',
            'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $business->update($validator->validated());
        return response()->json($business);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $business = Business::findOrFail($id);
        $business->delete();
        return response()->json(['message' => 'Business deleted successfully'], 204);
    }
}
