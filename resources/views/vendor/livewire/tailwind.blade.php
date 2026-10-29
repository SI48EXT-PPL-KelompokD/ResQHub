@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4">
            
            {{-- Mobile Previous / Next Buttons --}}
            <div class="flex justify-between w-full sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded-xl cursor-not-allowed">
                        &laquo; Sebelumnya
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-blue-600 shadow-2xs transition">
                        &laquo; Sebelumnya
                    </button>
                @endif

                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-blue-600 shadow-2xs transition">
                        Selanjutnya &raquo;
                    </button>
                @else
                    <span class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-slate-300 bg-white border border-slate-200 rounded-xl cursor-not-allowed">
                        Selanjutnya &raquo;
                    </span>
                @endif
            </div>

            {{-- Desktop Information & Links --}}
            <div class="hidden sm:flex sm:items-center sm:justify-between w-full">
                <div>
                    <p class="text-xs sm:text-sm text-slate-500">
                        <span>Menampilkan</span>
                        <span class="font-semibold text-slate-800">{{ $paginator->firstItem() ?? 0 }}</span>
                        <span>sampai</span>
                        <span class="font-semibold text-slate-800">{{ $paginator->lastItem() ?? 0 }}</span>
                        <span>dari</span>
                        <span class="font-semibold text-slate-800">{{ $paginator->total() }}</span>
                        <span>hasil</span>
                    </p>
                </div>

                <div class="flex items-center space-x-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Sebelumnya">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-slate-300 bg-white border border-slate-200 rounded-lg cursor-not-allowed text-xs" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="inline-flex items-center justify-center w-8 h-8 text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 hover:border-slate-300 transition shadow-2xs text-xs" aria-label="Sebelumnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center justify-center w-8 h-8 text-slate-400 bg-white border border-slate-200 rounded-lg cursor-default text-xs font-semibold">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page">
                                            <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-semibold text-white bg-blue-600 border border-blue-600 rounded-lg shadow-2xs">
                                                {{ $page }}
                                            </span>
                                        </span>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="inline-flex items-center justify-center w-8 h-8 text-xs font-medium text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 hover:border-slate-300 transition shadow-2xs" aria-label="Halaman {{ $page }}">
                                            {{ $page }}
                                        </button>
                                    @endif
                                </span>
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="inline-flex items-center justify-center w-8 h-8 text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:text-blue-600 hover:border-slate-300 transition shadow-2xs text-xs" aria-label="Selanjutnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="Selanjutnya">
                            <span class="inline-flex items-center justify-center w-8 h-8 text-slate-300 bg-white border border-slate-200 rounded-lg cursor-not-allowed text-xs" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </div>
            </div>

        </nav>
    @endif
</div>
