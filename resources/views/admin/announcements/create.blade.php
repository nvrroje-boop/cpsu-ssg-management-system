@extends('layouts.app')

@section('title', 'Create Announcement')
@section('page_title', 'Create Announcement')
@section('page_subtitle', 'Draft the announcement, then narrow the recipient list by department, year, or section before sending.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $managementRoutePrefix = request()->routeIs('officer.*') ? 'officer' : 'admin';
    @endphp

    <div class="portal-form-shell">
        <x-card title="Announcement draft" subtitle="Write the message students will read in the portal and receive through email.">
            <form method="POST" action="{{ route($managementRoutePrefix.'.announcements.store') }}" id="announcementCreateForm" class="portal-form-stack">
                @csrf

                <div class="portal-form-grid">
                    <div class="field field--full">
                        <label for="title">Title</label>
                        <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                        @error('title')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field field--full">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="8" required>{{ old('message') }}</textarea>
                        @error('message')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field field--full">
                        <label for="description">Short Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Optional summary used in listings and previews">{{ old('description') }}</textarea>
                        @error('description')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <hr class="portal-divider">

                <section class="portal-form-section">
                    <div class="portal-form-section__header">
                        <h2 class="portal-form-section__title">Student filter for email delivery</h2>
                        <p class="portal-form-section__text">Use a single filter or combine department, year, and section to target only the matching students before this draft is sent.</p>
                    </div>

                    <div class="portal-form-grid">
                        <div class="field">
                            <label for="filter_department_id">Department</label>
                            <select id="filter_department_id" name="filter_department_id">
                                <option value="">All Departments</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) old('filter_department_id') === (string) $department->id)>
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
                                <option value="1" @selected(old('filter_year') === '1')>1</option>
                                <option value="2" @selected(old('filter_year') === '2')>2</option>
                                <option value="3" @selected(old('filter_year') === '3')>3</option>
                                <option value="4" @selected(old('filter_year') === '4')>4</option>
                            </select>
                            @error('filter_year')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="filter_section_id">Section</label>
                            <select id="filter_section_id" name="filter_section_id">
                                <option value="">All Sections</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}" data-department-id="{{ $section->department_id }}" data-year-level="{{ $section->year_level }}" @selected((string) old('filter_section_id') === (string) $section->id)>
                                        {{ $section->section_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('filter_section_id')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="previewRecipientsButton">Recipient Preview</label>
                            <x-button type="button" id="previewRecipientsButton" variant="secondary">Preview Matching Students</x-button>
                        </div>
                    </div>

                    <div id="filterPreviewCard" class="portal-note-box portal-note-box--accent portal-preview-card">
                        <div class="portal-preview-card__meta">
                            <strong id="recipientCountText">0 students match the current filter.</strong>
                            <p class="portal-preview-card__hint">Preview shows the first 10 matching students.</p>
                        </div>
                        <div id="recipientPreviewList"></div>
                    </div>
                </section>

                <div class="actions">
                    <x-button :href="route($managementRoutePrefix.'.announcements.index')" variant="secondary">Cancel</x-button>
                    <x-button type="submit">Create Draft</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const previewButton = document.getElementById('previewRecipientsButton');
        const previewCard = document.getElementById('filterPreviewCard');
        const recipientCountText = document.getElementById('recipientCountText');
        const recipientPreviewList = document.getElementById('recipientPreviewList');
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

        const renderPreviewTable = (students) => `
            <div class="portal-table-wrap">
                <table class="portal-responsive-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Student No.</th>
                            <th>Email</th>
                            <th>Section</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${students.map((student) => `
                            <tr>
                                <td data-label="Name">${student.name}</td>
                                <td data-label="Student No.">${student.student_number ?? '-'}</td>
                                <td data-label="Email">${student.email}</td>
                                <td data-label="Section">${student.section ?? '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        departmentSelect.addEventListener('change', syncSections);
        yearSelect.addEventListener('change', syncSections);
        syncSections();

        previewButton.addEventListener('click', async () => {
            previewButton.disabled = true;
            previewButton.textContent = 'Loading Preview...';

            try {
                const response = await fetch('{{ route($managementRoutePrefix.'.announcements.target-preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        department_id: departmentSelect.value,
                        year: yearSelect.value,
                        section_id: sectionSelect.value,
                    }),
                });

                const data = await response.json();
                previewCard.classList.add('is-visible');
                recipientCountText.textContent = `${data.count} student${data.count === 1 ? '' : 's'} match the current filter.`;

                if (!data.students.length) {
                    recipientPreviewList.innerHTML = '<p class="portal-preview-message">No students found for the selected filters.</p>';
                    return;
                }

                recipientPreviewList.innerHTML = renderPreviewTable(data.students);
            } catch (error) {
                previewCard.classList.add('is-visible');
                recipientCountText.textContent = 'Unable to load the filtered student preview.';
                recipientPreviewList.innerHTML = '<p class="portal-preview-message">Check the filter values and try again.</p>';
            } finally {
                previewButton.disabled = false;
                previewButton.textContent = 'Preview Matching Students';
            }
        });
    });
</script>
@endpush
