@php
    // Prefer the current form state, fall back to the record's stored value.
    use Illuminate\Support\Facades\Storage;

    $stateImage = $get('image');
    $imagePath = $stateImage ?: ($record->image ?? null);

    if ($imagePath) {
        $path = ltrim($imagePath, '/');
        $path = preg_replace('#^uploads/#', '', $path);
        $imageUrl = Storage::disk('uploads')->url($path);
    } else {
        $imageUrl = null;
    }
@endphp

@if ($imageUrl)
    <div class="flex items-center gap-3">
        <div class="text-sm text-gray-600">Preview:</div>
        <img src="{{ $imageUrl }}" alt="Product image" class="h-10 w-10 object-cover rounded">
    </div>
@endif
