<x-filament::page>
    <style>
    /* ========================================
       FILAMENT-STYLE MEDIA LIBRARY
       ======================================== */

    .media-page {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
.media-pager__pagination p {
    display: none !important;
}
    /* --- Header Section --- */
    .media-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        margin-bottom: 50px;
    }

    .media-header h2 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #ffffff;
        line-height: 1.5;
    }

    .media-header p {
        margin: 0.25rem 0 0;
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.875rem;
        line-height: 1.5;
    }

    .media-header code {
        background: rgba(255, 255, 255, 0.1);
        padding: 0.125rem 0.375rem;
        border-radius: 0.375rem;
        font-size: 0.8125rem;
        color: rgba(255, 255, 255, 0.8);
    }

    /* --- Delete Button --- */
    .media-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        background: rgba(239, 68, 68, 0.15);
        color: #fca5a5;
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 0.625rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .media-button:hover:not(:disabled) {
        background: rgba(239, 68, 68, 0.25);
        border-color: rgba(239, 68, 68, 0.5);
        color: #fecaca;
    }

    .media-button:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.4);
    }

    .media-button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* --- Media Grid --- */
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    /* --- Media Card --- */
    .media-card {
        display: flex;
        flex-direction: column;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 0.75rem;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .media-card:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.15);
    }

    .media-card:has(input:checked) {
        background: rgba(250, 204, 21, 0.08);
        border-color: rgba(250, 204, 21, 0.4);
        box-shadow: 0 0 0 1px rgba(250, 204, 21, 0.2);
    }

    /* --- Card Header --- */
    .media-card-header {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.75rem;
    }

    .media-card-header input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        border-radius: 0.25rem;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: transparent;
        cursor: pointer;
        accent-color: #facc15;
        flex-shrink: 0;
    }

    .media-card-header input[type="checkbox"]:checked {
        background: #facc15;
        border-color: #facc15;
    }

    .media-name {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #ffffff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.4;
    }

    /* --- Card Size --- */
    .media-card-size {
        font-size: 0.75rem;
        color: rgba(255, 255, 255, 0.5);
        padding: 0 0.75rem 0.5rem;
        margin-top: -0.25rem;
    }

    /* --- Card Image --- */
    .media-card-image {
        flex: 1;
        min-height: 10rem;
        background: rgba(0, 0, 0, 0.2);
    }

    .media-card-image img {
        width: 100%;
        height: 100%;
        min-height: 10rem;
        max-height: 12rem;
        object-fit: cover;
        display: block;
    }

    /* --- Empty State --- */
    .media-page > p {
        text-align: center;
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.875rem;
        padding: 3rem 1rem;
        background: rgba(255, 255, 255, 0.03);
        border: 1px dashed rgba(255, 255, 255, 0.15);
        border-radius: 0.75rem;
    }

    /* ========================================
       PAGINATION / PAGER
       ======================================== */

    .media-pager {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 0.75rem;
    }

    /* --- Per Page Selector --- */
    .media-pager__per-page {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
        color: rgba(255, 255, 255, 0.6);
    }

    .media-pager__per-page label,
    .media-pager__per-page span {
        white-space: nowrap;
    }

    .media-pager__per-page select {
        appearance: none;
        background: rgba(255, 255, 255, 0.05);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.6)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.5rem center;
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.5rem;
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        font-size: 0.8125rem;
        font-weight: 500;
        cursor: pointer;
        outline: none;
        transition: all 0.15s ease;
    }

    .media-pager__per-page select:hover {
        background-color: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .media-pager__per-page select:focus {
        border-color: rgba(250, 204, 21, 0.5);
        box-shadow: 0 0 0 2px rgba(250, 204, 21, 0.2);
    }

    /* --- Pagination --- */
    .media-pager__pagination {
        display: flex;
        align-items: center;
    }

    .media-pager__pagination nav {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .media-pager__pagination a,
    .media-pager__pagination span {
        min-width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8125rem;
        font-weight: 500;
        border-radius: 0.5rem;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.7);
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.08);
        transition: all 0.15s ease;
    }

    .media-pager__pagination a:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
        color: #ffffff;
    }

    /* Active page */
    .media-pager__pagination .active span,
    .media-pager__pagination span[aria-current="page"] {
        background: rgba(250, 204, 21, 0.15);
        border-color: rgba(250, 204, 21, 0.4);
        color: #facc15;
    }

    /* Disabled pagination */
    .media-pager__pagination .disabled span {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Previous/Next buttons */
    .media-pager__pagination a[rel="prev"],
    .media-pager__pagination a[rel="next"] {
        padding: 0 0.75rem;
    }

    /* Ellipsis */
    .media-pager__pagination span:not([aria-current]):not(.active span) {
        background: transparent;
        border-color: transparent;
    }
</style>

    <div class="media-page">
        <form wire:submit.prevent="deleteSelected">
            <div class="media-header">
                <div>
                    <h2>Media library</h2>
                    <p>Manage uploads stored on the <code>uploads</code> disk.</p>
                </div>
                <button
                    type="submit"
                    class="media-button"
                    wire:loading.attr="disabled"
                    wire:target="deleteSelected"
                    :disabled="count($selectedImages) === 0"
                >
                    Delete selected ({{ count($selectedImages) }})
                </button>
            </div>

            @if ($images->isEmpty())
                <p>No media files have been uploaded yet.</p>
            @else
                <div class="media-grid">
                    @foreach ($images as $image)
                        @php
                            $safeId = str_replace(['/', '\\', '.'], ['-', '-', '-'], $image['path']);
                            $id = "media-{$safeId}";
                        @endphp

                        <label wire:key="media-{{ $safeId }}" for="{{ $id }}" class="media-card">
                            <div class="media-card-header">
                                <input id="{{ $id }}" type="checkbox" value="{{ $image['path'] }}" wire:model.live="selectedImages" />
                                <span class="media-name">{{ $image['name'] }}</span>
                            </div>
                            <div class="media-card-size">{{ $image['formatted_size'] ?? '' }}</div>
                            <div class="media-card-image">
                                <img src="{{ $image['url'] }}" alt="{{ $image['name'] }}" loading="lazy" />
                            </div>
                        </label>
                    @endforeach
                </div>
            @endif
        </form>

        @if (! $images->isEmpty())
       <div class="media-pager">
    <div class="media-pager__per-page">
        <label for="perPageSelector">Show</label>

        <select
            id="perPageSelector"
            wire:model="perPage"
            wire:change="setPerPage($event.target.value)"
            wire:click.stop
        >
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}">{{ $option }}</option>
            @endforeach
        </select>

        <span>items per page</span>
    </div>

    @if ($images->hasPages())
        <div class="media-pager__pagination">
            {{ $images->links() }}
        </div>
    @endif
</div>

        @endif
    </div>
</x-filament::page>
