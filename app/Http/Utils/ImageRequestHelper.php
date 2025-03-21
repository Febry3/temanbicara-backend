<?php

namespace App\Http\Utils;

use Illuminate\Http\Request;
use Error;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Throwable;


//note: ganti jadi throwable
class ImageRequestHelper
{
    public static function postImageToSupabase(Request $request)
    {

        $requestedData = $request->only($request->all(), [
            'image'
        ]);

        $validateData = Validator::make(
            $requestedData,
            [
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]
        );

        if ($validateData->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validateData->errors(),
            ], 400);
        }

        $image = $request->file('image');

        //cek imagenya ada atau tidak
        if (!$image) {
            throw new Exception("Image can't be empty");

            // return response()->json([
            //     'status' => false,
            //     'message' => 'Image cant be empty',
            // ], 400);
        }

        //cek tipe
        if (!in_array($image->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid image type. Only JPEG, JPG, and PNG are allowed',
            ], 400);
        }

        //cek size < 2mb
        if ($image->getSize() > 2048 * 1024) {
            return response()->json([
                'status' => false,
                'message' => 'Image size should be less than 2MB',
            ], 400);
        }


        $imagePath = uniqid() . '.' . $image->getClientOriginalExtension();

        $imageContent = fopen($image->getRealPath(), 'r');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('supabase.key'),
            'Content-Type' => $image->getMimeType(),
            'Cache-Control' => 'public, max-age=31536000',
        ])->send('POST', config('supabase.url') . '/profile/' . $imagePath, [
            'body' => $imageContent
        ]);

        fclose($imageContent);

        if (!$response->ok()) {
            return response()->json([
                'status' => false,
                'message' => 'Error in uploading image',
            ], 400);
        }
        return $response;
    }
}
