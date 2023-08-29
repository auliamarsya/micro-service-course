<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\MyCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MyCourseController extends Controller
{
    public function index(Request $request)
    {
        $mycourse = MyCourse::query()->with('course');

        $user = $request->query('user_id');

        $mycourse->when($user, function($query) use ($user){
            return $query->where('user_id', $user);
        });

        return response()->json([
            'status' => 'success',
            'data' => $mycourse->get()
        ]);
    }

    public function create(Request $request)
    {
        try {
            $rules = [
                'user_id' => 'required|integer',
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
    
            $user = getUser($request->user_id);
    
            if($user["status"] === "error"){
                return response()->json([
                    'status' => $user["status"],
                    'message' => $user["message"]
                ], $user["http_code"]);
            }
    
            $isExistMyCourse = MyCourse::where('course_id', $request->course_id)
                                        ->where('user_id', $request->user_id)->exists();
    
            if ($isExistMyCourse) {
                return response()->json([
                    'status' => "error",
                    'message' => "user already taken this course"
                ], 409);
            }
    
            if($course->type == 'premium'){
                $order = postOrder([
                    "user" => $user["data"],
                    "course" => $course->toArray()
                ]);
          
                if($order["status"] === "error"){
                    return response()->json([
                        'status' => $order["status"],
                        'message' => $order["message"]
                    ], 500);
                }
    
                return response()->json([
                    'status' => $order["status"],
                    'data' => $order["data"]
                ]);
            } else {
                $mycourse = MyCourse::create($data);
    
                return response()->json([
                    'status' => 'success',
                    'data' => $mycourse
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
                "data" => []
            ]);
        }
        
    }

    public function createPremiumAccess(Request $request){
        try {
            $data = $request->all();
            $mycourse = MyCourse::create($data);
    
            return response()->json([
                'status' => 'success',
                'data' => $mycourse
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => "error",
                "message" => $th->getMessage(),
                "data" => []
            ]);
        }

    }
}
