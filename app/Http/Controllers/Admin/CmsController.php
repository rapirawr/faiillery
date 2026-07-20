<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CmsController extends Controller
{
    /**
     * Default CMS settings with labels and groups
     */
    private array $defaults = [
        // General
        'site_name'        => 'Failerry',
        'site_tagline'     => 'Abadikan Momenmu',
        'site_description' => 'Platform berbagi foto yang hangat dan personal.',
        'maintenance_mode' => '0',
        'maintenance_message' => 'Kami sedang melakukan pemeliharaan. Kembali lagi sebentar.',

        // Homepage
        'hero_title'       => 'Abadikan Setiap Momen',
        'hero_subtitle'    => 'Platform berbagi foto yang hangat, personal, dan penuh inspirasi.',
        'hero_cta_text'    => 'Mulai Sekarang',
        'show_hero_banner' => '1',

        // Upload Rules
        'max_upload_size_mb'    => '10',
        'allowed_file_types'    => 'jpg,jpeg,png,webp,gif,mp4,mov,webm',
        'max_photos_per_user'   => '500',
        'watermark_enabled'     => '0',
        'watermark_text'        => 'Failerry',

        // Social
        'allow_comments'        => '1',
        'allow_likes'           => '1',
        'allow_follows'         => '1',
        'allow_messages'        => '1',
        'allow_guest_view'      => '1',

        // Registration
        'registration_open'     => '1',
        'email_verification'    => '1',
        'welcome_message'       => 'Selamat datang di Failerry! Yuk bagikan momen pertamamu.',

        // Footer
        'footer_text'           => '© {year} Failerry. All rights reserved.',
        'contact_email'         => '',
    ];

    public function index()
    {
        $settings = [];
        foreach ($this->defaults as $key => $default) {
            $settings[$key] = Setting::get($key, $default);
        }

        return view('admin.cms', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'             => 'required|string|max:100',
            'site_tagline'          => 'nullable|string|max:200',
            'site_description'      => 'nullable|string|max:500',
            'hero_title'            => 'nullable|string|max:200',
            'hero_subtitle'         => 'nullable|string|max:300',
            'hero_cta_text'         => 'nullable|string|max:50',
            'maintenance_message'   => 'nullable|string|max:500',
            'max_upload_size_mb'    => 'required|integer|min:1|max:100',
            'max_photos_per_user'   => 'required|integer|min:1|max:99999',
            'allowed_file_types'    => 'required|string|max:200',
            'watermark_text'        => 'nullable|string|max:100',
            'welcome_message'       => 'nullable|string|max:500',
            'footer_text'           => 'nullable|string|max:300',
            'contact_email'         => 'nullable|email|max:200',
        ]);

        $toggles = [
            'maintenance_mode', 'show_hero_banner', 'watermark_enabled',
            'allow_comments', 'allow_likes', 'allow_follows',
            'allow_messages', 'allow_guest_view', 'registration_open', 'email_verification',
        ];

        foreach ($this->defaults as $key => $default) {
            if (in_array($key, $toggles)) {
                Setting::set($key, $request->has($key) ? '1' : '0');
            } else {
                Setting::set($key, $request->input($key, $default));
            }
        }

        Cache::forget('site_settings');

        return back()->with('success', 'Pengaturan CMS berhasil disimpan.');
    }

    public function reset(Request $request)
    {
        $key = $request->input('key');

        if ($key && isset($this->defaults[$key])) {
            Setting::set($key, $this->defaults[$key]);
            Cache::forget('site_settings');
            return back()->with('success', "Setting '{$key}' berhasil direset ke default.");
        }

        // Reset all
        foreach ($this->defaults as $k => $v) {
            Setting::set($k, $v);
        }
        Cache::forget('site_settings');

        return back()->with('success', 'Semua pengaturan CMS direset ke default.');
    }
}
