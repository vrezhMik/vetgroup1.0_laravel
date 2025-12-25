<x-filament::widget>
    <x-filament::card>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold">
                    {{ $this->heading }}
                </h2>
                <p class="text-sm text-gray-500">
                    Run the same product sync as the hourly cron.
                </p>
            </div>

            <div class="shrink-0">
                <x-filament::button
                    color="primary"
                    wire:click="syncProducts"
                >
                    Sync Products Now
                </x-filament::button>
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>
