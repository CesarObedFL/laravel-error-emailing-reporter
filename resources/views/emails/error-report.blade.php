<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error Report</title>
    <style>
        body { font-family: -apple-system, Arial, sans-serif; color: #222; line-height: 1.5; }
        h2 { border-bottom: 2px solid #e74c3c; padding-bottom: 4px; }
        h3 { margin-top: 24px; color: #c0392b; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; font-size: 13px; vertical-align: top; }
        th { background: #f5f5f5; width: 180px; }
        pre { background: #f8f8f8; padding: 10px; overflow-x: auto; font-size: 12px; border-left: 3px solid #e74c3c; }
        .badge { display: inline-block; background: #e74c3c; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <h2>⚠️ Error Report: {{ $context['project']['name'] ?? 'App' }}</h2>

    <h3>Project</h3>
    <table>
        <tr><th>Name</th><td>{{ $context['project']['name'] ?? '-' }}</td></tr>
        <tr><th>Version</th><td>{{ $context['project']['version'] ?? '-' }}</td></tr>
        <tr><th>URL</th><td>{{ $context['project']['url'] ?? '-' }}</td></tr>
        <tr><th>Environment</th><td>{{ $context['environment'] ?? '-' }}</td></tr>
        <tr><th>Timestamp</th><td>{{ $context['timestamp'] ?? '-' }}</td></tr>
        <tr><th>PHP</th><td>{{ $context['php'] ?? '-' }}</td></tr>
        <tr><th>Laravel</th><td>{{ $context['laravel'] ?? '-' }}</td></tr>
    </table>

    <h3>Exception</h3>
    <table>
        <tr><th>Class</th><td><span class="badge">{{ $context['exception']['class'] ?? '-' }}</span></td></tr>
        <tr><th>Message</th><td>{{ $context['exception']['message'] ?? '-' }}</td></tr>
        <tr><th>Code</th><td>{{ $context['exception']['code'] ?? '-' }}</td></tr>
        <tr><th>File</th><td>{{ $context['exception']['file'] ?? '-' }}</td></tr>
        <tr><th>Line</th><td>{{ $context['exception']['line'] ?? '-' }}</td></tr>
        @if(!empty($context['exception']['previous']))
            <tr><th>Previous</th><td>{{ $context['exception']['previous'] }}</td></tr>
        @endif
    </table>

    <h3>Stack Trace</h3>
    <pre>{{ $context['exception']['trace'] ?? '-' }}</pre>

    @if(!empty($context['request']))
        <h3>Request</h3>
        <table>
            <tr><th>URL</th><td>{{ $context['request']['url'] ?? '-' }}</td></tr>
            <tr><th>Method</th><td>{{ $context['request']['method'] ?? '-' }}</td></tr>
            <tr><th>IP</th><td>{{ $context['request']['ip'] ?? '-' }}</td></tr>
        </table>

        @if(!empty($context['request']['input']))
            <h4>Input</h4>
            <pre>{{ json_encode($context['request']['input'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif

        @if(!empty($context['request']['headers']))
            <h4>Headers</h4>
            <pre>{{ json_encode($context['request']['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @endif
    @endif

    @if(!empty($context['user']))
        <h3>User</h3>
        <table>
            <tr><th>ID</th><td>{{ $context['user']['id'] ?? '-' }}</td></tr>
            <tr><th>Email</th><td>{{ $context['user']['email'] ?? '-' }}</td></tr>
        </table>
    @endif
</body>
</html>