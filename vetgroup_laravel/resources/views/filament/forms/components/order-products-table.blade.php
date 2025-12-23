@php
    $products = $get('products');

    if (is_string($products)) {
        $decoded = json_decode($products, true);
        $products = is_array($decoded) ? $decoded : [];
    }

    $rows = is_array($products) ? $products : [];
@endphp

@if ($rows && count($rows))
    <div>
        <strong>Products</strong>

        <div style="max-height: 260px; overflow: auto; margin-top: 6px;">
            <table border="1" cellspacing="0" cellpadding="6" style="border-collapse: collapse; width: 100%; font-size: 12px;">
                <thead style="background-color: #f3f4f6;">
                    <tr>
                        <th align="left">Name</th>
                        <th align="left">Description</th>
                        <th align="right">Qty</th>
                        <th align="right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ data_get($row, 'name') }}</td>
                            <td>{{ data_get($row, 'description') }}</td>
                            <td align="right">{{ data_get($row, 'qty') }}</td>
                            <td align="right">{{ data_get($row, 'price') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
