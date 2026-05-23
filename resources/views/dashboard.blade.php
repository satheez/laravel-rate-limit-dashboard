@extends('rate-limit-dashboard::layout')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold leading-tight text-gray-900">Dashboard Overview</h2>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Total Requests Logged</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['total_requests']) }}</dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Throttled Requests</dt>
                <dd class="mt-1 text-3xl font-semibold text-red-600">{{ number_format($stats['throttled_requests']) }}</dd>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <dt class="text-sm font-medium text-gray-500 truncate">Active Limiters</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($stats['limiters_count']) }}</dd>
            </div>
        </div>
    </div>

    <!-- Top Offenders & Recent Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Offenders -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Top Throttled IPs</h3>
            </div>
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200">
                    @forelse($topOffenders as $offender)
                    <li class="px-4 py-4 flex items-center justify-between sm:px-6">
                        <div class="text-sm font-medium text-indigo-600">{{ $offender->ip_address ?: 'Unknown IP' }}</div>
                        <div class="text-sm text-gray-500">{{ number_format($offender->count) }} throttled events</div>
                    </li>
                    @empty
                    <li class="px-4 py-4 sm:px-6 text-sm text-gray-500">No throttled events recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Events</h3>
            </div>
            <div class="border-t border-gray-200">
                <ul role="list" class="divide-y divide-gray-200 h-96 overflow-y-auto">
                    @forelse($recentEvents as $event)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium {{ $event->status === 'throttled' ? 'text-red-600' : 'text-green-600' }} truncate">
                                {{ strtoupper($event->status) }}
                            </p>
                            <div class="ml-2 flex-shrink-0 flex">
                                <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $event->limiter_name }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 sm:flex sm:justify-between">
                            <div class="sm:flex text-sm text-gray-500">
                                <p class="flex items-center">
                                    {{ $event->request_method }} {{ $event->url_path }}
                                </p>
                                <p class="mt-2 sm:mt-0 sm:ml-6 flex items-center">
                                    IP: {{ $event->ip_address }}
                                </p>
                            </div>
                            <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                <p>
                                    {{ $event->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="px-4 py-4 sm:px-6 text-sm text-gray-500">No events recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
