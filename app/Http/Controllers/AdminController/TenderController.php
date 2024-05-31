<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class TenderController extends Controller
{
    public function index()
    {
        $tenders = Tender::orderBy('id', 'DESC')->get();
        return view('admin.tender.list', compact('tenders'));
    }

    public function viewForm($id = null)
    {
        try {
            $data = null;
            if (!empty($id)) {
                $data = Tender::findOrFail($id);
            }
            return view('admin.tender.form', compact('data'));
        } catch (\Throwable $th) {
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'department' => 'required',
                'diary_no' => 'required',
                'tender_date' => 'required',
                'opening_date' => 'required',
                'pdf_file' => 'nullable|mimes:pdf|max:2048', // Adjust the max file size as needed
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }

            // Handle file upload
            // $filePath = null;
            // if ($request->hasFile('pdf_file')) {
            //     $file = $request->file('pdf_file');
            //     $fileName = time() . '_' . $file->getClientOriginalName();
            //     $filePath = $file->storeAs('pdf_files', $fileName); // Assuming 'pdf_files' is your storage path
            // }

            $slug = Str::slug($request->input('title'), '-');

            $fileName = '';

            if ($request->hasFile('pdf_file')) {
                // Generate a unique image name based on the title and current timestamp
                $fileName = $slug . '-' . now()->format('YmdHis') . '.' . $request->file('pdf_file')->getClientOriginalExtension();

                // Store the image in the uploads/content directory
                $request->file('pdf_file')->move(public_path('uploads/pdf/'), $fileName);
            }

            $tender = Tender::create([
                'description' => $request->description,
                'department' => $request->department,
                'diary_no' => $request->diary_no,
                'tender_date' => $request->tender_date,
                'opening_date' => $request->opening_date,
                'pdf_file' => $fileName,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Tender successfully stored', 'data' => $tender]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'department' => 'required',
                'diary_no' => 'required',
                'tender_date' => 'required',
                'opening_date' => 'required',
                'pdf_file' => 'nullable|mimes:pdf|max:2048', // Adjust the max file size as needed
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }

            // Handle file upload
            // $filePath = null;
            // if ($request->hasFile('pdf_file')) {
            //     $file = $request->file('pdf_file');
            //     $fileName = time() . '_' . $file->getClientOriginalName();
            //     $filePath = $file->storeAs('pdf_files', $fileName); // Assuming 'pdf_files' is your storage path
            // }


            $slug = Str::slug($request->input('title'), '-');

            $tender = Tender::findOrFail($id);

            if ($request->hasFile('pdf_file')) {
                // Generate a unique image name based on the title and current timestamp
                $fileName = $slug . '-' . now()->format('YmdHis') . '.' . $request->file('pdf_file')->getClientOriginalExtension();

                // Store the image in the uploads/content directory
                $request->file('pdf_file')->move(public_path('uploads/pdf/'), $fileName);

                // Remove the old image
                if ($tender->pdf_file) {
                    $oldImagePath = public_path('uploads/pdf/') . $tender->pdf_file;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $tender->update([
                'description' => $request->description,
                'department' => $request->department,
                'diary_no' => $request->diary_no,
                'tender_date' => $request->tender_date,
                'opening_date' => $request->opening_date,
                'pdf_file' => $fileName,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Tender Successfully Updated', 'data' => $tender]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            Tender::destroy($id);
            return response()->json(['status' => 'success', 'message' => 'Tender Successfully Deleted']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
}
