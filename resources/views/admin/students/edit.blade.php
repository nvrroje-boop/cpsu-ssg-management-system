@extends('layouts.app')

@section('title', 'Edit Account')
@section('page_title', 'Edit Student or Officer')
@section('page_subtitle', 'Update the account record with valid year and section combinations.')

@push('page-css')
<link href="{{ asset('css/detail-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="portal-form-shell">
        <x-card title="Edit Account" subtitle="Update personal information, academic assignment, and optional password reset details.">
            <form method="POST" action="{{ route('admin.students.update', $student['id']) }}" id="studentEditForm" class="portal-form-stack">
                @csrf
                @method('PUT')

                <div class="portal-form-grid">
                    <div class="field">
                        <label for="role_id">Role</label>
                        <select id="role_id" name="role_id" required>
                            <option value="">Select role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role['id'] }}" @selected((int) old('role_id', $student['role_id']) === $role['id'])>{{ $role['role_label'] }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="name">Full Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $student['name']) }}" required>
                        @error('name')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="student_number">Student Number</label>
                        <input id="student_number" name="student_number" type="text" value="{{ old('student_number', $student['student_number']) }}">
                        @error('student_number')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $student['email']) }}" required>
                        @error('email')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $student['phone']) }}" placeholder="Optional contact number">
                        @error('phone')<span class="error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="course">Course</label>
                        <input id="course" name="course" type="text" value="{{ old('course', $student['course']) }}" placeholder="Optional course or program">
                        @error('course')<span class="error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="portal-form-section">
                    <div class="portal-form-section__header">
                        <h2 class="portal-form-section__title">Academic Assignment and Password</h2>
                        <p class="portal-form-section__text">Keep the account aligned with valid department, year, and section combinations.</p>
                    </div>

                    <div class="portal-form-grid">
                        <div class="field">
                            <label for="department_id">Department</label>
                            <select id="department_id" name="department_id" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department['id'] }}" @selected((int) old('department_id', $student['department_id']) === $department['id'])>{{ $department['department_name'] }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="year">Year</label>
                            <select id="year" name="year" required>
                                <option value="">Select year</option>
                                <option value="1" @selected((string) old('year', $student['year']) === '1')>1</option>
                                <option value="2" @selected((string) old('year', $student['year']) === '2')>2</option>
                                <option value="3" @selected((string) old('year', $student['year']) === '3')>3</option>
                                <option value="4" @selected((string) old('year', $student['year']) === '4')>4</option>
                            </select>
                            @error('year')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="section_id">Section</label>
                            <select id="section_id" name="section_id" required disabled>
                                <option value="">Select year first</option>
                            </select>
                            <small id="sectionHelp" class="portal-helper">Valid sections follow the selected year rule.</small>
                            @error('section_id')<span class="error">{{ $message }}</span>@enderror
                        </div>

                        <div class="field">
                            <label for="password">New Password</label>
                            <div class="input-with-action">
                                <input id="password" name="password" type="text" value="{{ old('password') }}" placeholder="Leave blank to keep the current password">
                                <x-button id="generatePassword" type="button" variant="secondary">Generate</x-button>
                            </div>
                            @error('password')<span class="error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <x-button :href="route('admin.students.index')" variant="secondary">Back</x-button>
                    <x-button type="submit">Update Account</x-button>
                </div>
            </form>
        </x-card>
    </div>
@endsection

@push('page-js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sectionRules = @json($sectionRules);
        const sections = @json($sectionsForDropdown);
        const previousSectionId = @json(old('section_id', $student['section_id']));

        const form = document.getElementById('studentEditForm');
        const yearSelect = document.getElementById('year');
        const sectionSelect = document.getElementById('section_id');
        const departmentSelect = document.getElementById('department_id');
        const sectionHelp = document.getElementById('sectionHelp');
        const generateButton = document.getElementById('generatePassword');
        const passwordInput = document.getElementById('password');

        const placeholderOption = (label) => {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = label;
            return option;
        };

        const randomString = (length) => {
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%^&*';
            let result = '';
            for (let index = 0; index < length; index += 1) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        };

        const getValidSections = () => {
            const selectedYear = yearSelect.value;
            const selectedDepartmentId = departmentSelect.value;
            const yearSections = sections[selectedYear] ?? [];
            const allowedLetters = sectionRules[selectedYear] ?? [];

            return yearSections.filter((section) => {
                const matchesDepartment = !selectedDepartmentId || String(section.department_id) === selectedDepartmentId;
                const matchesLetterRule = allowedLetters.includes(section.letter);
                return matchesDepartment && matchesLetterRule;
            });
        };

        const renderSectionOptions = (preferredSectionId = '') => {
            const selectedYear = yearSelect.value;
            const validSections = getValidSections();

            sectionSelect.innerHTML = '';
            if (!selectedYear) {
                sectionSelect.appendChild(placeholderOption('Select year first'));
                sectionSelect.disabled = true;
                sectionHelp.textContent = 'Choose a year to load valid sections.';
                return;
            }

            if (validSections.length === 0) {
                sectionSelect.appendChild(placeholderOption('No valid sections available'));
                sectionSelect.disabled = true;
                sectionHelp.textContent = 'No sections match the selected department and year.';
                return;
            }

            sectionSelect.appendChild(placeholderOption('Select section'));
            validSections.forEach((section) => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = section.name;
                option.dataset.year = selectedYear;
                option.dataset.letter = section.letter;
                option.dataset.departmentId = section.department_id;
                sectionSelect.appendChild(option);
            });

            sectionSelect.disabled = false;
            sectionHelp.textContent = `Valid letters for year ${selectedYear}: ${(sectionRules[selectedYear] ?? []).join(', ')}`;

            if (preferredSectionId && [...sectionSelect.options].some((option) => option.value === String(preferredSectionId))) {
                sectionSelect.value = String(preferredSectionId);
                return;
            }

            if (validSections.length === 1) {
                sectionSelect.value = String(validSections[0].id);
            }
        };

        generateButton.addEventListener('click', () => {
            passwordInput.value = randomString(12);
        });

        yearSelect.addEventListener('change', () => renderSectionOptions());
        departmentSelect.addEventListener('change', () => renderSectionOptions());

        form.addEventListener('submit', (event) => {
            const selectedYear = yearSelect.value;
            const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
            if (!selectedYear || !selectedOption || !selectedOption.value) {
                return;
            }

            const allowedLetters = sectionRules[selectedYear] ?? [];
            if (selectedOption.dataset.year !== selectedYear || !allowedLetters.includes(selectedOption.dataset.letter)) {
                event.preventDefault();
                renderSectionOptions();
                window.alert('The selected section is invalid for the chosen year. Please pick a valid section.');
            }
        });

        renderSectionOptions(previousSectionId);
    });
</script>
@endpush
