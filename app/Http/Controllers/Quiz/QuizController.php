<?php

namespace App\Http\Controllers\Quiz;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\user_answer;
use Illuminate\Http\Request;
use App\Models\Quiz;

class QuizController extends Controller
{
    public static function getAllQuestion() 
    {
        try {
            $quiz = Quiz::with(['answers:option,quiz_id'])->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data keahlian berhasil diambil',
                    'data' => $quiz,
                ],
                200
            );
        }  catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public static function getAllAnswer() 
    {
        try {
            $users = User::with(['user_answers' => function($query) {
                $query->select('user_point', 'user_id', 'answer_id'); // Sesuaikan dengan kolom yang diperlukan
            }])->get();
            
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data keahlian berhasil diambil',
                    'data' => $users,
                ],
                200
            );
        }  catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }
}
