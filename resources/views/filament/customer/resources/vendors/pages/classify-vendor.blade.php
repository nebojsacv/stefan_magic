<x-filament-panels::page>
    {{-- Vendor Info Header --}}
    <x-filament::section>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <span style="color: white; font-size: 22px; font-weight: 700;">
                    {{ strtoupper(substr($this->record->name, 0, 1)) }}
                </span>
            </div>
            <div>
                <h2 style="font-size: 20px; font-weight: 700; margin: 0;">{{ $this->record->name }}</h2>
                <div style="display: flex; gap: 16px; margin-top: 4px; font-size: 14px; color: #6b7280;">
                    <span>📧 {{ $this->record->poc_email }}</span>
                    @if($this->record->industry)
                        <span>🏢 {{ $this->record->industry }}</span>
                    @endif
                    @if($this->record->poc_name)
                        <span>👤 {{ $this->record->poc_name }}</span>
                    @endif
                </div>
            </div>
            <div style="margin-left: auto;">
                @if($this->record->classification_status === 'approved')
                    <x-filament::badge color="success" size="lg">
                        ✓ Classified
                    </x-filament::badge>
                @elseif($this->record->classification_status === 'pending_approval')
                    <x-filament::badge color="warning" size="lg">
                        Pending Approval
                    </x-filament::badge>
                @else
                    <x-filament::badge color="gray" size="lg">
                        Unclassified
                    </x-filament::badge>
                @endif
            </div>
        </div>
    </x-filament::section>

    {{-- Classification Form --}}
    <form wire:submit="saveClassification">
        {{ $this->form }}

        <div style="margin-top: 16px; display: flex; gap: 12px; justify-content: flex-end;">
            <x-filament::button
                type="submit"
                color="primary"
                icon="heroicon-m-check-circle"
            >
                Save Classification
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
