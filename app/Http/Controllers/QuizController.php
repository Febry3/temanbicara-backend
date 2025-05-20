<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\user_answer;
use Illuminate\Http\Request;
use App\Models\Quiz;

class QuizController extends Controller
{
    public static function getAllQuestion()
    {
        try {
            $quiz = Quiz::with(['answers:option,point,quiz_id'])->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data Soal berhasil diambil',
                    'data' => $quiz,
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }

    public static function getAllAnswer(Request $request)
    {
        try {

            $answers = user_answer::where('user_id', $request->user()->id)->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data jawaban berhasil diambil',
                    'data' => $answers,
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ],
                500
            );
        }
    }

    public static function storeAnswer(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'user_point' => 'required|integer',
            ]);

            $answerUser = user_answer::create([
                'user_id' => $validatedData['user_id'],
                'user_point' => $validatedData['user_point'],
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data keahlian berhasil diambil',
                    'data' => $answerUser,
                ],
                200
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ],
                500
            );
        }
    }
    public static function getMaxPoints()
    {
        $quizzes = Quiz::all();
        $maxPoints = 0;

        foreach ($quizzes as $quiz) {
            $answers = Answer::where('quiz_id', $quiz->quiz_id)->orderBy('point', 'desc')->first();
            if ($answers) {
                $maxPoints += $answers->point;
            }
        }

        return response()->json(['maxPoints' => $maxPoints]);
    }
}
