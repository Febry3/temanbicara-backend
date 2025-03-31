<?php

namespace App\Http\Controllers\Article;

use Throwable;
use Carbon\Carbon;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\ImageRequestHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ArticleController extends Controller
{
    private static function validateArticleRequest(Request $request)
    {
        $requestedData = $request->only([
            'title',
            'content',
            'image',
        ]);

        $validateData = Validator::make($requestedData, [
            'title' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validateData->fails()) {
            throw new ValidationException($validateData);
        }

        return $validateData->validated();
    }
    public static function getAllArticle()
    {
        try {
            $articles = Article::with('user:id,name,role')->where('status', 'Published')->get();
            if ($articles->isEmpty()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Data Artikel tidak di temukan',
                    ],
                    200
                );
            }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Artikel berhasil diambil',
                    'data' => $articles,
                ],
                200
            );
        } catch (Throwable $e) {
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
            $validatedData = self::validateArticleRequest($request);

            $response = ImageRequestHelper::postImageToSupabase($request, 'article');
            $artikels = Article::create([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'image_url' => config('supabase.url') . '/' . $response->json()['Key'],
                'user_id' => Auth::user()->id,
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Artikel berhasil dibuat',
                    'data' => $artikels,
                ],
                201
            );
        } catch (Throwable $e) {
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
            $article = Article::where('user_id', Auth::user()->id)->get();

            if ($article->isEmpty()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Data Artikel tidak di temukan',
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
        } catch (Throwable $err) {
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data Artikel tidak ditemukan',
            ], 404);
        } catch (Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage(),
            ], 500);
        }
    }
}
