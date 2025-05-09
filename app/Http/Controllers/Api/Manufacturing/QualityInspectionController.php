<?php

namespace App\Http\Controllers\Api\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\Manufacturing\QualityInspection;
use App\Models\Manufacturing\QualityParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class QualityInspectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        $query = QualityInspection::query();

        // Optional: add filters if needed, e.g. status, search, date_filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_type', 'like', "%{$search}%")
                  ->orWhere('reference_id', 'like', "%{$search}%");
            });
        }
        if ($request->has('date_filter') && $request->date_filter !== '') {
            // Implement date filter logic here if needed
            // For example, filter by inspection_date based on date_filter value
        }

        $qualityInspections = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $qualityInspections->items(),
            'meta' => [
                'current_page' => $qualityInspections->currentPage(),
                'last_page' => $qualityInspections->lastPage(),
                'per_page' => $qualityInspections->perPage(),
                'total' => $qualityInspections->total(),
            ],
        ]);
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
            'reference_type' => 'required|string|max:50',
            'reference_id' => 'required|integer',
            'inspection_date' => 'required|date',
            'status' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'parameters' => 'sometimes|array',
            'parameters.*.parameter_name' => 'required|string|max:100',
            'parameters.*.specification' => 'required|string',
            'parameters.*.actual_value' => 'required|string',
            'parameters.*.is_passed' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $inspection = QualityInspection::create([
                'reference_type' => $request->reference_type,
                'reference_id' => $request->reference_id,
                'inspection_date' => $request->inspection_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            if ($request->has('parameters')) {
                foreach ($request->parameters as $parameter) {
                    QualityParameter::create([
                        'inspection_id' => $inspection->inspection_id,
                        'parameter_name' => $parameter['parameter_name'],
                        'specification' => $parameter['specification'],
                        'actual_value' => $parameter['actual_value'],
                        'is_passed' => $parameter['is_passed'],
                    ]);
                }
            }

            DB::commit();
            
            return response()->json([
                'data' => $inspection->load('qualityParameters'), 
                'message' => 'Quality inspection created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create quality inspection', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $inspection = QualityInspection::with('qualityParameters')->find($id);
        
        if (!$inspection) {
            return response()->json(['message' => 'Quality inspection not found'], 404);
        }
        
        return response()->json(['data' => $inspection]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $inspection = QualityInspection::find($id);
        
        if (!$inspection) {
            return response()->json(['message' => 'Quality inspection not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'reference_type' => 'sometimes|required|string|max:50',
            'reference_id' => 'sometimes|required|integer',
            'inspection_date' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|max:50',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $inspection->update($request->all());
        
        return response()->json([
            'data' => $inspection, 
            'message' => 'Quality inspection updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $inspection = QualityInspection::find($id);
        
        if (!$inspection) {
            return response()->json(['message' => 'Quality inspection not found'], 404);
        }

        DB::beginTransaction();
        try {
            // Delete quality parameters first
            $inspection->qualityParameters()->delete();
            
            // Then delete the inspection
            $inspection->delete();
            
            DB::commit();
            return response()->json(['message' => 'Quality inspection deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete quality inspection', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource for a specific reference.
     *
     * @param  string  $referenceType
     * @param  int  $referenceId
     * @return \Illuminate\Http\Response
     */
    public function byReference($referenceType, $referenceId)
    {
        $inspections = QualityInspection::with('qualityParameters')
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->get();
            
        return response()->json(['data' => $inspections]);
    }
}