<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Photo;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure uid column exists (in case migration order changes)
        if (!Schema::hasColumn('photos', 'uid')) {
            Schema::table('photos', function (Blueprint $table) {
                $table->string('uid', 12)->nullable()->unique()->after('id');
            });
        }

        // Backfill existing rows with unique uids
        DB::table('photos')->whereNull('uid')->orderBy('id')->chunk(100, function ($photos) {
            foreach ($photos as $row) {
                $uid = Photo::generateUniqueId();
                DB::table('photos')->where('id', $row->id)->update(['uid' => $uid]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do not remove uids on rollback to avoid data loss
        // Optionally, could set to null
        // DB::table('photos')->update(['uid' => null]);
    }
};
