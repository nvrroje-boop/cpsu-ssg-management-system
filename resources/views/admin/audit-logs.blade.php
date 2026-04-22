{{-- resources/views/admin/audit-logs.blade.php --}}
@extends('layouts.app')

@section('title', 'Audit Logs')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/audit-logs.css') }}">
@endpush

@section('content')
<section class="audit-logs">
    <h1 class="audit-logs__title">Audit Logs</h1>
    <form class="audit-logs__filter-form" method="GET" action="{{ route('admin.audit-logs.index') }}">
        <label for="date_from">From:</label>
        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
        <label for="date_to">To:</label>
        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
        <label for="action_type">Action:</label>
        <select name="action_type" id="action_type">
            <option value="">All</option>
            @foreach($actionTypes as $type)
                <option value="{{ $type }}" @selected(request('action_type')==$type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn">Filter</button>
    </form>
    <div class="audit-logs__table-wrap">
        <table class="audit-logs__table">
            <thead>
                <tr>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td data-label="Actor">{{ $log->actor }}</td>
                    <td data-label="Action">{{ $log->action }}</td>
                    <td data-label="Target">{{ $log->target }}</td>
                    <td data-label="Timestamp">{{ $log->created_at->format('M d, Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="audit-logs__empty">No logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="audit-logs__pagination">
        {{ $logs->links() }}
    </div>
</section>
@endsection
