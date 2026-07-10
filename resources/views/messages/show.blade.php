@extends('layouts.app')

@section('title', 'Chat dengan ' . $user->name . ' - Failerry')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 h-[calc(100vh-120px)] flex flex-col" x-data="chatHandler()">
 <!-- Chat Header -->
 <div class="flex items-center justify-between p-6 bg-soft-cream rounded-t-[32px] border border-sand shadow-sm">
 <div class="flex items-center gap-4">
 <a href="{{ route('messages.index') }}" class="p-2 -ml-2 rounded-full hover:bg-cream transition-colors text-caramel">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
 </a>
 <a href="{{ route('profile.show', $user->username) }}" class="flex items-center gap-3 group">
 <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover">
 <div>
 <h3 class="font-bold text-cocoa group-hover:underline flex items-center gap-1.5">
 {{ $user->name }}
 @if($user->is_verified)
 <x-verified-badge size="w-3.5 h-3.5" checkSize="w-2.5 h-2.5" />
 @endif
 </h3>
 <p class="text-[10px] text-caramel font-black uppercase tracking-widest">Online</p>
 </div>
 </a>
 </div>
 <button class="p-2 rounded-full hover:bg-cream transition-colors text-caramel">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path></svg>
 </button>
 </div>

 <!-- Messages Container -->
 <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-soft-cream border-x border-sand hide-scrollbar" id="messages-container" x-ref="container">
 <template x-for="message in messages" :key="message.id">
 <div :class="message.sender_id == {{ auth()->id() }} ? 'flex justify-end' : 'flex justify-start'" class="animate-modal-up">
 <div :class="message.sender_id == {{ auth()->id() }} ? 'bg-brown text-white rounded-t-2xl rounded-bl-2xl' : 'bg-soft-cream text-cocoa rounded-t-2xl rounded-br-2xl'" 
 class="max-w-[80%] px-5 py-3 shadow-sm">
 <p class="text-sm leading-relaxed" x-text="message.body"></p>
 <div :class="message.sender_id == {{ auth()->id() }} ? 'text-white/60' : 'text-caramel'" class="text-[9px] font-bold uppercase tracking-widest mt-1 text-right">
 <span x-text="formatDate(message.created_at)"></span>
 </div>
 </div>
 </div>
 </template>
 </div>

 <!-- Chat Input -->
 <div class="p-6 bg-soft-cream rounded-b-[32px] border border-sand shadow-sm">
 <form @submit.prevent="sendMessage" class="flex gap-4">
 <input type="text" x-model="newMessage" placeholder="Ketik pesan..." class="flex-1 bg-soft-cream border-none rounded-2xl px-6 py-4 text-sm text-cocoa focus:ring-2 focus:ring-sand transition-all" :disabled="loading">
 <button type="submit" class="w-14 h-14 bg-brown text-white rounded-2xl flex items-center justify-center shadow-lg shadow-warm hover:scale-105 active:scale-95 transition-all disabled:opacity-50" :disabled="loading || !newMessage.trim()">
 <svg x-show="!loading" class="w-6 h-6 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
 <svg x-show="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
 </button>
 </form>
 </div>
</div>

<script>
function chatHandler() {
 return {
 messages: @json($messages),
 newMessage: '',
 loading: false,
 chatChannel: null,
 init() {
 this.scrollToBottom();
 this.subscribeToRealtime();
 },
 subscribeToRealtime() {
 if (window.supabase) {
 const channel = window.supabase.channel('conversation_{{ $conversation->id }}');
 
 channel
 .on('broadcast', { event: 'new_message' }, payload => {
 if (payload.sender_id != {{ auth()->id() }}) {
 this.messages.push(payload);
 this.scrollToBottom();
 }
 })
 .subscribe();
 
 this.chatChannel = channel;
 }
 },
 sendMessage() {
 if (!this.newMessage.trim()) return;
 this.loading = true;
 
 const messageBody = this.newMessage;
 
 axios.post('{{ route('messages.store', $user->username) }}', {
 body: messageBody
 }).then(res => {
 const sentMessage = res.data.message;
 this.messages.push(sentMessage);
 
 // Broadcast to the other user
 if (this.chatChannel) {
 this.chatChannel.send({
 type: 'broadcast',
 event: 'new_message',
 payload: sentMessage
 });
 }
 
 this.newMessage = '';
 this.scrollToBottom();
 }).finally(() => {
 this.loading = false;
 });
 },
 scrollToBottom() {
 this.$nextTick(() => {
 const container = this.$refs.container;
 container.scrollTop = container.scrollHeight;
 });
 },
 formatDate(dateStr) {
 const date = dateStr ? new Date(dateStr) : new Date();
 if (isNaN(date.getTime())) return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
 return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
 }
 }
}
</script>
@endsection
