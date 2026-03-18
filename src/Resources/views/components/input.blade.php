@props(['icon' => null])

<div {{ $attributes->only('class')->merge(['class' => 'relative']) }}>
    <input {{ $attributes->except('class')->merge(['class' => 'pl-10 pr-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all shadow-sm w-full font-medium']) }}>
    @if($icon || isset($slot))
    <div class="absolute left-3 top-2.5 text-gray-400">
        {{ $icon ?? $slot }}
    </div>
    @endif
</div>