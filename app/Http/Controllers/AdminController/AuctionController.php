<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuctionController extends Controller
{
    public function index()
    {
        $auctions = Auction::orderBy('id', 'DESC')->get();
        return view('admin.auctions.list', compact('auctions'));
    }

    public function viewForm($id = null)
    {
        try {
            $data = null;
            if (!empty($id)) {
                $data = Auction::findOrFail($id);
            }
            return view('admin.auctions.form', compact('data'));
        } catch (\Throwable $th) {
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'description' => 'required',
                'pdf_file' => 'nullable|mimes:pdf|max:2048', // Adjust the max file size as needed
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }


            $slug = Str::slug('auction', '-');

            $fileName = '';

            if ($request->hasFile('pdf_file')) {
                // Generate a unique image name based on the title and current timestamp
                $fileName = $slug . '-' . now()->format('YmdHis') . '.' . $request->file('pdf_file')->getClientOriginalExtension();

                // Store the image in the uploads/content directory
                $request->file('pdf_file')->move(public_path('uploads/pdf/'), $fileName);
            }

            $Auction = Auction::create([
                'date' => $request->date,
                'description' => $request->description,
                'pdf_file' => $fileName,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Auction successfully stored', 'data' => $Auction]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required',
                'description' => 'required',
                'pdf_file' => 'nullable|mimes:pdf|max:2048', // Adjust the max file size as needed
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }
            $slug = Str::slug('auction', '-');

            $fileName = '';

            $Auction = Auction::findOrFail($id);

            if ($request->hasFile('pdf_file')) {
                // Generate a unique image name based on the title and current timestamp
                $fileName = $slug . '-' . now()->format('YmdHis') . '.' . $request->file('pdf_file')->getClientOriginalExtension();

                // Store the image in the uploads/content directory
                $request->file('pdf_file')->move(public_path('uploads/pdf/'), $fileName);

                // Remove the old image
                if ($Auction->pdf_file) {
                    $oldImagePath = public_path('uploads/pdf/') . $Auction->pdf_file;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
            }

            $Auction = Auction::where('id', $id)->update([
                'date' => $request->date,
                'description' => $request->description,
                'pdf_file' => $fileName,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Auction Successfully Updated', 'data' => $Auction]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            Auction::destroy($id);
            return response()->json(['status' => 'success', 'message' => 'Auction Successfully Deleted']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
}
