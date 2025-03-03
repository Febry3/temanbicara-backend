<?php

namespace App\Http\Controllers\Article;

use Carbon\Carbon;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;


class ArticleController extends Controller
{
    private static function validateArticleRequest(Request $request)
    {
        $requestedData = $request->only([
            'title',
            'content',
            'image',
            'user_id',
        ]);

        $validateData = Validator::make($requestedData, [
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id' => 'required',
        ]);

        if ($validateData->fails()) {
            throw new ValidationException($validateData);
        }

        return $validateData->validated();
    }
    public static function getAllArticle()
    {
        try {
            $articles = Article::with('user:id,name,role')->where('status','Published')->get();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Artikel berhasil diambil',
                    'data' => $articles,
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
    public static function createArticle(Request $request)
    {
        try {
            $imageUrl = null;
            $validatedData = self::validateArticleRequest($request);
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('article', 'public');

                $imageUrl = asset('storage/' . $imagePath);
            }

            $artikels = Article::create([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'image' => $imageUrl,
                'user_id' => $validatedData['user_id'],
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
    public static function getAllArticleByCounselor(Request $request)
    {

        try {
            $article =Article::where('user_id', $request->user()->id)->get();

            if (!$article) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'article tidak ditemukan',
                    ],
                    200
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => $article
                ],
                200
            );
        } catch (\Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                500
            );
        }
    }



    public static function getArticleById($id)
    {
        try {
            $artikel = Article::with('user:id,name')->findOrFail($id);
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
