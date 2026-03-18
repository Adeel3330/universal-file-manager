@props(['item', 'selectedIds' => [], 'itemUrl'])

<div wire:key="media-{{ $item->id }}"
    x-on:click.stop="$wire.selectMedia({{ $item->id }})"
    x-on:dblclick.stop="{{ $item->is_folder ? '$wire.navigateTo('.$item->id.')' : ( $item->is_image ? 'openPreview(\''.$itemUrl.'\', \''.$item->name.'\', \'image\')' : '' ) }}"
    x-on:contextmenu.prevent.stop="openMenu($event, {{ $item->id }}, {{ $item->is_folder ? 'true' : 'false' }})"
    class="group relative rounded-2xl p-5 transition-all cursor-pointer overflow-hidden border-2 {{ in_array($item->id, $selectedIds) ? 'bg-blue-50 border-blue-400 shadow-lg ring-2 ring-blue-100' : 'bg-white border-transparent hover:border-gray-100 hover:shadow-md' }}">

    <div class="flex flex-col items-center">
        <div class="w-full aspect-square flex items-center justify-center mb-4 bg-gray-50 rounded-2xl transition-all group-hover:scale-110 group-hover:rotate-1 shadow-inner relative overflow-hidden">
            @if($item->is_folder)
            <svg class="w-20 h-20 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
            </svg>
            @elseif($item->is_image)
            <div class="w-full h-full relative group/img">
                <img src="{{ $itemUrl }}" alt="{{ $item->name }}" class="w-full h-full object-cover rounded-2xl shadow-sm transition-transform duration-500 group-hover/img:scale-110">
                <div class="absolute inset-0 bg-blue-600/0 group-hover/img:bg-blue-600/10 transition-all rounded-2xl"></div>
            </div>
            @else
            <svg class="w-20 h-20 text-blue-400 drop-shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            @endif
        </div>

        <span class="w-full text-center text-sm font-bold text-gray-800 truncate px-2" title="{{ $item->name }}">
            {{ $item->name }}
        </span>
        @if(!$item->is_folder)
        <span class="text-[10px] font-black text-gray-400 mt-1 uppercase tracking-tighter bg-gray-100 px-2 py-0.5 rounded-full">
            {{ number_format($item->size / 1024, 1) }} KB
        </span>
        @endif
    </div>

    @if(in_array($item->id, $selectedIds))
    <div class="absolute top-3 right-3 animate-in zoom-in duration-200">
        <div class="bg-blue-600 rounded-full p-2 text-white shadow-xl ring-4 ring-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 20 20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7" />
            </svg>
        </div>
    </div>
    @endif
</div>