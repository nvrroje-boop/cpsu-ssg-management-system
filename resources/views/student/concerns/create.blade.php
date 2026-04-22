@extends('layouts.app')

@section('title', 'Submit Concern')
@section('page_title', 'Submit Concern')
@section('page_subtitle', 'Report a concern linked to an announcement or event and wait for an SSG reply.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-form-shell">
        <x-card title="New Concern" subtitle="Choose the related record, describe the issue clearly, and submit it for review.">
            <form action="{{ route('student.concerns.store') }}" method="POST" class="portal-form-stack">
                @csrf

                <div class="portal-form-grid">
                    <div class="field field--full">
                        <label for="source_reference">Select Related Item</label>
                        <select name="source_reference" id="source_reference" required>
                            <option value="">Select an announcement or event</option>
                            @php
                                $groupedOptions = collect($titleOptions)->groupBy('group');
                            @endphp
                            @foreach ($groupedOptions as $group => $options)
                                <optgroup label="{{ $group }}">
                                    @foreach ($options as $option)
                                        <option value="{{ $option['value'] }}" @selected(old('source_reference') === $option['value'])>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('source_reference')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field field--full">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" rows="7" required>{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="actions">
                    <x-button :href="route('student.concerns.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">Submit Concern</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection
