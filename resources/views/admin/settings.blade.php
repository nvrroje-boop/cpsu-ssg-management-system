{{-- resources/views/admin/settings.blade.php --}}
@extends('layouts.app')

@section('title', 'System Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
@endpush

@section('content')
<section class="settings">
    <h1 class="settings__title">System Settings</h1>
    <div class="settings__layout">
        <aside class="settings__sidebar">
            <ul>
                <li class="settings__tab @if($tab=='general') settings__tab--active @endif"><a href="?tab=general">General</a></li>
                <li class="settings__tab @if($tab=='branding') settings__tab--active @endif"><a href="?tab=branding">Branding</a></li>
                <li class="settings__tab @if($tab=='contact') settings__tab--active @endif"><a href="?tab=contact">Contact</a></li>
                <li class="settings__tab @if($tab=='academic') settings__tab--active @endif"><a href="?tab=academic">Academic Year</a></li>
            </ul>
        </aside>
        <div class="settings__content">
            @if($tab=='general')
            <form method="POST" action="{{ route('admin.settings.update', 'general') }}" class="settings__form">
                @csrf
                <label for="site_name">Site Name</label>
                <input type="text" id="site_name" name="site_name" value="{{ $settings['site_name'] ?? '' }}">
                <button type="submit" class="btn">Save</button>
            </form>
            @elseif($tab=='branding')
            <form method="POST" action="{{ route('admin.settings.update', 'branding') }}" class="settings__form" enctype="multipart/form-data">
                @csrf
                <label for="logo">Logo Upload</label>
                <input type="file" id="logo" name="logo">
                <button type="submit" class="btn">Save</button>
            </form>
            @elseif($tab=='contact')
            <form method="POST" action="{{ route('admin.settings.update', 'contact') }}" class="settings__form">
                @csrf
                <label for="contact_email">Contact Email</label>
                <input type="email" id="contact_email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">
                <button type="submit" class="btn">Save</button>
            </form>
            @elseif($tab=='academic')
            <form method="POST" action="{{ route('admin.settings.update', 'academic') }}" class="settings__form">
                @csrf
                <label for="academic_year">Academic Year</label>
                <input type="text" id="academic_year" name="academic_year" value="{{ $settings['academic_year'] ?? '' }}">
                <button type="submit" class="btn">Save</button>
            </form>
            @endif
        </div>
    </div>
</section>
@endsection
