<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Journal;
use App\Models\Tracking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helper\ImageRequestHelper;
use App\Http\Requests\JournalRequest;
use Error;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JournalController extends Controller
{
    private const NOT_FOUND_MSG = 'Jurnal tidak ditemukan';
    public static function createJournal(JournalRequest $request)
    {
        try {
            $imageUrl = "Empty";
            $requestedData = $request->only([
                'title',
                'body',
            ]);

            Validator::make(
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
                'image_url' => $imageUrl ?? "Empty",
                'tracking_id' => $trackingId,
                'user_id' => Auth::user()->id,
            ]);
            $responseAi = app(ReportController::class)->doReport($request->user()->id);
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $journal,
                    'response_ai' => $responseAi
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
            $responseData = [];
            $journal = Journal::where([
                'journal_id' => $id,
                'user_id' => Auth::user()->id
            ])->first();

            if (!$journal) {
                $responseData = [
                    'status' => false,
                    'message' => self::NOT_FOUND_MSG,
                ];
                return response()->json($responseData, 500);
            }

            if ($request->hasFile('image')) {
                if (str_contains($journal->image_url, 'default')) {
                    $response = ImageRequestHelper::postImageToSupabase($request, 'journal');
                    $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];
                } else {
                    $response = ImageRequestHelper::updateImageFromSupabase($journal->image_url, $request);
                }

                if ($response->failed()) {
                    $responseData =
                        [
                            'status' => false,
                            'message' => 'Kesalahan pada memperbaharui gambar',
                        ];
                    return response()->json($responseData, 500);
                }
            }

            $journal->update([
                'title' => $request['title'],
                'body' => $request['body'],
                'image_url' => $imageUrl ?? $journal->image_url,
            ]);

            $responseData = [
                'status' => true,
                'message' => 'Data berhasil diubah',
            ];
        } catch (Throwable $err) {
            $responseData = [
                'status' => false,
                'message' => $err->getMessage()
            ];
        }
        return response()->json($responseData);
    }

    public static function deleteJournal($id)
    {
        $responseData = [];
        $statusCode = 200;
        try {
            $journal = Journal::where([
                'journal_id' => $id,
                'user_id' => Auth::user()->id
            ])->first();

            if (!$journal) {
                $responseData =
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan atau Anda tidak memiliki akses.',
                    ];
                $statusCode = 204;
                return response()->json($responseData, $statusCode);
            }

            if ($journal->image_url != 'Empty') {
                $response = ImageRequestHelper::deleteImageFromSupabase($journal->image_url);

                if ($response->failed()) {
                    $responseData =
                        [
                            'status' => false,
                            'message' => 'Kesalahan pada menghapus gambar',
                        ];
                    $statusCode = 500;
                    return response()->json($responseData, $statusCode);
                }
            }

            $journal->delete();

            $responseData =
                [
                    'status' => true,
                    'message' => 'Data berhasil dihapus',
                ];
        } catch (\Throwable $err) {
            $responseData =
                [
                    'status' => false,
                    'message' => 'Terjadi kesalahan: ' . $err->getMessage(),
                ];
        }
        return response()->json($responseData, $statusCode);
    }

    public static function getJournalById($id)
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
                        'message' => self::NOT_FOUND_MSG,
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
            $requestedData = $request->only([
                'date_request'
            ]);

            Validator::make(
                $requestedData,
                [
                    'date_request' => ['required', 'date_format:YYYY-mm-dd'],
                ]
            );

            $datereq = $request['date_request'];
            $journal = Journal::with('user')->where('user_id', $userId)
                ->whereDate('created_at', $datereq)->get();

            if ($journal->isEmpty()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => self::NOT_FOUND_MSG,
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
                500
            );
        }
    }
}
