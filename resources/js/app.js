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

// Console Log Watermark & Security Warning
try {
    const asciiArt = `
  ██████╗ █████╗ ██╗██╗██╗     ██╗     ███████╗██████╗ ██╗   ██╗
  ██╔════╝██╔══██╗██║██║██║     ██║     ██╔════╝██╔══██╗╚██╗ ██╔╝
  █████╗  ███████║██║██║██║     ██║     █████╗  ██████╔╝ ╚████╔╝ 
  ██╔══╝  ██╔══██║██║██║██║     ██║     ██╔══╝  ██╔══██╗  ╚██╔╝  
  ██║     ██║  ██║██║██║███████╗███████╗███████╗██║  ██║   ██║   
  ╚═╝     ╚═╝  ╚═╝╚═╝╚═╝╚══════╝╚══════╝╚══════╝╚═╝  ╚═╝   ╚═╝   
`;
    console.log(
        `%c${asciiArt}%cff\n 🌐 https://faiillery.my.id\n`,
        "color: #8B5E3C; font-weight: bold; font-family: monospace; font-size: 11px; line-height: 1.2;",
        "color: #5C3A21; font-weight: 600; font-family: system-ui, sans-serif; font-size: 12px; line-height: 1.6;"
    );
    console.log(
        "%c PERHATIAN! %c Halaman ini diperuntukkan bagi pengembang. Jangan menempelkan script asing di sini!",
        "background: #8B5E3C; color: #FFF8ED; font-weight: 900; font-size: 12px; padding: 4px 10px; border-radius: 6px;",
        "color: #b91c1c; font-weight: 700; font-size: 12px;"
    );
} catch (e) {
    // Ignore console output errors
}

