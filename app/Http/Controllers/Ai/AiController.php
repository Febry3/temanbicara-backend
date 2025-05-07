<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Tracking;
use App\Models\TrackJournalResponse;
use Carbon\Carbon;
// use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Support\Facades\Http;



class AiController extends Controller
{
    public function prompt($mood, $sleep, $stress, $screenTime, $steps, $journalText)
    {
        return <<<EOT
        Kamu adalah seorang konsuler dan asisten pendamping kesehatan mental. Aku akan memberikan beberapa data mengenai kesehatan mental harian. Aku ingin kamu merespon sebagai seorang konsuler dan memberikan penilaian dalam bentuk JSON.

        Berikut datanya:

        Mood (Depresi, Sedih, Netral, Senang, Bahagia) : {$mood}
        Kualitas Tidur (<3 jam, 4-5 jam, 5-6 jam, 7-8 jam, >8 jam) : {$sleep}
        Stress Level (Range 1 - 5) : {$stress}
        Screen Time (<1 jam, 1-2 jam, 3-4 jam, 5-6 jam, >6 jam) : {$screenTime}
        Steps (<500, 500-1000, 1000-3000, 3000-5000, >5000) : {$steps}
        Journal : "{$journalText}"

        Terakhir, berikan juga nilai matrix terhadap keseluruhan hasil dengan indikator sebagai berikut:

        1 - 20 : Struggling
        21 - 40 : Challenging
        41 - 60 : Balanced
        61 - 80 : Energized
        81 - 100 : Flourishing
        untuk matrix berikan hasil dalam bentuk integer ya
        Berikan jawaban dalam format berikut:

        {
            "assessment": "",
            "observations": {
                "mood": "",
                "sleep": "",
                "stress": "",
                "screen_time": "",
                "activity": ""
            },
            "recommendations": {
                "short_term": [],
                "long_term": []
            },
            "closing": "",
            "matrix": ""
        }

        Berikan hanya JSON tanpa penjelasan tambahan.
        EOT;
    }
    public function generate($userId)
    {
        $tracking = Tracking::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->first();

        if (!$tracking) {
            return response()->json([
                'error' => 'Silahkan mengisi tracking terlebih dahulu'
            ], 404);
        }

        $journal = Journal::with('user')
            ->where('tracking_id', $tracking->tracking_id)
            ->first();

        $mood = $tracking->mood_level;
        $sleep = $tracking->bed_time;
        $stress = $tracking->stress_level;
        $screenTime = $tracking->screen_time;
        $steps = $tracking->activity;
        $journalText = $journal->body ?? null;

        $prompt = $this->prompt($mood, $sleep, $stress, $screenTime, $steps, $journalText);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post(config('services.gemini.url') . '?key=' . config('services.gemini.key'), [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ]
                    ]);
            $reply = $response->json();
            $text = $reply['candidates'][0]['content']['parts'][0]['text'] ?? null;
            $cleanedText = trim($text);
            $cleanedText = preg_replace('/^```json\s*/', '', $cleanedText);
            $cleanedText = preg_replace('/\s*```$/', '', $cleanedText);
            $jsonResult = json_decode($cleanedText, true);

            if (!$jsonResult) {
                return response()->json([
                    'error' => 'Format JSON tidak valid dari Gemini',
                    'raw_response' => $jsonResult
                ]);
            }

            return response()->json([
                'status' => true,
                'tracking_id' => $tracking->tracking_id,
                'result' => $jsonResult
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to store data',
                'message' => $e->getMessage()
            ]);
        }
    }

}
