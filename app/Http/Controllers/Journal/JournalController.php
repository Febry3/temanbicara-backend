<?php

namespace App\Http\Controllers\Journal;

use Throwable;
use App\Models\Journal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class JournalController extends Controller
{
    private static function validateJournalRequest(Request $request)
    {
        $requestedData = $request->only([
            'title',
            'body',
            'stress_level',
            'mood_level',
            'user_id',
        ]);

        $validateData = Validator::make(
            $requestedData,
            [
                'title' => 'required|string',
                'body' => 'required|string',
                'stress_level' => 'required|integer',
                'mood_level' => 'required|string',
                'user_id' => 'required',
            ]
        );

        if ($validateData->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Ada bagian yang tidak diisi',
                'error' => $validateData->errors(),
            ], 200);
        }
        ;
        return $requestedData;
    }

    public static function createJournal(Request $request)
    {
        try {
            $validatedData = self::validateJournalRequest($request);

            $journal = Journal::create([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'stress_level' => $validatedData['stress_level'],
                'mood_level' => $validatedData['mood_level'],
                'user_id' => $validatedData['user_id'],
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

            // $journal = Journal::find($id)->where('user_id', $request->user()->id);
            $journal = Journal::where('journal_id', $id)
                ->where('user_id', $request->user()->id)
                ->first();

            if (!$journal) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan',
                    ],
                    200
                );
            }

            $journal->update([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'stress_level' => $validatedData['stress_level'],
                'mood_level' => $validatedData['mood_level'],
                'user_id' => $validatedData['user_id'],

            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diubah',
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
    public static function deleteJournal(Request $request, $id)
    {
        try {

            $journal = Journal::where('journal_id', $id)
                ->where('user_id', $request->user()->id)
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

    public static function getJournal(Request $request, $id)
    {
        try {
            $journal = Journal::find($id)->where('user_id', $request->user()->id)->get();

            if (!$journal) {
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
            // $data = $request->json()->all();
            // $userId = 3;

            $journal = Journal::with('user')->where('user_id', $userId)->get();


            if (!$journal) {
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
