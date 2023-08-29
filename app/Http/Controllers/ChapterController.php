<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDO;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $chapter = Chapter::query();

        $course = $request->query('course_id');

        $chapter->when($course, function($query) use ($course){
            return $query->where('course_id', $course);
        });

        return response()->json([
            'status' => 'success',
            'data' => $chapter->get()
        ]);
    }

    public function show($id)
    {
        $chapter = Chapter::find($id);

        if(!$chapter){
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'course_id' => 'required|integer',
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $course = Course::find($request->course_id);

        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }

        $chapter = Chapter::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function update(Request $request, $id)
    {
        $chapter = Chapter::find($id);
    
        if(!$chapter){
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }

        $rules = [
            'name' => 'string',
            'course_id' => 'integer',
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        if($request->course_id){
            $course = Course::find($request->course_id);

            if(!$course){
                return response()->json([
                    'status' => 'error',
                    'message' => 'course not found'
                ], 404);
            }
        }

        $chapter->fill($data);
        $chapter->save();

        return response()->json([
            'status' => 'success',
            'data' => $chapter
        ]);
    }

    public function destroy($id)
    {
        $chapter = Chapter::find($id);

        if(!$chapter){
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }
        
        $chapter->delete();
        
        return response()->json([
            'status' => 'success',
            'data' => 'chapter successfully deleted'
        ]);
    }
}
