{{-- resources/views/admin/password-reset.blade.php --}}
@extends('layouts.app')

@section('title', 'Password Reset')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/password-reset.css') }}">
@endpush

@section('content')
<section class="password-reset">
    <h1 class="password-reset__title">Password Reset</h1>
    <form class="password-reset__search-form" method="GET" action="{{ route('admin.password-reset.index') }}">
        <input type="text" name="search" placeholder="Search student or officer by name or email" value="{{ request('search') }}">
        <button type="submit" class="btn">Search</button>
    </form>
    <div class="password-reset__table-wrap">
        <table class="password-reset__table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td data-label="Name">{{ $user->name }}</td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Role">
                        <x-badge :type="$user->role">{{ ucfirst($user->role) }}</x-badge>
                    </td>
                    <td data-label="Actions">
                        <button type="button" class="btn btn--sm btn--danger password-reset__trigger" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">Reset Password</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="password-reset__empty">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- Confirm Modal --}}
    <x-modal id="resetConfirmModal">
        <form method="POST" action="" id="resetConfirmForm">
            @csrf
            <h2 class="modal__title">Confirm Password Reset</h2>
            <p>Are you sure you want to reset the password for <span id="resetUserName"></span>?<br>This will send a new temporary password via email.</p>
            <button type="submit" class="btn btn--danger">Confirm Reset</button>
            <button type="button" class="btn modal__close">Cancel</button>
        </form>
    </x-modal>
</section>
@push('scripts')
<script src="{{ asset('js/password-reset.js') }}" defer></script>
@endpush
@endsection
