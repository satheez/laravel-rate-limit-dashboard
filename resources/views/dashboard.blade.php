@extends('rate-limit-dashboard::layout')

@section('content')
    @if (session('status'))
        <div class="flash">{{ session('status') }}</div>
    @endif

    <section class="grid stats-grid" aria-label="Summary">
        <div class="panel metric">
            <span>Total Requests</span>
            <strong>{{ number_format($stats['total_requests']) }}</strong>
        </div>
        <div class="panel metric">
            <span>Throttled Requests</span>
            <strong>{{ number_format($stats['throttled_requests']) }}</strong>
        </div>
        <div class="panel metric">
            <span>Active Limiters</span>
            <strong>{{ number_format($stats['limiters_count']) }}</strong>
        </div>
    </section>

    <section class="grid two-col" style="margin-top: 16px;">
        <div class="panel">
            <div class="panel-header">
                <strong>Hourly Volume</strong>
                <span class="mono">last {{ $summaries->count() }} windows</span>
            </div>
            <div class="panel-body">
                @if ($summaries->isNotEmpty())
                    @php($maxTotal = max(1, (int) $summaries->max('total_requests')))
                    <div class="bars" aria-label="Hourly request chart">
                        @foreach ($summaries as $summary)
                            <div
                                class="bar"
                                title="{{ $summary->window_start->format('Y-m-d H:i') }}: {{ $summary->total_requests }} request(s)"
                                style="height: {{ max(8, ((int) $summary->total_requests / $maxTotal) * 120) }}px;"
                            ></div>
                        @endforeach
                    </div>
                @else
                    <div class="empty">No summary windows have been recorded yet.</div>
                @endif
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <strong>Health Checks</strong>
                <span class="mono">{{ count($checks) }} result(s)</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Severity</th>
                            <th>Check</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($checks as $check)
                            <tr>
                                <td><span class="badge badge-{{ $check['severity'] }}">{{ $check['severity'] }}</span></td>
                                <td>
                                    <strong>{{ $check['code'] }}</strong><br>
                                    <span>{{ $check['message'] }}</span>
                                </td>
                                <td>{{ $check['action'] ?? 'No action required.' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty">No checks are enabled.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <div class="panel-header">
            <strong>Limiter Configuration</strong>
            <span class="mono">{{ $configs->count() }} configured</span>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('rate-limit-dashboard.configs.store') }}">
                @csrf
                <div class="form-grid">
                    <input name="limiter_name" placeholder="limiter name" required>
                    <input name="max_attempts" type="number" min="1" value="60" required>
                    <input name="decay_seconds" type="number" min="1" value="60" required>
                    <input name="alert_threshold" type="number" min="1" max="100" value="80" required>
                </div>
                <div style="margin-top: 10px;">
                    <textarea name="overrides" placeholder='{"ip":{"127.0.0.1":{"max_attempts":120,"decay_seconds":60}}}'></textarea>
                </div>
                <div style="margin-top: 10px;">
                    <input name="reason" placeholder="change reason">
                </div>
                <div style="margin-top: 10px; max-width: 180px;">
                    <button type="submit">Save Limiter</button>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Limiter</th>
                        <th>Max</th>
                        <th>Decay</th>
                        <th>Alert</th>
                        <th>Overrides</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($configs as $config)
                        <tr>
                            <td><strong>{{ $config->limiter_name }}</strong></td>
                            <td>{{ number_format($config->max_attempts) }}</td>
                            <td>{{ number_format($config->decay_seconds) }}s</td>
                            <td>{{ $config->alert_threshold }}%</td>
                            <td><code>{{ $config->overrides ? json_encode($config->overrides) : '-' }}</code></td>
                            <td>
                                <form method="POST" action="{{ route('rate-limit-dashboard.configs.destroy', $config->limiter_name) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="button-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">No runtime limiter configuration has been saved.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" style="margin-top: 16px;">
        <div class="panel-header">
            <strong>Limiter Activity</strong>
            <span class="mono">{{ $limiters->count() }} tracked</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Limiter</th>
                        <th>Total</th>
                        <th>Throttled</th>
                        <th>Current / Max</th>
                        <th>Utilisation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($limiters as $limiter)
                        @php($utilisation = $limiter->max_attempts > 0 ? round(($limiter->current_attempts / $limiter->max_attempts) * 100, 1) : 0)
                        <tr>
                            <td><strong>{{ $limiter->limiter_name }}</strong></td>
                            <td>{{ number_format($limiter->total_requests) }}</td>
                            <td>{{ number_format($limiter->throttled_requests) }}</td>
                            <td>{{ number_format($limiter->current_attempts) }} / {{ number_format($limiter->max_attempts) }}</td>
                            <td>{{ $utilisation }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">No limiter activity has been recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="grid two-col" style="margin-top: 16px;">
        <div class="panel">
            <div class="panel-header">
                <strong>Top Throttled IPs</strong>
                <span class="mono">top {{ $topOffenders->count() }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Throttles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topOffenders as $offender)
                            <tr>
                                <td class="mono">{{ $offender->ip_address ?: 'Unknown IP' }}</td>
                                <td>{{ number_format($offender->count) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="empty">No throttled events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header">
                <strong>Recent Activity</strong>
                <span class="mono">latest {{ $recentEvents->count() }}</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Limiter</th>
                            <th>Route</th>
                            <th>IP</th>
                            <th>Seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentEvents as $event)
                            <tr>
                                <td><span class="badge badge-{{ $event->status }}">{{ $event->status }}</span></td>
                                <td><strong>{{ $event->limiter_name }}</strong></td>
                                <td><span class="mono">{{ $event->request_method }} {{ $event->url_path }}</span></td>
                                <td class="mono">{{ $event->ip_address }}</td>
                                <td>{{ $event->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">No events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
