{{-- resources/views/admin/officers.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Officers')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/officers.css') }}">
@endpush

@section('content')
<section class="officers">
    <h1 class="officers__title">Manage Officers</h1>
    <button class="btn officers__add-btn" id="addOfficerBtn">Add Officer</button>
    <div class="officers__table-wrap">
        <table class="officers__table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($officers as $officer)
                <tr>
                    <td data-label="Name">{{ $officer->name }}</td>
                    <td data-label="Email">{{ $officer->email }}</td>
                    <td data-label="Position">
                        <x-badge :type="$officer->position">{{ $officer->position }}</x-badge>
                    </td>
                    <td data-label="Role">
                        <x-badge :type="$officer->role">{{ ucfirst($officer->role) }}</x-badge>
                    </td>
                    <td data-label="Actions">
                        <a href="{{ route('admin.officers.edit', $officer) }}" class="btn btn--sm">Edit</a>
                        <form action="{{ route('admin.officers.deactivate', $officer) }}" method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="btn btn--sm btn--danger">Deactivate</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="officers__empty">No officers found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Add Officer Modal --}}
    <x-modal id="addOfficerModal">
        <form method="POST" action="{{ route('admin.officers.store') }}" class="officers__form">
            @csrf
            <h2 class="modal__title">Add Officer</h2>
            <label for="officer_name">Name</label>
            <input type="text" id="officer_name" name="name" required>
            @error('name')<div class="form__error">{{ $message }}</div>@enderror
            <label for="officer_email">Email</label>
            <input type="email" id="officer_email" name="email" required>
            @error('email')<div class="form__error">{{ $message }}</div>@enderror
            <label for="officer_position">Position</label>
            <input type="text" id="officer_position" name="position" required>
            @error('position')<div class="form__error">{{ $message }}</div>@enderror
            <button type="submit" class="btn">Add Officer</button>
        </form>
    </x-modal>
</section>
@endpush
@push('scripts')
<script src="{{ asset('js/officers.js') }}" defer></script>
@endpush
@endsection
