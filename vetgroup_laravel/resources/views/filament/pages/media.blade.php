<x-filament::page>
    <div class="space-y-6">
        <form wire:submit.prevent="deleteSelected">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
                <div>
                    <h2 class="text-lg font-semibold tracking-tight text-gray-900 dark:text-white">
                        Media library
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Manage uploads stored on the <code>uploads</code> disk.
                    </p>
                </div>
<x-filament::button
    type="submit"
    color="danger"
    wire:loading.attr="disabled"
    wire:target="deleteSelected"
    :disabled="count($selectedImages) === 0"
>
    Delete selected ({{ count($selectedImages) }})
</x-filament::button>



            </div>

            @if ($images->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No media files have been uploaded yet.
                </p>
            @else
                {{-- z-0 + isolate prevents cards stealing clicks from pagination area --}}
                <div class="relative z-0 isolate grid gap-4 grid-cols-2 md:grid-cols-4">
                    @foreach ($images as $image)
                        @php
                            $safeId = str_replace(['/', '\\', '.'], ['-', '-', '-'], $image['path']);
                            $id = "media-{$safeId}";
                        @endphp

                        <label
                            wire:key="media-{{ $safeId }}"
                            for="{{ $id }}"
                            class="flex max-h-[22rem] flex-col cursor-pointer overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-primary-500/20 hover:shadow-lg dark:border-gray-700 dark:bg-gray-900"
                        >
                            <div class="flex h-14 items-center gap-3 px-4">
                                <input
                                    id="{{ $id }}"
                                    type="checkbox"
                                    value="{{ $image['path'] }}"
                                    wire:model.live="selectedImages"
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                />

                                <div class="flex-1 truncate text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $image['name'] }}
                                </div>
                            </div>

                            <div class="flex-1 bg-gray-100 dark:bg-gray-800">
                                <div class="relative w-full overflow-hidden aspect-square">
                                    <img
                                        src="{{ $image['url'] }}"
                                        alt="{{ $image['name'] }}"
                                        class="absolute inset-0 h-full w-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </form>

        @if (! $images->isEmpty())
            <div class="relative z-10 mt-6 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white/40 p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900/60 dark:text-gray-300 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2">
                    <label for="perPageSelector" class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        Show
                    </label>

                    <select
                        id="perPageSelector"
                        wire:model.live="perPage"
                        wire:change="setPerPage($event.target.value)"
                        wire:click.stop
                        class="min-w-[5rem] rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs font-medium text-gray-700 outline-none transition focus:border-primary-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"
                    >
                        @foreach ($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>

                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        items per page
                    </span>
                </div>

                @if ($images->hasPages())
                    <div class="flex justify-end">
                        {{ $images->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-filament::page>
