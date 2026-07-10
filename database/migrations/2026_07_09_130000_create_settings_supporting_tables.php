<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Alter sessions table to add custom fields for device management
        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('sessions', 'device_name')) {
                    $table->string('device_name')->nullable()->after('user_agent');
                }
                if (!Schema::hasColumn('sessions', 'location')) {
                    $table->string('location')->nullable()->after('device_name');
                }
            });
        }

        // 2. Create notification_preferences table
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel'); // mail, database, broadcast
            $table->string('event_type'); // like, comment, follow, storage_full
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'channel', 'event_type']);
        });

        // 3. Create storage_usages table
        Schema::create('storage_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('used_bytes')->default(0);
            $table->unsignedBigInteger('quota_bytes')->default(26843545600); // 25 GB default
            $table->timestamps();
        });

        // 4. Create export_jobs table
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 5. Create oauth_connections table
        Schema::create('oauth_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider'); // google, dropbox, etc
            $table->text('access_token'); // Cast encrypted
            $table->text('refresh_token')->nullable(); // Cast encrypted
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
        });

        // 6. Create user_blocks table
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blocker_id', 'blocked_id']);
        });

        // 7. Create photo_shares table
        Schema::create('photo_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('password_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_shares');
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('oauth_connections');
        Schema::dropIfExists('export_jobs');
        Schema::dropIfExists('storage_usages');
        Schema::dropIfExists('notification_preferences');

        if (Schema::hasTable('sessions')) {
            Schema::table('sessions', function (Blueprint $table) {
                if (Schema::hasColumn('sessions', 'device_name')) {
                    $table->dropColumn('device_name');
                }
                if (Schema::hasColumn('sessions', 'location')) {
                    $table->dropColumn('location');
                }
            });
        }
    }
};
