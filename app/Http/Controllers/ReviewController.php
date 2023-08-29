<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'user_id' => 'required|integer',
            'course_id' => 'required|integer',
            'rating' => 'required|integer|min:1|max:5',
            'note' => 'string',
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

        $user = getUser($request->user_id);

        if($user["status"] === "error"){
            return response()->json([
                'status' => $user["status"],
                'message' => $user["message"]
            ], $user["http_code"]);
        }

        $isExistReview = Review::where('course_id', $request->course_id)
                                    ->where('user_id', $request->user_id)->exists();

        if ($isExistReview) {
            return response()->json([
                'status' => "error",
                'message' => "user already reviewed this course"
            ], 409);
        }

        $review = Review::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

    public function update(Request $request, $id)
    {
        $review = Review::find($id);
    
        if(!$review){
            return response()->json([
                'status' => 'error',
                'message' => 'review not found'
            ], 404);
        }

        $rules = [
            'rating' => 'integer|min:1|max:5',
            'note' => 'string',
        ];

        $data = $request->except('user_id', 'course_id');

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $review->fill($data);
        $review->save();

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

    public function destroy($id)
    {
        try {
            $review = Review::find($id);
    
            if(!$review){
                return response()->json([
                    'status' => 'error',
                    'message' => 'reviews not found'
                ], 404);
            }
            
            $review->delete();
            
            return response()->json([
                'status' => 'success',
                'data' => 'reviews successfully deleted'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
