<?php

namespace App\Http\Controllers\Journal;

use Throwable;
use App\Models\Journal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\ImageRequestHelper;
use App\Http\Utils\ImageRequestHelper as UtilsImageRequestHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class JournalController extends Controller
{
    private static function validateJournalRequest(Request $request)
    {
        $requestedData = $request->only([
            'title',
            'body',
            'image',
            'stress_level',
            'mood_level',
        ]);

        $validateData = Validator::make($requestedData, [
            'title' => 'required|string',
            'body' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'stress_level' => 'required|integer',
            'mood_level' => 'required|string',
        ]);

        if ($validateData->fails()) {
            throw new ValidationException($validateData);
        }

        return $validateData->validated();
    }


    public static function createJournal(Request $request)
    {
        try {
            $validatedData = self::validateJournalRequest($request);

            if ($request->hasFile('image')) {
                $response = ImageRequestHelper::postImageToSupabase($request);
                $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];

                if ($response->failed()) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Kesalahan pada mengupload gambar',
                        ],
                        404
                    );
                }
            }

            $journal = Journal::create([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'image_url' => $imageUrl ?? config('supabase.url') . '/profile/' . 'default.png',
                'stress_level' => $validatedData['stress_level'],
                'mood_level' => $validatedData['mood_level'],
                'user_id' => Auth::user()->id,
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $journal
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
    public static function updateJournal(Request $request, $id)
    {
        try {
            $validatedData = self::validateJournalRequest($request);

            $journal = Journal::where([
                'journal_id' => $id,
                'user_id' => Auth::user()->id
            ])->first();

            if (!$journal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jurnal tidak ditemukan',
                ], 200);
            }

            if ($request->hasFile('image')) {
                if (str_contains($journal->image_url, 'default')) {
                    $response = ImageRequestHelper::postImageToSupabase($request);
                    $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];
                } else {
                    $response = ImageRequestHelper::updateImageFromSupabase($journal->image_url, $request);
                }

                if ($response->failed()) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Kesalahan pada memperbaharui gambar',
                        ],
                        404
                    );
                }
            }

            $journal = $journal->update([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'image_url' => $imageUrl ?? $journal->image_url,
                'stress_level' => $validatedData['stress_level'],
                'mood_level' => $validatedData['mood_level'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diubah',
                'data' => $journal
            ], 200);
        } catch (Throwable $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage()
            ], 500);
        }
    }

    public static function deleteJournal($request, $id)
    {
        try {
            $journal = Journal::where([
                'journal_id' => $id,
                'user_id' => Auth::user()->id
            ])->first();

            if (!$journal) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses.',
                    ],
                    404
                );
            }

            $response = ImageRequestHelper::deleteImageFromSupabase($journal->image_url);

            if ($response->failed()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Kesalahan pada menghapus gambar',
                    ],
                    404
                );
            }

            $journal->delete();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil dihapus.',
                ],
                200
            );
        } catch (\Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $err->getMessage(),
                ],
                500
            );
        }
    }

    public static function getJournalById(Request $request, $id)
    {
        try {
            $journal = Journal::where([
                'journal_id' => $id,
                'user_id' => Auth::user()->id
            ])->first();

            if (!$journal) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan',
                    ],
                    404
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => $journal
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
    public static function getAllJournalByUserId(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $journal = Journal::with('user')->where('user_id', $userId)->get();

            if ($journal->isEmpty()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan',
                    ],
                    200
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'id' => $userId,
                    'data' => $journal
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

    public static function testDelete()
    {
        try {
            $response = ImageRequestHelper::deleteImageFromSupabase("https://qzsrrlobwlisodbasdqi.supabase.co/storage/v1/object/profile/profile67e2a28932b07-1742905993.png");
            dd($response->body());
            return response()->json(
                $response,
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                $err->getMessage(),
                400
            );
        }
    }
}
