<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactProblems;
use App\Models\Map;
use App\Models\PostsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        try {
            $problems = ContactProblems::get();

            $contents = PostsMedia::where('type', 'map')->get();
            $map = Map::where('id', 2)->first();

            return view('fronts.contact', compact(['problems','contents','map']));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required',
                'phone' => 'required',
                'problem' => 'required',
                'message' => 'required',
            ]);
            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }

            $create = Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'problem' => $request->problem,
                'message' => $request->message,
                'visitor' => $request->ip(),
            ]);
            return response()->json(['status' => 'success', 'message' => 'Thank you for reaching out to us! We appreciate your interest in TMC. Your message has been received, and our team will get back to you as soon as possible.']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
}
