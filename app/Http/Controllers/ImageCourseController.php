<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ImageCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageCourseController extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'image' => 'required|url',
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

        $imagecourse = ImageCourse::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $imagecourse
        ]);
    }

    public function destroy($id)
    {
        $imagecourse = ImageCourse::find($id);

        if(!$imagecourse){
            return response()->json([
                'status' => 'error',
                'message' => 'image course not found'
            ], 404);
        }
        
        $imagecourse->delete();
        
        return response()->json([
            'status' => 'success',
            'data' => 'image course successfully deleted'
        ]);
    }

}
