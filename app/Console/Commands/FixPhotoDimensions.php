<?php

namespace App\Console\Commands;

use App\Models\Photo;
use Illuminate\Console\Command;

class FixPhotoDimensions extends Command
{
    protected $signature = 'photos:fix-dimensions {--limit= : Limit the number of photos to process}';
    protected $description = 'Fetch actual dimensions for recovered photos from their URLs';

    public function handle()
    {
        $query = Photo::where('width', 1200)->where('height', 1600);
        
        if ($this->option('limit')) {
            $query->limit($this->option('limit'));
        }

        $photos = $query->get();
        
        if ($photos->isEmpty()) {
            $this->info('No photos found with default dimensions.');
            return;
        }

        $this->info('Processing ' . $photos->count() . ' photos...');
        $bar = $this->output->createProgressBar($photos->count());
        $bar->start();

        foreach ($photos as $photo) {
            try {
                // Use the accessor to get full URLa
                $url = $photo->image_url;
                
                // getimagesize on remote URL fetches only the header, so it's relatively fast
                $size = @getimagesize($url);
                
                if ($size && $size[0] > 0 && $size[1] > 0) {
                    $photo->update([
                        'width' => $size[0],
                        'height' => $size[1]
                    ]);
                }
            } catch (\Exception $e) {
                // Skip errors
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Dimensions updated!');
    }
}
