import './bootstrap';

import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';
import Masonry from 'masonry-layout';
import imagesLoaded from 'imagesloaded';
import { supabase } from './supabase';

window.Alpine = Alpine;
window.Masonry = Masonry;
window.imagesLoaded = imagesLoaded;
window.supabase = supabase;

// Realtime Notifications Handler
const user_id = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

if (user_id) {
    supabase
        .channel('public:notifications')
        .on('postgres_changes', { 
            event: 'INSERT', 
            schema: 'public', 
            table: 'notifications',
            filter: `user_id=eq.${user_id}`
        }, payload => {
            const data = typeof payload.new.data === 'string' ? JSON.parse(payload.new.data) : payload.new.data;
            if (window.showToast) {
                window.showToast(data.message || 'Anda mendapat notifikasi baru!');
            }
        })
        .subscribe();
}

Alpine.plugin(intersect);
Alpine.start();
