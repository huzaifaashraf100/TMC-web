<?php

namespace App\Http\Controllers\AdminController;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventsMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventsController extends Controller
{
    public function viewForm()
    {
        try {
            return view('admin.events.form');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function viewList()
    {
        try {
            $data = Event::orderBy('id', 'DESC')->get();
            return view('admin.events.list', compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function viewUpdateForm($id)
    {
        try {
            $data = Event::with('eventMedia')->where('id', $id)->first();
            return view('admin.events.form', compact('data'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'file' => 'required',
            ]);
            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }
            // Create Slug name
            $slug = Str::slug($request->input('title'), '-');

            $event = Event::create([
                'title' => $request->input('title'),
            ]);


               // Save Image
               $files = $request->file('file');

               foreach ($files as $key  => $files) {
                   $imageName = $slug . '-' . now()->format('YmdHis') . $key . '.webp';
                   $files->move(public_path('uploads/content/'), $imageName);
                   EventsMedia::create([
                       'file_name' => $imageName,
                       'path' => 'uploads/content/',
                       'type' => 'menu',
                       'event_id' => $event->id,
                   ]);
               }

            return response()->json(['status' => 'success', 'message' => 'Event successfully stored', 'data' => $event]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something went wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
            ]);

            if ($validator->fails()) {
                $errorString = implode('<br>', $validator->errors()->all());
                return response()->json(['status' => 'danger', 'message' => 'Validation Error', 'error' => $errorString]);
            }
            // Create Slug name
            $slug = Str::slug($request->input('title'), '-');

            $event = Event::where('id', $id)->update([
                'title' => $request->input('title'),
            ]);


            $event = Event::where('id', $id)->first();


            if (!empty($request->input('removeMedia'))) {
                // Remove Media

                foreach ($request->input('removeMedia') as $id) {
                     try {
                    $removedFile = EventsMedia::findOrFail($id);
                    $oldImagePath = public_path('uploads/content/'. $removedFile->file_name) ;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    $removedFile->delete();
                     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                        // Handle the case where the record is not found (log, return response, etc.)
                        return response()->json(['status' => 'danger', 'message' => 'Media record not found', 'error' => $e->getMessage()]);
                }
                }
            }


            if (!empty($request->file('file'))) {
                // Save Image
                $files = $request->file('file');
                foreach ($files as $key => $file) {
                    $imageName = $slug . '-' . now()->format('YmdHis') . $key . '.webp';
                    $file->move(public_path('uploads/content/'), $imageName);
                    EventsMedia::create([
                        'file_name' => $imageName,
                        'path' => 'uploads/content/',
                        'type' => 'menu',
                        'event_id' => $event->id,
                    ]);
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Event successfully updated', 'data' => $event]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something went wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
    public function destory($id)
    {
        try {

            $event = Event::findOrFail($id);

            $eventsMedia = EventsMedia::where('event_id', $event->id)->get();
            foreach ($eventsMedia as $eventMedia) {

                $oldImagePath = public_path('uploads/content/') . $eventMedia->file_name;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $eventMedia->delete();
            }

            Event::destroy($id);
            return response()->json(['status' => 'success', 'message' => 'Event Successfully Deleted']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'danger', 'message' => 'Something wrong in the DB', 'error' => $th->getMessage()]);
        }
    }
}
