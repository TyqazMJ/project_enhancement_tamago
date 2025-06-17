@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Completed Risk Mitigations</h2>

    {{-- Print Button --}}
    <div class="d-flex justify-content-end mb-3">
        <button onclick="window.print()" class="btn btn-secondary">
            🖨️ Print
        </button>
    </div>

    <form method="GET" action="{{ route('admin.tasks.completed') }}" class="row g-3 mb-4">
        {{-- Risk Level Filter --}}
        <div class="col-md-3">
            <label for="filter" class="form-label">Risk Level</label>
            <select name="filter" id="filter" class="form-select">
                <option value="">All</option>
                <option value="low" {{ $filter === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ $filter === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ $filter === 'high' ? 'selected' : '' }}>High</option>
            </select>
        </div>

        {{-- Staff Filter --}}
        <div class="col-md-3">
            <label for="staff" class="form-label">Assigned Staff</label>
            <select name="staff" id="staff" class="form-select">
                <option value="">All</option>
                @foreach ($staffList as $staff)
                    <option value="{{ $staff->id }}" {{ $staffId == $staff->id ? 'selected' : '' }}>
                        {{ $staff->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Date Range Filters --}}
        <div class="col-md-2">
            <label for="date_from" class="form-label">Date From</label>
            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="form-control">
        </div>
        <div class="col-md-2">
            <label for="date_to" class="form-label">Date To</label>
            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="form-control">
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Apply</button>
        </div>
    </form>

    @if ($completedTasks->isEmpty())
        <div class="alert alert-info mt-4">No completed tasks found for the selected filters.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Risk Description</th>
                        <th>Risk Level</th>
                        <th>Risk Treatment</th>
                        <th>Assigned Staff</th>
                        <th>Solution Details</th>
                        <th>Date Assigned</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($completedTasks as $index => $mitigation)
                        @php
                            $level = (int) $mitigation->risk_level;
                            if ($level < 20) {
                                $label = 'Low';
                                $badgeClass = 'bg-success';
                            } elseif ($level < 50) {
                                $label = 'Medium';
                                $badgeClass = 'bg-warning text-dark';
                            } else {
                                $label = 'High';
                                $badgeClass = 'bg-danger';
                            }
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mitigation->risk->risk_detail ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">{{ $level }} ({{ $label }})</span>
                            </td>
                            <td class="text-capitalize">{{ $mitigation->risk_treatment }}</td>
                            <td>{{ $mitigation->assignedStaff->name ?? 'N/A' }}</td>
                            <td>{{ $mitigation->staff_solution ?? 'N/A' }}</td>
                            <td>{{ $mitigation->date_assigned ? \Carbon\Carbon::parse($mitigation->date_assigned)->format('d M Y') : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Print styles for better print view --}}
<style>
    @media print {
        /* Hide filters, buttons, nav, footer when printing */
        form,
        .btn,
        nav,
        footer,
        .d-flex.justify-content-end {
            display: none !important;
        }
        /* Simplify table for printing */
        table {
            font-size: 12pt;
            color: black !important;
            background-color: transparent !important;
            border-collapse: collapse !important;
        }
        table th, table td {
            border: 1px solid #000 !important;
        }
        table tr:hover {
            background-color: transparent !important;
        }
    }
</style>
@endsection
