@extends('rate-limit-dashboard::layout')

@section('content')
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Dashboard Overview</h2>
            <p class="mt-2 text-sm text-slate-500 font-medium">Real-time metrics and rate limit monitoring</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3 mb-10">
        <!-- Total Requests -->
        <div class="glass-panel rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 overflow-hidden relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="px-6 py-6 relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Total Requests Logged</p>
                        <p class="text-4xl font-bold text-slate-900">{{ number_format($stats['total_requests']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform duration-300 shadow-sm shadow-indigo-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Throttled Requests -->
        <div class="glass-panel rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 overflow-hidden relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-rose-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="px-6 py-6 relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Throttled Requests</p>
                        <p class="text-4xl font-bold text-red-600">{{ number_format($stats['throttled_requests']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-600 group-hover:scale-110 transition-transform duration-300 shadow-sm shadow-red-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Limiters -->
        <div class="glass-panel rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 overflow-hidden relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="px-6 py-6 relative z-10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500 mb-1">Active Limiters</p>
                        <p class="text-4xl font-bold text-slate-900">{{ number_format($stats['limiters_count']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform duration-300 shadow-sm shadow-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Offenders & Recent Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Offenders -->
        <div class="glass-panel rounded-2xl shadow-sm overflow-hidden flex flex-col h-[500px]">
            <div class="px-6 py-5 border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <h3 class="text-lg font-semibold text-slate-900 tracking-tight">Top Throttled IPs</h3>
                </div>
            </div>
            <div class="overflow-y-auto flex-1 p-3">
                <ul role="list" class="space-y-2">
                    @forelse($topOffenders as $offender)
                    <li class="px-4 py-3 bg-white/60 hover:bg-white rounded-xl transition-all duration-200 flex items-center justify-between group border border-transparent hover:border-indigo-100 shadow-sm hover:shadow">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs shadow-sm shadow-indigo-100/50 group-hover:scale-105 transition-transform">
                                {{ $loop->iteration }}
                            </div>
                            <div class="text-sm font-semibold text-slate-800">{{ $offender->ip_address ?: 'Unknown IP' }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/10">
                                {{ number_format($offender->count) }} throttles
                            </span>
                        </div>
                    </li>
                    @empty
                    <div class="flex flex-col items-center justify-center h-full text-slate-400">
                        <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm">No throttled events recorded yet.</p>
                    </div>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="glass-panel rounded-2xl shadow-sm overflow-hidden flex flex-col h-[500px]">
            <div class="px-6 py-5 border-b border-slate-100 bg-white/50 backdrop-blur-sm z-10">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-lg font-semibold text-slate-900 tracking-tight">Recent Activity</h3>
                </div>
            </div>
            <div class="overflow-y-auto flex-1 p-5">
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @forelse($recentEvents as $event)
                        <li>
                            <div class="relative pb-8 hover:bg-slate-50/50 transition-colors duration-200 -mx-3 px-3 rounded-xl group">
                                @if (!$loop->last)
                                    <span class="absolute top-4 left-7 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3 pt-2">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-transparent {{ $event->status === 'throttled' ? 'bg-red-100 text-red-500 shadow-sm shadow-red-200 group-hover:scale-110 transition-transform' : 'bg-green-100 text-green-500 shadow-sm shadow-green-200 group-hover:scale-110 transition-transform' }}">
                                            @if($event->status === 'throttled')
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1 flex justify-between space-x-4">
                                        <div>
                                            <p class="text-sm text-slate-600 font-medium flex items-center flex-wrap gap-2">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $event->status === 'throttled' ? 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-600/20' : 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' }}">
                                                    {{ $event->status }}
                                                </span>
                                                <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">{{ $event->limiter_name }}</span>
                                            </p>
                                            <div class="mt-1.5 text-sm text-slate-500 flex items-center gap-2">
                                                <span class="font-mono text-[11px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 border border-slate-200">{{ $event->request_method }}</span>
                                                <span class="truncate font-medium text-slate-700">{{ $event->url_path }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right text-xs whitespace-nowrap text-slate-500 flex flex-col items-end">
                                            <time datetime="{{ $event->created_at }}" class="font-medium text-slate-600">{{ $event->created_at->diffForHumans() }}</time>
                                            <div class="mt-1.5 px-2 py-0.5 bg-slate-100 rounded-md text-[11px] font-mono text-slate-500 border border-slate-200">
                                                {{ $event->ip_address }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @empty
                        <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p class="text-sm">No events recorded yet.</p>
                        </div>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
