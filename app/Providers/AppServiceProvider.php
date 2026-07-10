<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // Share CMS settings globally to all views
        View::composer('*', function ($view) {
            try {
                $view->with('cms', [
                    'site_name'          => Setting::get('site_name', 'Failerry'),
                    'site_tagline'       => Setting::get('site_tagline', 'Abadikan Momenmu'),
                    'site_description'   => Setting::get('site_description', ''),
                    'footer_text'        => Setting::get('footer_text', '© {year} Failerry. All rights reserved.'),
                    'contact_email'      => Setting::get('contact_email', ''),
                    'hero_title'         => Setting::get('hero_title', 'Abadikan Setiap Momen'),
                    'hero_subtitle'      => Setting::get('hero_subtitle', 'Platform berbagi foto yang hangat.'),
                    'hero_cta_text'      => Setting::get('hero_cta_text', 'Mulai Sekarang'),
                    'show_hero_banner'   => Setting::enabled('show_hero_banner', true),
                    'allow_comments'     => Setting::enabled('allow_comments', true),
                    'allow_likes'        => Setting::enabled('allow_likes', true),
                    'allow_follows'      => Setting::enabled('allow_follows', true),
                    'allow_messages'     => Setting::enabled('allow_messages', true),
                    'allow_guest_view'   => Setting::enabled('allow_guest_view', true),
                    'registration_open'  => Setting::enabled('registration_open', true),
                    'maintenance_mode'   => Setting::enabled('maintenance_mode', false),
                ]);
            } catch (\Throwable $e) {
                // DB might not be ready (e.g. during migrations), fail silently
                $view->with('cms', [
                    'site_name' => 'Failerry', 'site_tagline' => 'Abadikan Momenmu',
                    'site_description' => '', 'footer_text' => '© {year} Failerry. All rights reserved.',
                    'contact_email' => '', 'hero_title' => 'Abadikan Setiap Momen',
                    'hero_subtitle' => '', 'hero_cta_text' => 'Mulai Sekarang',
                    'show_hero_banner' => true, 'allow_comments' => true, 'allow_likes' => true,
                    'allow_follows' => true, 'allow_messages' => true, 'allow_guest_view' => true,
                    'registration_open' => true, 'maintenance_mode' => false,
                ]);
            }
        });
    }
}
