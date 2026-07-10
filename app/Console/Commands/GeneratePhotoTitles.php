<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeneratePhotoTitles extends Command
{
    protected $signature = 'photos:generate-titles {--limit= : Limit the number of photos to process} {--force : Reprocess photos that already have titles}';

    protected $description = 'Use AI (Gemini) to read photo content and generate appropriate titles and descriptions';

    protected $apiKey;
    // protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent';
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function handle()
    {
        set_time_limit(0);
        $this->apiKey = config('services.gemini.key');

        if (!$this->apiKey) {
            $this->error('Gemini API Key is not set. Please add GEMINI_API_KEY to your .env file.');
            return Command::FAILURE;
        }

        $query = Photo::query();

        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('title')
                    ->orWhere('title', '')
                    ->orWhere('title', 'like', 'Photo %')
                    ->orWhere('title', 'like', 'IMG_%')
                    ->orWhere('title', 'like', 'Recovery Photo%')
                    ->orWhereNull('description')
                    ->orWhere('description', '');
            });
        }

        if ($this->option('limit')) {
            $query->limit((int) $this->option('limit'));
        }

        $photos = $query->cursor();

        $this->info('Memproses foto satu per satu...');

        $index = 0;
        foreach ($photos as $photo) {
            $index++;
            $this->warn("[{$index}] Memproses ID {$photo->id}...");

            try {
                $this->processPhoto($photo);
                sleep(4);
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
                Log::error("Failed to process photo ID {$photo->id}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('Selesai!');

        return Command::SUCCESS;
    }

    protected function processPhoto(Photo $photo)
    {
        $imageUrl = $photo->thumbnail_url ?: $photo->image_url;

        // Step 1: Download gambar
        try {
            $response = Http::timeout(60)->withoutVerifying()->get($imageUrl);

            if (!$response->successful()) {
                $this->error("   ❌ Gagal download gambar: HTTP {$response->status()} dari {$imageUrl}");
                return;
            }

            $imageContent = $response->body();
            $sizeInKb = round(strlen($imageContent) / 1024, 2);
            $this->line("   <comment>i Ukuran gambar original: {$sizeInKb} KB</comment>");

            // Resize kalau > 500 KB agar tidak reset koneksi
            if (strlen($imageContent) > 500 * 1024) {
                $imageContent = $this->resizeImage($imageContent);
                $resizedKb = round(strlen($imageContent) / 1024, 2);
                $this->line("   <comment>i Setelah resize: {$resizedKb} KB</comment>");
            }

            $mimeType = 'image/jpeg';
            $base64Image = base64_encode($imageContent);

        } catch (\Exception $e) {
            $this->error('   ❌ Download Error: ' . $e->getMessage());
            return;
        }

        // Step 2: Kirim ke Gemini
        try {
            $response = retry(3, function () use ($base64Image, $mimeType) {
                return Http::timeout(120)
                    ->withoutVerifying()
                    ->post($this->apiUrl . '?key=' . $this->apiKey, [
                        'contents' => [[
                            'parts' => [
                                ['text' => 'Analisis gambar ini dan berikan respons HANYA dalam format JSON murni (tanpa markdown) dengan key: "title" (string, max 60 karakter) dan "description" (string, max 160 karakter). 
                                ATURAN PENTING: Gunakan gaya bahasa Indonesia yang "Indo Banget", santai, kekinian (gaul), dan asik kayak di media sosial (Instagram/TikTok). 
                                Jangan kaku! Boleh pakai istilah kayak "Vibes", "Kece parah", "Mabar", "Momen gokil", dll. Bikin judulnya menarik dan deskripsinya bikin orang betah baca.'],
                                ['inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Image,
                                ]],
                            ],
                        ]],
                        'generationConfig' => [
                            'response_mime_type' => 'application/json',
                            'temperature' => 0.8, // Menaikkan temperature agar lebih kreatif
                        ],
                    ]);
            }, 3000);

            if ($response->successful()) {
                $rawText = $response->json('candidates.0.content.parts.0.text');
                $cleaned = trim(preg_replace('/^```json|^```|```$/m', '', $rawText ?? ''));
                $data = json_decode($cleaned, true);

                if (isset($data['title'])) {
                    $photo->update([
                        'title' => $data['title'],
                        'description' => $data['description'] ?? $photo->description,
                    ]);
                    $this->line('   <info>✔ Judul:</info> ' . $data['title']);
                } else {
                    $this->error('   ❌ AI tidak memberikan format JSON yang valid.');
                    Log::error("Gemini Invalid JSON Response ID {$photo->id}: " . $rawText);
                }
            } else {
                $errorCode = $response->status();
                $errorMsg = $response->json('error.message') ?? $response->body();
                $this->error("   ❌ Gemini Error [{$errorCode}]: " . $errorMsg);

                if ($errorCode === 429) {
                    $this->warn('   ⚠ Rate limit, extra sleep...');
                    sleep(10);
                }
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Request Error: ' . $e->getMessage());
        }
    }

    /**
     * Resize gambar ke max 800px dan compress ke JPEG ~70%
     */
    protected function resizeImage(string $imageContent): string
    {
        $src = imagecreatefromstring($imageContent);
        if (!$src) {
            return $imageContent;
        }

        $origW = imagesx($src);
        $origH = imagesy($src);
        $maxSize = 800;

        if ($origW > $origH) {
            $newW = min($origW, $maxSize);
            $newH = (int) ($origH * ($newW / $origW));
        } else {
            $newH = min($origH, $maxSize);
            $newW = (int) ($origW * ($newH / $origH));
        }

        $dst = imagecreatetruecolor($newW, $newH);

        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        imagejpeg($dst, null, 70);
        $output = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $output;
    }
}