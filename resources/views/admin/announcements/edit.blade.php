@extends('layouts.app')

@section('title', 'Edit Announcement')
@section('page_title', 'Edit Announcement')
@section('page_subtitle', 'Update the announcement content and recipient filters before it goes out to students.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp

    <div class="portal-form-shell">
        <x-card title="Announcement details" subtitle="Refine the message, audience filters, and summary shown in student-facing lists.">
            <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.update', $announcement->id) }}" class="portal-form-stack">
                @csrf
                @method('PUT')

                <div class="portal-form-grid">
                    <div class="field field--full">
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $announcement->title) }}" required>
                        @error('title')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field field--full">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="8" required>{{ old('message', $announcement->message) }}</textarea>
                        @error('message')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field field--full">
                        <label for="description">Short Description</label>
                        <textarea id="description" name="description" rows="3">{{ old('description', $announcement->description) }}</textarea>
                        @error('description')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <hr class="portal-divider">

                <section class="portal-form-section">
                    <div class="portal-form-section__header">
                        <h2 class="portal-form-section__title">Target filters</h2>
                        <p class="portal-form-section__text">Adjust the department, year, and section filters if this announcement should only reach a specific student group.</p>
                    </div>

                    <div class="portal-form-grid">
                        <div class="field">
                            <label for="filter_department_id">Department</label>
                            <select id="filter_department_id" name="filter_department_id">
                                <option value="">All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) old('filter_department_id', $announcement->target_filters['department_id'] ?? '') === (string) $department->id)>
                                        {{ $department->department_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('filter_department_id')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="filter_year">Year</label>
                            <select id="filter_year" name="filter_year">
                                <option value="">All Years</option>
                                <option value="1" @selected((string) old('filter_year', $announcement->target_filters['year'] ?? '') === '1')>1</option>
                                <option value="2" @selected((string) old('filter_year', $announcement->target_filters['year'] ?? '') === '2')>2</option>
                                <option value="3" @selected((string) old('filter_year', $announcement->target_filters['year'] ?? '') === '3')>3</option>
                                <option value="4" @selected((string) old('filter_year', $announcement->target_filters['year'] ?? '') === '4')>4</option>
                            </select>
                            @error('filter_year')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="filter_section_id">Section</label>
                            <select id="filter_section_id" name="filter_section_id">
                                <option value="">All Sections</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}" data-year-level="{{ $section->year_level }}" @selected((string) old('filter_section_id', $announcement->target_filters['section_id'] ?? '') === (string) $section->id)>
                                        {{ $section->section_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('filter_section_id')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                <div class="actions">
                    <x-button :href="route($managementRoutePrefix.'.announcements.index')" variant="secondary">Back</x-button>
                    <x-button type="submit">Update Announcement</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const departmentSelect = document.getElementById('filter_department_id');
        const yearSelect = document.getElementById('filter_year');
        const sectionSelect = document.getElementById('filter_section_id');
        const sectionOptions = Array.from(sectionSelect.querySelectorAll('option')).map((option) => ({
            value: option.value,
            label: option.textContent,
            departmentId: option.dataset.departmentId ?? '',
            yearLevel: option.dataset.yearLevel ?? '',
        }));

        const syncSections = () => {
            const departmentId = departmentSelect.value;
            const yearLevel = yearSelect.value;
            const currentValue = sectionSelect.value;

            sectionSelect.innerHTML = '<option value="">All Sections</option>';

            sectionOptions
                .filter((option) => option.value !== '')
                .filter((option) => !departmentId || option.departmentId === departmentId)
                .filter((option) => !yearLevel || option.yearLevel === yearLevel)
                .forEach((option) => {
                    const element = document.createElement('option');
                    element.value = option.value;
                    element.textContent = option.label;
                    element.selected = currentValue === option.value;
                    sectionSelect.appendChild(element);
                });

            if (currentValue && !Array.from(sectionSelect.options).some((option) => option.value === currentValue)) {
                sectionSelect.value = '';
            }
        };

        departmentSelect.addEventListener('change', syncSections);
        yearSelect.addEventListener('change', syncSections);
        syncSections();
    });
</script>
@endpush
