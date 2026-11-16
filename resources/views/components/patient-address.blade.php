@props([
    'value' => null,
    'id' => 'patient-address',
])

@php
    $defaultAddress = 'San Francisco, Talibon, Bohol';
    $address = (string) old('address', filled($value) ? $value : $defaultAddress);
    $address = filled($address) ? $address : $defaultAddress;
    $selectedPurok = old('purok');

    if (preg_match('/^(Purok [1-7])\s*,?\s*(.*)$/i', $address, $matches)) {
        $selectedPurok = old('purok', ucfirst(strtolower($matches[1])));
        $address = filled($matches[2]) ? $matches[2] : $defaultAddress;
    }

    $purokOptions = collect(range(1, 7))
        ->mapWithKeys(fn ($number) => ["Purok {$number}" => "Purok {$number}"])
        ->all();
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <x-form.select
        label="Purok"
        name="purok"
        :options="$purokOptions"
        :value="$selectedPurok"
        :id="$id . '-purok'"
        placeholder="Select purok"
        required
    />
    <x-form.input
        label="Address"
        name="address"
        :value="$address"
        :id="$id"
        required
    />
</div>
