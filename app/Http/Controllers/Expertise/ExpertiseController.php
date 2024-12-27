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
            $idUser = 4;
            $Expertise=Expertise::create([
                'type' => $reqData['type'],
                'user_id' => $idUser,
                //ini buat ngambil id artikel yg login yg diatas masih dummy ganti sesuai id user lu
                //'user_id' => $request->user()->id,
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

    public function getArtikelById($id)
{
    try {
        $artikel = Artikel::with('user:id,name')->findOrFail($id);
        $artikel->created_at = Carbon::parse($artikel->created_at)->format('Y-m-d');
        return response()->json([
            'status' => true,
            'message' => 'Artikel berhasil ditemukan',
            'data' => $artikel,
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'Artikel tidak ditemukan',
        ], 404);
    }
}

}
