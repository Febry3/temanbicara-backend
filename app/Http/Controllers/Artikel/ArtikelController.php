<?php

namespace App\Http\Controllers\Artikel;

use App\Http\Controllers\Controller;
use App\Models\artikel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class ArtikelController extends Controller
{
    public static function getArtikel(Request $request){
        try {
            $artikels = Artikel::with('user:id,name,role')->get();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Artikel berhasil dibuat',
                    'data' => $artikels,
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
    public static function doArtikel(Request $request){
        try {
            $reqData = $request->only(
                [
                    "title",
                    "content",
                    "image"
                ]
            );
            $validate = Validator::make($reqData, [
                "title"=> "required",
                "content" => "required",
                "image"=> "required"
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ada bagian yang tidak diisi',
                    'error' => $validate->errors(),
                ], 200);
            };
            //ganti sesuai id user lu
            $idUser = 12;
            $artikels=artikel::create([
                'title'=> $reqData['title'],
                'content'=> $reqData['content'],
                'image'=> $reqData['image'],
                'user_id'=> $idUser,
                //ini buat ngambil id artikel yg login yg diatas masih dummy ganti sesuai id user lu
                //'user_id' => $request->user()->id,
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Artikel berhasil dibuat',
                    'data' => $artikels,
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
