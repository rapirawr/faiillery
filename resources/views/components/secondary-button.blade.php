<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-soft-cream border border-sand rounded-md font-semibold text-xs text-espresso uppercase tracking-widest shadow-sm hover:bg-cream focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
 {{ $slot }}
</button>
