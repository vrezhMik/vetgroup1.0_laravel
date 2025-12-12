@php
    $json = $get('products_json');
    $productsText = $get('products');

    $rows = null;
    $prettyJson = null;

    if ($json) {
        // Handle both string and already-decoded array values
        $decoded = is_array($json) ? $json : json_decode($json, true);

        if (is_array($decoded)) {
            $rows = array_is_list($decoded) ? $decoded : [$decoded];
            $prettyJson = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    if (! $prettyJson && $json) {
        $prettyJson = $json;
    }
@endphp

@if (($rows && count($rows)) || $productsText || $prettyJson)
    <div
        x-data="{ showTable: false, showJson: {{ $prettyJson ? 'true' : 'false' }} }"
        class="space-y-3"
    >
        <div class="flex items-center gap-2">
            <div class="text-sm font-semibold text-gray-700">Products</div>

            @if ($rows && count($rows))
                <button
                    type="button"
                    class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50"
                    x-on:click="showTable = !showTable"
                >
                    <span x-show="showTable">Hide table</span>
                    <span x-show="!showTable">Show table</span>
                </button>
            @endif

            @if ($prettyJson)
                <button
                    type="button"
                    class="text-xs px-2 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50"
                    x-on:click="showJson = !showJson"
                >
                    <span x-show="showJson">Hide JSON</span>
                    <span x-show="!showJson">Show JSON</span>
                </button>
            @endif
        </div>

        @if ($rows && count($rows))
            @php
                $columns = collect($rows)
                    ->flatMap(fn ($row) => array_keys((array) $row))
                    ->unique()
                    ->values()
                    ->all();
            @endphp

            <div x-show="showTable" x-cloak>
                <div class="overflow-x-auto overflow-y-auto max-h-64 border border-gray-200 rounded-lg">
                    <table class="min-w-full text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach ($columns as $column)
                                    <th class="px-2 py-1 text-left font-semibold text-gray-700 border-b border-gray-200">
                                        {{ \Illuminate\Support\Str::title(str_replace('_', ' ', $column)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr class="border-t border-gray-100">
                                    @foreach ($columns as $column)
                                        <td class="px-2 py-1 align-top border-b border-gray-100">
                                            @php
                                                $value = data_get($row, $column);
                                            @endphp

                                            @if (is_array($value))
                                                {{ json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @elseif($productsText)
            <div class="space-y-1">
                <pre class="text-xs bg-gray-50 border border-gray-200 rounded-lg p-2 overflow-auto max-h-64">{{ $productsText }}</pre>
            </div>
        @endif

        @if ($prettyJson)
            <div x-show="showJson" x-cloak>
                <div class="text-xs text-gray-600 mb-1">Products JSON</div>
                <pre class="text-xs bg-gray-900 text-gray-100 rounded-lg p-2 overflow-auto max-h-64">{{ $prettyJson }}</pre>
            </div>
        @endif
    </div>
@endif
