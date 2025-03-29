<?php

namespace App\Http\Helper;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;


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
            throw new Exception($validateData->errors());
        }

        $image = $request->file('image');

        //cek imagenya ada atau tidak
        if (!$image) {
            throw new Exception("Image can't be empty");
        }

        //cek tipe
        if (!in_array($image->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
            throw new Exception("Invalid image type. Only JPEG, JPG, and PNG are allowed");
        }

        //cek size < 2mb
        if ($image->getSize() > 2048 * 1024) {
            throw new Exception("Image size should be less than 2MB");
        }


        $imagePath = uniqid() . '_' . time() . '.' . $image->getClientOriginalExtension();

        $imageContent = fopen($image->getRealPath(), 'r');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('supabase.key'),
            'Content-Type' => $image->getMimeType(),
            'Cache-Control' => 'public, max-age=31536000',
        ])->send('POST', config('supabase.url') . '/profile/' . $imagePath, [
            'body' => $imageContent
        ]);

        fclose($imageContent);

        if ($response->failed()) {
            throw new Exception("Error in uploading image");
        }
        return $response;
    }

    public static function deleteImageFromSupabase(string $imageUrl)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('supabase.key'),
        ])->delete($imageUrl);

        if ($response->failed()) {
            throw new Exception("Error in deleting image");
        }
        return $response;
    }

    public static function updateImageFromSupabase(string $imageUrl, Request $request)
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
            throw new Exception($validateData->errors());
        }

        $image = $request->file('image');

        //cek imagenya ada atau tidak
        if (!$image) {
            throw new Exception("Image can't be empty");
        }

        //cek tipe
        if (!in_array($image->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg'])) {
            throw new Exception("Invalid image type. Only JPEG, JPG, and PNG are allowed");
        }

        //cek size < 2mb
        if ($image->getSize() > 2048 * 1024) {
            throw new Exception("Image size should be less than 2MB");
        }

        $imageContent = fopen($image->getRealPath(), 'r');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('supabase.key'),
            'Content-Type' => $image->getMimeType(),
            'Cache-Control' => 'public, max-age=31536000',
        ])->send('PUT', $imageUrl, [
            'body' => $imageContent
        ]);

        fclose($imageContent);

        if ($response->failed()) {
            throw new Exception("Error in uploading image");
        }
        return $response;
    }
}
