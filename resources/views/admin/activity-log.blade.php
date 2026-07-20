@extends('layouts.admin')

@section('page-title', 'Activity Log')
@section('page-subtitle', 'Audit trail semua aksi admin')

@section('content')

<!-- Filters -->
<div class="flex flex-wrap gap-3 mb-5">
    <form method="GET" action="{{ route('admin.activity-log') }}" class="flex flex-wrap gap-3 items-center w-full">

        <!-- Filter by action type -->
        <select name="action" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-xl text-sm outline-none border cursor-pointer"
                style="background:#FEFAF4;border-color:#E3C79A;color:#3B2417;">
            <option value="">Semua Aksi</option>
            @foreach([
                'delete_photo'     => '🗑 Delete Photo',
                'bulk_delete_photos' => '🗑 Bulk Delete Photos',
                'delete_user'      => '🗑 Delete User',
                'grant_admin'      => '🛡 Grant Admin',
                'revoke_admin'     => '🛡 Revoke Admin',
                'verify_user'      => '✅ Verify User',
                'unverify_user'    => '❌ Unverify User',
                'shadowban_user'   => '🚫 Shadowban',
                'unshadowban_user' => '✅ Unshadowban',
                'impersonate_user' => '👤 Impersonate',
                'reset_password'   => '🔑 Reset Password',
                'send_announcement' => '📢 Announcement',
            ] as $val => $label)
                <option value="{{ $val }}" {{ request('action') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>

        <!-- Filter by admin -->
        <select name="admin_id" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-xl text-sm outline-none border cursor-pointer"
                style="background:#FEFAF4;border-color:#E3C79A;color:#3B2417;">
            <option value="">Semua Admin</option>
            @foreach($admins as $admin)
                <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                    {{ $admin->name }}
                </option>
            @endforeach
        </select>

        @if(request('action') || request('admin_id'))
        <a href="{{ route('admin.activity-log') }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition-all"
           style="background:#F5E6CE;color:#8B5E3C;"
           onmouseover="this.style.background='#E3C79A'" onmouseout="this.style.background='#F5E6CE'">
            Clear Filter
        </a>
        @endif

        <div class="ml-auto text-sm" style="color:#C69C6D;">
            {{ $logs->total() }} entri
        </div>
    </form>
</div>

<!-- Log Table -->
<div class="admin-card rounded-2xl overflow-hidden">
    <table class="w-full admin-table">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Admin</th>
                <th>Aksi</th>
                <th>Subject</th>
                <th>Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <!-- Waktu -->
                <td class="whitespace-nowrap">
                    <div class="text-xs font-semibold" style="color:#3B2417;">{{ $log->created_at->format('d M Y') }}</div>
                    <div class="text-xs" style="color:#C69C6D;">{{ $log->created_at->format('H:i:s') }}</div>
                </td>

                <!-- Admin -->
                <td>
                    @if($log->admin)
                    <div class="flex items-center gap-2">
                        <img src="{{ $log->admin->avatar_url }}" class="w-7 h-7 rounded-full object-cover flex-shrink-0" style="border:1px solid #E3C79A;">
                        <div>
                            <div class="text-xs font-semibold" style="color:#3B2417;">{{ $log->admin->name }}</div>
                            <div class="text-xs" style="color:#C69C6D;">@{{ $log->admin->username }}</div>
                        </div>
                    </div>
                    @else
                        <span class="text-xs" style="color:#E3C79A;">—</span>
                    @endif
                </td>

                <!-- Aksi Badge -->
                <td>
                    @php
                        $actionColors = [
                            'delete_photo'       => '#fef2f2::#e11d48',
                            'bulk_delete_photos' => '#fef2f2::#e11d48',
                            'delete_user'        => '#fef2f2::#e11d48',
                            'grant_admin'        => '#eff6ff::#2563eb',
                            'revoke_admin'       => '#fff7ed::#ea580c',
                            'verify_user'        => '#f0fdf4::#16a34a',
                            'unverify_user'      => '#fafafa::#6b7280',
                            'shadowban_user'     => '#fef9c3::#ca8a04',
                            'unshadowban_user'   => '#f0fdf4::#16a34a',
                            'impersonate_user'   => '#f5f3ff::#7c3aed',
                            'reset_password'     => '#fff7ed::#ea580c',
                            'send_announcement'  => '#eff6ff::#3b82f6',
                        ];
                        [$bg, $color] = explode('::', $actionColors[$log->action] ?? '#F5E6CE::#8B5E3C');
                    @endphp
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap"
                          style="background:{{ $bg }};color:{{ $color }};">
                        {{ str_replace('_', ' ', $log->action) }}
                    </span>
                </td>

                <!-- Subject -->
                <td>
                    @if($log->subject_type && $log->subject_id)
                        <span class="text-xs font-mono px-2 py-0.5 rounded" style="background:#F5E6CE;color:#8B5E3C;">
                            {{ $log->subject_type }} #{{ $log->subject_id }}
                        </span>
                    @else
                        <span class="text-xs" style="color:#E3C79A;">—</span>
                    @endif
                </td>

                <!-- Deskripsi -->
                <td>
                    <p class="text-xs max-w-xs truncate" style="color:#3B2417;" title="{{ $log->description }}">
                        {{ $log->description ?? '—' }}
                    </p>
                    @if($log->meta)
                    <details class="mt-1">
                        <summary class="text-xs cursor-pointer" style="color:#C69C6D;">Meta</summary>
                        <pre class="text-xs mt-1 p-2 rounded overflow-x-auto" style="background:#F5E6CE;color:#3B2417;max-width:240px;">{{ json_encode($log->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-16" style="color:#C69C6D;">
                    <div class="text-4xl mb-3">📋</div>
                    <div class="font-semibold text-sm">Belum ada aktivitas</div>
                    <div class="text-xs mt-1">Log akan muncul setelah ada aksi admin.</div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $logs->links() }}
</div>

@endsection
