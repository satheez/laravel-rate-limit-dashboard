<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Limit Dashboard</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f6f7f9;
            --panel: #ffffff;
            --panel-muted: #f1f5f9;
            --border: #d9e0e8;
            --text: #172033;
            --muted: #667085;
            --accent: #0f766e;
            --danger: #b42318;
            --warning: #b54708;
            --info: #175cd3;
            --radius: 8px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
        }

        a {
            color: var(--info);
        }

        .shell {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px;
        }

        .topbar {
            border-bottom: 1px solid var(--border);
            background: var(--panel);
        }

        .topbar-inner {
            max-width: 1280px;
            height: 64px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .brand strong {
            font-size: 18px;
        }

        .brand span {
            color: var(--muted);
            font-size: 12px;
        }

        .grid {
            display: grid;
            gap: 16px;
        }

        .stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .two-col {
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        }

        .panel {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }

        .panel-header {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .panel-body {
            padding: 16px;
        }

        .metric {
            padding: 16px;
        }

        .metric span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .metric strong {
            display: block;
            margin-top: 8px;
            font-size: 32px;
            line-height: 1;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px 12px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            background: var(--panel-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        code, .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 12px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-hit, .badge-info {
            color: #155eef;
            background: #eff4ff;
        }

        .badge-throttled, .badge-error, .badge-critical {
            color: var(--danger);
            background: #fef3f2;
        }

        .badge-warning {
            color: var(--warning);
            background: #fffaeb;
        }

        .empty {
            padding: 24px;
            color: var(--muted);
            text-align: center;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 120px 120px 120px;
            gap: 10px;
        }

        input, textarea, button {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 6px;
            font: inherit;
        }

        input, textarea {
            padding: 9px 10px;
            background: #fff;
        }

        textarea {
            min-height: 74px;
            resize: vertical;
        }

        button {
            cursor: pointer;
            padding: 9px 12px;
            background: var(--accent);
            color: #fff;
            font-weight: 700;
        }

        .button-danger {
            background: var(--danger);
        }

        .flash {
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid #99f6e4;
            border-radius: var(--radius);
            color: #115e59;
            background: #f0fdfa;
        }

        .bars {
            display: flex;
            align-items: end;
            gap: 6px;
            min-height: 120px;
        }

        .bar {
            flex: 1;
            min-width: 8px;
            background: #99f6e4;
            border: 1px solid #5eead4;
            border-radius: 4px 4px 0 0;
        }

        @media (max-width: 900px) {
            .stats-grid,
            .two-col,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .shell,
            .topbar-inner {
                padding-left: 14px;
                padding-right: 14px;
            }

            .table-wrap {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <strong>Rate Limit Dashboard</strong>
                <span>Operational metrics, offender analysis, and limiter controls</span>
            </div>
        </div>
    </header>

    <main class="shell">
        @yield('content')
    </main>
</body>
</html>
