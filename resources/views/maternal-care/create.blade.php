@extends('layouts.app')

@section('content')
    <x-page-header title="New Maternal Care Record" description="Antenatal assessment and prenatal visit.">
        <x-slot:actions>
            <a href="{{ route('maternal-care.index') }}" class="btn-secondary">Back</a>
            <button type="submit" form="maternal-care-form" class="btn-primary">Save Record</button>
        </x-slot:actions>
    </x-page-header>

    @include('maternal-care.partials.form', ['record' => null])
@endsection
