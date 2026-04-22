@extends('layouts.app')

@section('title', 'Accounts')
@section('page_title', 'Student & Officer Records')
@section('page_subtitle', 'Review campus accounts by department, resend credentials, and keep clearance-related records organized.')

@push('page-css')
<link href="{{ asset('css/portal-pages.css') }}" rel="stylesheet">
@endpush

@section('content')
    @php
        $sortBy = request('sort', 'name');
        $groupedStudents = $studentService->getStudentsGroupedByDepartment($sortBy);
    @endphp

    <div class="portal-page-stack">
        <section class="portal-page-lead">
            <div>
                <h1 class="portal-page-lead__title">Account directory</h1>
                <p class="portal-page-lead__text">Manage student and officer portal records organized by department so account updates and clearance checks stay easier to audit.</p>
            </div>
            <div class="portal-inline-actions">
                <x-button :href="route('admin.students.create')">
                    <i class="fas fa-plus" aria-hidden="true"></i>
                    <span>Add Account</span>
                </x-button>
            </div>
        </section>

        <x-card title="Sort account view" subtitle="Change the grouping order without leaving the page.">
            <div class="portal-toolbar">
                <div class="portal-toolbar__group">
                    <div class="portal-select-inline">
                        <label for="sortBy">Sort By</label>
                        <select id="sortBy" onchange="window.location.href = '?sort=' + encodeURIComponent(this.value);">
                            <option value="name" @selected(request('sort', 'name') === 'name')>Name</option>
                            <option value="student_number" @selected(request('sort') === 'student_number')>Student Number</option>
                            <option value="role" @selected(request('sort') === 'role')>Role</option>
                            <option value="section" @selected(request('sort') === 'section')>Section</option>
                        </select>
                    </div>
                </div>
            </div>
        </x-card>

        @forelse ($groupedStudents as $departmentName => $students)
            <section class="portal-group-card">
                <header class="portal-group-card__header">
                    <h2 class="portal-group-card__title">{{ $departmentName }}</h2>
                    <span class="portal-group-card__count">{{ count($students) }}</span>
                </header>

                <x-card padding="flush">
                    <div class="portal-table-wrap">
                        <table class="portal-responsive-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Student No.</th>
                                    <th>Role</th>
                                    <th>Email</th>
                                    <th>Section</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr>
                                        <td data-label="Name">
                                            <div class="portal-table-cell-stack">
                                                <p class="portal-table-cell-stack__title">{{ $student['name'] }}</p>
                                            </div>
                                        </td>
                                        <td data-label="Student No."><span class="portal-code">{{ $student['student_number'] }}</span></td>
                                        <td data-label="Role"><span class="badge badge-primary">{{ $student['role_name'] }}</span></td>
                                        <td data-label="Email"><span class="portal-table-cell-stack__meta">{{ $student['email'] }}</span></td>
                                        <td data-label="Section"><span class="portal-table-cell-stack__meta">{{ $student['section_name'] }}</span></td>
                                        <td data-label="Actions">
                                            <div class="portal-table-actions">
                                                <x-button :href="route('admin.students.show', $student['id'])" variant="ghost" size="sm">
                                                    <i class="fas fa-eye" aria-hidden="true"></i>
                                                    <span>View</span>
                                                </x-button>

                                                <x-button :href="route('admin.students.edit', $student['id'])" variant="ghost" size="sm">
                                                    <i class="fas fa-edit" aria-hidden="true"></i>
                                                    <span>Edit</span>
                                                </x-button>

                                                <form method="POST" action="{{ route('admin.students.resend-credentials', $student['id']) }}" onsubmit="return confirm('Resend credentials to this account? This will generate a new temporary password.');">
                                                    @csrf
                                                    <x-button type="submit" variant="ghost" size="sm">
                                                        <i class="fas fa-paper-plane" aria-hidden="true"></i>
                                                        <span>Resend</span>
                                                    </x-button>
                                                </form>

                                                <form method="POST" action="{{ route('admin.students.destroy', $student['id']) }}" onsubmit="return confirm('Delete this account?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-button type="submit" variant="ghost" size="sm">
                                                        <i class="fas fa-trash" aria-hidden="true"></i>
                                                        <span>Delete</span>
                                                    </x-button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="portal-responsive-table__empty">
                                            <div class="portal-empty">
                                                <div class="portal-empty__icon">
                                                    <i class="fas fa-users" aria-hidden="true"></i>
                                                </div>
                                                <h3 class="portal-empty__title">No accounts in this department</h3>
                                                <p class="portal-empty__text">Add a student or officer record to begin organizing this department.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </section>
        @empty
            <x-card>
                <div class="portal-empty">
                    <div class="portal-empty__icon">
                        <i class="fas fa-users" aria-hidden="true"></i>
                    </div>
                    <h3 class="portal-empty__title">No student or officer records found</h3>
                    <p class="portal-empty__text">Create the first account to start assigning student and officer access.</p>
                </div>
            </x-card>
        @endforelse
    </div>
@endsection
