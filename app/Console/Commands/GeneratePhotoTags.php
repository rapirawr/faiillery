<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeneratePhotoTags extends Command
{
    protected $signature = 'photos:generate-tags {--limit= : Limit jumlah foto} {--force : Update foto yang sudah punya tag}';
    protected $description = 'Generate tags otomatis untuk foto menggunakan AI (Gemini)';

    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent';
    // protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function handle()
    {
        set_time_limit(0);
        $this->apiKey = config('services.gemini.key');

        if (!$this->apiKey) {
            $this->error('Gemini API Key belum diset di .env!');
            return Command::FAILURE;
        }

        $query = Photo::query();

        // Hanya cari foto yang belum punya tag
        if (!$this->option('force')) {
            $query->doesntHave('tags');
        }

        if ($this->option('limit')) {
            $query->limit((int) $this->option('limit'));
        }

        $photos = $query->cursor();
        $this->info('Mulai generate tags untuk foto...');

        $index = 0;
        foreach ($photos as $photo) {
            $index++;
            $this->warn("[{$index}] Menganalisis Tags untuk ID {$photo->id}...");

            try {
                $this->processTags($photo);
                sleep(2); // Jeda biar ga kena rate limit
            } catch (\Exception $e) {
                $this->error('   ❌ Error: ' . $e->getMessage());
            }
        }

        $this->info('Selesai generate tags!');
        return Command::SUCCESS;
    }

    protected function processTags(Photo $photo)
    {
        $imageUrl = $photo->thumbnail_url ?: $photo->image_url;

        // 1. Download & Resize
        $response = Http::timeout(30)->withoutVerifying()->get($imageUrl);
        if (!$response->successful()) return;

        $imageContent = $response->body();
        if (strlen($imageContent) > 500 * 1024) {
            $imageContent = $this->resizeForAI($imageContent);
        }

        $base64Image = base64_encode($imageContent);

        // 2. Tanya Gemini
        $res = Http::timeout(60)->withoutVerifying()->post($this->apiUrl . '?key=' . $this->apiKey, [
            'contents' => [[
                'parts' => [
                    ['text' => 'Berikan 5 sampai 8 tag/kata kunci yang relevan untuk gambar ini dalam format JSON array of strings. Gunakan Bahasa Indonesia yang populer dan relevan dengan konten gambar.'],
                    ['inline_data' => ['mime_type' => 'image/jpeg', 'data' => $base64Image]]
                ]
            ]],
            'generationConfig' => ['response_mime_type' => 'application/json']
        ]);

        if ($res->successful()) {
            $tags = json_decode($res->json('candidates.0.content.parts.0.text'), true);

            if (is_array($tags)) {
                $tagIds = [];
                foreach ($tags as $tagName) {
                    $tag = Tag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
                $photo->tags()->syncWithoutDetaching($tagIds);
                $this->line('   <info># Tags:</info> ' . implode(', ', $tags));
            }
        }
    }

    protected function resizeForAI($content)
    {
        $src = imagecreatefromstring($content);
        if (!$src) return $content;
        $w = imagesx($src); $h = imagesy($src);
        $ratio = 500 / max($w, $h);
        $newW = $w * $ratio; $newH = $h * $ratio;
        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        ob_start(); imagejpeg($dst, null, 70);
        $out = ob_get_clean();
        imagedestroy($src); imagedestroy($dst);
        return $out;
    }
}
