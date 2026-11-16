@extends('layouts.app')

@section('content')
    <x-page-header title="Edit Maternal Care Record" :description="$record->patient->full_name">
        <x-slot:actions>
            <a href="{{ route('maternal-care.index') }}" class="btn-secondary">Back</a>
            <button type="submit" form="maternal-care-form" class="btn-primary">Save Changes</button>
        </x-slot:actions>
    </x-page-header>

    @include('maternal-care.partials.form', ['record' => $record])
@endsection
