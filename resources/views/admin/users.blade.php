@extends('layouts.admin')

@section('page-title', 'Users')
@section('page-subtitle', 'Kelola akun dan permissions')

@section('content')

<!-- Fitur 2: Search & Filter Bar -->
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <form method="GET" action="{{ route('admin.users') }}" class="flex flex-1 gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#C69C6D;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, username, atau email…"
                   class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm outline-none border transition-all"
                   style="background:#FEFAF4;border-color:#E3C79A;color:#3B2417;"
                   onfocus="this.style.borderColor='#8B5E3C'" onblur="this.style.borderColor='#E3C79A'">
        </div>
        <select name="filter" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-xl text-sm outline-none border cursor-pointer"
                style="background:#FEFAF4;border-color:#E3C79A;color:#3B2417;">
            <option value="all"         {{ ($filter ?? 'all') === 'all'          ? 'selected' : '' }}>All Users</option>
            <option value="admin"       {{ ($filter ?? '') === 'admin'           ? 'selected' : '' }}>Admins Only</option>
            <option value="verified"    {{ ($filter ?? '') === 'verified'        ? 'selected' : '' }}>Verified Only</option>
            <option value="shadowbanned" {{ ($filter ?? '') === 'shadowbanned'   ? 'selected' : '' }}>Shadowbanned</option>
        </select>
        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all"
                style="background:#8B5E3C;" onmouseover="this.style.background='#5C3A21'" onmouseout="this.style.background='#8B5E3C'">
            Search
        </button>
        @if($search || ($filter ?? 'all') !== 'all')
        <a href="{{ route('admin.users') }}" class="px-4 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center"
           style="background:#F5E6CE;color:#8B5E3C;" onmouseover="this.style.background='#E3C79A'" onmouseout="this.style.background='#F5E6CE'">
            Clear
        </a>
        @endif
    </form>
</div>

<div class="admin-card rounded-2xl overflow-hidden">
    <table class="w-full admin-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Status</th>
                <th>Joined</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" style="border:1px solid #E3C79A;">
                        <div>
                            <div class="font-semibold text-sm flex items-center gap-1.5" style="color:#3B2417;">
                                {{ $user->name }}
                                @if($user->is_verified)
                                    <x-verified-badge size="w-3.5 h-3.5" checkSize="w-2 h-2" />
                                @endif
                            </div>
                            <div class="text-xs" style="color:#C69C6D;">{{ $user->username }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="text-sm" style="color:#8B5E3C;">{{ $user->email }}</span>
                </td>
                <td>
                    <div class="flex flex-wrap gap-1">
                        @if($user->is_admin)
                            <span class="badge badge-admin">Admin</span>
                        @endif
                        @if($user->is_shadowbanned)
                            <span class="badge badge-shadowban">Shadowbanned</span>
                        @endif
                        @if(!$user->is_admin && !$user->is_shadowbanned)
                            <span class="text-xs" style="color:#E3C79A;">—</span>
                        @endif
                    </div>
                </td>
                <td>
                    <span class="text-xs" style="color:#C69C6D;">{{ $user->created_at->format('d M Y') }}</span>
                </td>
                <td class="text-right">
                    <div class="inline-flex items-center gap-1">
                        <!-- Impersonate -->
                        <form action="{{ route('admin.users.impersonate', $user) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="icon-btn" title="Login sebagai user ini">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </button>
                        </form>

                        <!-- Toggle Verified -->
                        <form id="toggle-verified-{{ $user->id }}" action="{{ route('admin.users.toggle-verified', $user) }}" method="POST" class="inline"
                            @submit.prevent="window.appConfirm('Verify User', 'Toggle status verifikasi untuk {{ addslashes($user->name) }}?', () => $el.submit(), 'Confirm', 'primary')">
                            @csrf
                            <button type="submit" class="icon-btn {{ $user->is_verified ? 'active' : '' }}" title="Toggle Verified">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                            </button>
                        </form>

                        <!-- Toggle Shadowban -->
                        <form id="toggle-shadowban-{{ $user->id }}" action="{{ route('admin.users.toggle-shadowban', $user) }}" method="POST" class="inline"
                            @submit.prevent="window.appConfirm('Shadowban User', 'Toggle shadowban untuk {{ addslashes($user->name) }}?', () => $el.submit(), 'Confirm')">
                            @csrf
                            <button type="submit" class="icon-btn {{ $user->is_shadowbanned ? 'active' : '' }}" title="Toggle Shadowban">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 14.12l4.242-4.242M3 3l18 18"></path></svg>
                            </button>
                        </form>

                        <!-- Toggle Admin -->
                        <form id="toggle-admin-{{ $user->id }}" action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="inline"
                            @submit.prevent="window.appConfirm('Toggle Admin', 'Ubah permission admin untuk {{ addslashes($user->name) }}?', () => $el.submit(), 'Confirm', 'primary')">
                            @csrf
                            <button type="submit" class="icon-btn {{ $user->is_admin ? 'active' : '' }}" title="Toggle Admin">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0112 2.714z"></path></svg>
                            </button>
                        </form>

                        <!-- Reset Password -->
                        <button onclick="adminResetPassword({{ $user->id }}, '{{ addslashes($user->name) }}')" class="icon-btn" title="Reset Password">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </button>

                        <!-- Hidden reset form -->
                        <form id="reset-form-{{ $user->id }}" action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="hidden">
                            @csrf
                            <input type="hidden" name="password" id="reset-input-{{ $user->id }}">
                        </form>

                        <!-- Delete -->
                        <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.delete', $user) }}" method="POST" class="inline"
                            @submit.prevent="window.appConfirm('Hapus User', 'Yakin ingin menghapus {{ addslashes($user->name) }} secara permanen?', () => $el.submit(), 'Hapus')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn" title="Hapus User" style="color:#fca5a5;" onmouseover="this.style.background='#FAF3E8';this.style.color='#8B5E3C';" onmouseout="this.style.background='';this.style.color='#fca5a5';">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $users->links() }}
</div>

<script>
function adminResetPassword(userId, userName) {
    window.appPrompt(
        'Reset Password',
        `Password baru untuk ${userName}:`,
        (newPassword) => {
            if (newPassword && newPassword.length >= 8) {
                document.getElementById(`reset-input-${userId}`).value = newPassword;
                document.getElementById(`reset-form-${userId}`).submit();
            } else if (newPassword) {
                window.showToast('Password minimal 8 karakter!', 'error');
            }
        },
        '',
        'Password baru (min. 8 karakter)'
    );
}
</script>
@endsection
