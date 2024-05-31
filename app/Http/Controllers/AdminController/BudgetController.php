<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BudgetController extends Controller
{
    public function index()
    {
        $budgets = Budget::orderBy('id', 'DESC')->get();
        return view('admin.budgets.list', compact('budgets'));
    }

    public function viewForm($id = null)
    {
        try {
            $data = null;
            if (!empty($id)) {
                $data = Budget::findOrFail($id);
            }
            return view('admin.budgets.form', compact('data'));
        } catch (\Throwable $th) {
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'no_details' => 'required',
                'details' => 'required',
                'pdf_file' => 'nullable|mimes:pdf|max:2048', // Adjust the max file size as needed
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }

            $slug = Str::slug('budget', '-');

            $fileName = '';

            if ($request->hasFile('pdf_file')) {
                // Generate a unique image name based on the title and current timestamp
                $fileName = $slug . '-' . now()->format('YmdHis') . '.' . $request->file('pdf_file')->getClientOriginalExtension();

                // Store the image in the uploads/content directory
                $request->file('pdf_file')->move(public_path('uploads/pdf/'), $fileName);
            }


            $Budget = Budget::create([
                'no_details' => $request->no_details,
                'details' => $request->details,
                'pdf_file' => $fileName,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Budget successfully stored', 'data' => $Budget]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'no_details' => 'required',
                'details' => 'required',
                'pdf_file' => 'nullable|mimes:pdf|max:2048', // Adjust the max file size as needed
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }


            $Budget = Budget::findOrFail($id);

            $slug = Str::slug('budget', '-');

            $fileName = '';

            if ($request->hasFile('pdf_file')) {
                // Generate a unique image name based on the title and current timestamp
                $fileName = $slug . '-' . now()->format('YmdHis') . '.' . $request->file('pdf_file')->getClientOriginalExtension();

                // Store the image in the uploads/content directory
                $request->file('pdf_file')->move(public_path('uploads/pdf/'), $fileName);

                // Remove the old image
                if ($Budget->pdf_file) {
                    $oldImagePath = public_path('uploads/pdf/') . $Budget->pdf_file;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $Budget = Budget::where('id', $id)->update([
                'no_details' => $request->no_details,
                'details' => $request->details,
                'pdf_file' => $fileName
            ]);

            return response()->json(['status' => 'success', 'message' => 'Budget Successfully Updated', 'data' => $Budget]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            Budget::destroy($id);
            return response()->json(['status' => 'success', 'message' => 'Budget Successfully Deleted']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
}
