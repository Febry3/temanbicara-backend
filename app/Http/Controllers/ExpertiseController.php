<?php

namespace App\Http\Controllers;

use App\Models\Expertise;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class ExpertiseController extends Controller
{
    public static function getAllExpertise()
    {
        try {

            $expertise = Expertise::with(['user:id,name,email'])
                ->whereHas('user', function ($query) {
                    $query->where('role', 'Counselor');
                })
                ->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data keahlian berhasil diambil',
                    'data' => $expertise,
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
    public static function doExpertise(Request $request)
    {
        try {
            $reqData = $request->only(
                [
                    "type",
                ]
            );
            $validate = Validator::make($reqData, [
                "type" => "required",
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'type tidak diisi',
                    'error' => $validate->errors(),
                ], 200);
            }
            $idUser = $request->user()->id;
            $expertise = Expertise::create([
                'type' => $reqData['type'],
                'user_id' => $idUser,
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Expertise berhasil dibuat',
                    'data' => $expertise,
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
