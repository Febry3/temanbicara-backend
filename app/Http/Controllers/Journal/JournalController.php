<?php

namespace App\Http\Controllers\Journal;

use Throwable;
use App\Models\Journal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Utils\ImageRequestHelper;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

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

            $response = ImageRequestHelper::postImageToSupabase($request);

            $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];

            $journal = Journal::create([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'image' => $imageUrl,
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

            $journal = Journal::where('journal_id', $id)
                ->where('user_id', Auth::user()->id)
                ->first();

            if (!$journal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Jurnal tidak ditemukan',
                ], 200);
            }

            $response = ImageRequestHelper::postImageToSupabase($request);

            $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];

            $journal->update([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'image' => $imageUrl,
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

    public static function deleteJournal(Request $request, $id)
    {
        try {

            $journal = Journal::where('journal_id', $id)
                ->where('user_id', 1)
                ->first();


            if (!$journal) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses.',
                    ],
                    404
                );
            }

            if ($journal) {
                $imagePath = str_replace(asset('storage/'), '', $journal->image);
                Storage::disk('public')->delete($imagePath);
                $journal->delete();
            }


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

    public static function getJournal(Request $request, $id)
    {
        try {
            $journal = Journal::where('journal_id', $id)->where('user_id', $request->user()->id)->first();
            if (is_null($journal)) {
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
    public static function getAllJournal(Request $request)
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
}
