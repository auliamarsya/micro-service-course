<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\Mentor;
use App\Models\MyCourse;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $course = Course::query();

        $q = $request->query('q');
        $status = $request->query('status');

        $course->when($q, function($query) use ($q){
            return $query->where('name','LIKE','%'.Str::lower($q).'%');
        })->when($status, function($query) use ($status){
            return $query->where('status', $status);
        });

        return response()->json([
            'status' => 'success',
            'data' => $course->paginate(10)
        ]);
    }

    public function show($id)
    {
        $course = Course::with(["chapters.lessons", "mentor", "images",])->find($id);

        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }

        
        $reviews = Review::where("course_id", $id)->get()->toArray();

        if (count($reviews) > 0) {
            $userIds = array_column($reviews, 'user_id');
            $users = getUserByIds($userIds);

            if ($users["status"] === "error") {
                $reviews = [];
            } else {
                foreach ($reviews as $key => $review) {
                    $userIndex = array_search($review['user_id'], array_column($users["data"], "id"));
                    
                    $reviews[$key]["users"] = $users["data"][$userIndex];
                }
            }
        }


        $totalStudent = MyCourse::where("course_id", $id)->count();
        $totalVideo = Chapter::where("course_id", $id)->withCount('lessons')->get()->toArray();
        $totalFinalVideo = array_sum(array_column($totalVideo, "lessons_count")); 
        
        $course['reviews'] = $reviews;
        $course['total_videos'] = $totalFinalVideo;
        $course['total_student'] = $totalStudent;

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function create(Request $request)
    {
        $rules = [
            'name' => 'required|string', 
            'certificate' => 'required|boolean', 
            'thumbnail' => 'string|url', 
            'type' => 'required|in:free,premium', 
            'status' => 'required|in:draft,published', 
            'price' => 'integer|gt:0', 
            'level' => 'required|in:all-level,beginner,intermediate,advance', 
            'description' => 'string', 
            'mentor_id' => 'required|integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $mentor = Mentor::find($request->mentor_id);

        if(!$mentor){
            return response()->json([
                'status' => 'error',
                'message' => 'mentor not found'
            ], 404);
        }

        $course = Course::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::find($id);
    
        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }

        $rules = [
            'name' => 'string', 
            'certificate' => 'boolean', 
            'thumbnail' => 'string|url', 
            'type' => 'in:free,premium', 
            'status' => 'in:draft,published', 
            'price' => 'integer', 
            'level' => 'in:all-level,beginner,intermediate,advance', 
            'description' => 'string', 
            'mentor_id' => 'integer'
        ];

        $data = $request->all();

        $validator = Validator::make($data, $rules);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        if($request->mentor_id){
            $mentor = Mentor::find($request->mentor_id);
    
            if(!$mentor){
                return response()->json([
                    'status' => 'error',
                    'message' => 'mentor not found'
                ], 404);
            }
        }

        $course->fill($data);
        $course->save();

        return response()->json([
            'status' => 'success',
            'data' => $course
        ]);
    }

    public function destroy($id)
    {
        $course = Course::find($id);

        if(!$course){
            return response()->json([
                'status' => 'error',
                'message' => 'course not found'
            ], 404);
        }
        
        $course->delete();
        
        return response()->json([
            'status' => 'success',
            'data' => 'course successfully deleted'
        ]);
    }

}
