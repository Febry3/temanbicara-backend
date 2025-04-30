<?php

namespace App\Http\Controllers\Journal;

use Throwable;
use App\Models\Journal;
use App\Models\Tracking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\ImageRequestHelper;
use App\Http\Requests\JournalRequest;
use Error;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JournalController extends Controller
{
    public static function createJournal(JournalRequest $request)
    {
        try {
            $requestedData = $request->only([
                'title',
                'body',
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'title' => 'required',
                    'body' => 'required',

                ]
            );
            if ($request->hasFile('image')) {
                $response = ImageRequestHelper::postImageToSupabase($request, 'journal');
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
            //cek apakah id_tracking masih kosong.
            //jika kosong dan tracking tersebut sudah tersedia maka akan di assign ke id_tracking yg tersedia di hari ini.
            $today = now()->toDateString();
            $trackingId = $request['tracking_id'];
            if (empty($trackingId)) {

                $tracking = Tracking::where('user_id', Auth::user()->id)
                    ->whereDate('created_at', $today)
                    ->first();
                if ($tracking) {
                    $trackingId = $tracking['tracking_id'];
                }
            }

            $journal = Journal::create([
                'title' => $request['title'],
                'body' => $request['body'],
                'image_url' => $imageUrl ?? config('supabase.url') . '/profile/' . 'default.png',
                'tracking_id' => $trackingId,
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
        } catch (Error $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                500
            );
        }
    }
    public static function updateJournal(JournalRequest $request, $id)
    {
        try {
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
                    $response = ImageRequestHelper::postImageToSupabase($request, 'journal');
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

            $journal->update([
                'title' => $request['title'],
                'body' => $request['body'],
                'image_url' => $imageUrl ?? $journal->image_url,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Data berhasil diubah',
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
                    'message' => 'Data berhasil dihapus',
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
