<?php

namespace App\Http\Controllers\Expertise;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Expertise;
use Validator;

class ExpertiseController extends Controller
{
    public static function getAllExpertise()
    {
        try {

            $Expertise = Expertise::with(['user:id,name,email'])
                ->whereHas('user', function ($query) {
                    $query->where('role', 'Counselor');
                })
                ->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data keahlian berhasil diambil',
                    'data' => $Expertise,
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
    public static function doExpertise(Request $request){
        try {
            $reqData = $request->only(
                [
                    "type",
                ]
            );
            $validate = Validator::make($reqData, [
                "type"=> "required",
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'type tidak diisi',
                    'error' => $validate->errors(),
                ], 200);
            };
            //ganti sesuai id user lu
            $idUser = $request->user()->id;
            $Expertise=Expertise::create([
                'type' => $reqData['type'],
                'user_id' => $idUser,
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Expertise berhasil dibuat',
                    'data' => $Expertise,
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
}
