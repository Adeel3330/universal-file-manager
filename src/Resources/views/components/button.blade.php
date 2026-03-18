@props(['variant' => 'primary'])

@php
$baseClasses = 'transition-all disabled:opacity-30 disabled:cursor-not-allowed flex items-center gap-2';
$variants = [
'primary' => 'px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg active:scale-95 font-bold',
'secondary' => 'px-5 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 shadow-sm active:scale-95 font-bold',
'danger' => 'px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white shadow-lg active:scale-95 font-bold',
'success' => 'px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white shadow-lg active:scale-95 font-bold',
'ghost' => 'p-2.5 rounded-xl hover:bg-white bg-gray-100 text-gray-700 border border-gray-200 shadow-sm active:scale-95',
'menu' => 'w-full text-left px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 flex items-center gap-3 transition-colors',
];
$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>