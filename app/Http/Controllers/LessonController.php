<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $lesson = Lesson::query();

        $chapter = $request->query('chapter_id');

        $lesson->when($chapter, function($query) use ($chapter){
            return $query->where('chapter_id', $chapter);
        });

        return response()->json([
            'status' => 'success',
            'data' => $lesson->get()
        ]);
    }

    public function show($id)
    {
        $lesson = Lesson::find($id);

        if(!$lesson){
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'video' => 'required|string',
            'chapter_id' => 'required|integer',
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $chapter = Chapter::find($request->chapter_id);

        if(!$chapter){
            return response()->json([
                'status' => 'error',
                'message' => 'chapter not found'
            ], 404);
        }

        $lesson = Lesson::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function update(Request $request, $id)
    {
        $lesson = Lesson::find($id);
    
        if(!$lesson){
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }

        $rules = [
            'name' => 'string',
            'video' => 'string',
            'chapter_id' => 'integer',
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        if($request->chapter_id){
            $chapter = Chapter::find($request->chapter_id);

            if(!$chapter){
                return response()->json([
                    'status' => 'error',
                    'message' => 'chapter not found'
                ], 404);
            }
        }

        $lesson->fill($data);
        $lesson->save();

        return response()->json([
            'status' => 'success',
            'data' => $lesson
        ]);
    }

    public function destroy($id)
    {
        $lesson = Lesson::find($id);

        if(!$lesson){
            return response()->json([
                'status' => 'error',
                'message' => 'lesson not found'
            ], 404);
        }
        
        $lesson->delete();
        
        return response()->json([
            'status' => 'success',
            'data' => 'lesson successfully deleted'
        ]);
    }
}
