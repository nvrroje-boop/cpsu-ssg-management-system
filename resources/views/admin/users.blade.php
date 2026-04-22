{{-- resources/views/admin/users.blade.php --}}
@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/users.css') }}">
@endpush

@section('content')
<section class="users">
    <h1 class="users__title">User Management</h1>
    <div class="users__table-wrap">
        <table class="users__table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Date Joined</th>
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
                    <td data-label="Status">
                        <x-badge :type="$user->status">{{ ucfirst($user->status) }}</x-badge>
                    </td>
                    <td data-label="Date Joined">{{ $user->created_at->format('M d, Y') }}</td>
                    <td data-label="Actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn--sm">Edit</a>
                        <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="btn btn--sm btn--danger">Deactivate</button>
                        </form>
                        <form action="{{ route('admin.users.reset', $user) }}" method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="btn btn--sm btn--secondary">Reset Password</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="users__empty">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
