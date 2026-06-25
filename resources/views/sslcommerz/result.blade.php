<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body {
                margin: 0;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                background: #f7fafc;
                color: #1a202c;
            }
            .page {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            .card {
                width: 100%;
                max-width: 520px;
                background: #ffffff;
                border-radius: 1rem;
                box-shadow: 0 18px 50px rgba(15, 23, 42, 0.12);
                padding: 2.5rem;
            }
            .badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.75rem 1rem;
                border-radius: 9999px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                font-size: 0.75rem;
            }
            .badge-success { background: #daf6e2; color: #0f5132; }
            .badge-warning { background: #fff4ce; color: #664d03; }
            .badge-error { background: #ffe4e6; color: #9f1239; }
            .title { font-size: 2rem; font-weight: 700; margin: 1.5rem 0 0.75rem; }
            .message { font-size: 1rem; line-height: 1.75; color: #4a5568; margin-bottom: 1.75rem; }
            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.85rem 1.5rem;
                border-radius: 0.75rem;
                background: #2563eb;
                color: white;
                text-decoration: none;
                font-weight: 600;
                transition: background 0.2s ease;
            }
            .button:hover { background: #1d4ed8; }
            .button-secondary {
                background: transparent;
                color: #374151;
                border: 1px solid #d1d5db;
                margin-left: 0.75rem;
            }
            .button-secondary:hover { background: #f3f4f6; }
            .actions { margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 0.75rem; }
        </style>
    @endif
</head>
<body>
    <main class="page">
        <section class="card">
            <div class="badge badge-{{ $status === 'success' ? 'success' : ($status === 'warning' ? 'warning' : 'error') }}">
                {{ ucfirst($status) }}
            </div>
            <h1 class="title">{{ $title }}</h1>
            <p class="message">{{ $message }}</p>
            <div class="actions">
                <a href="{{ $buttonUrl }}" class="button">{{ $buttonText }}</a>
                <a href="{{ url('/dashboard') }}" class="button button-secondary">View Dashboard</a>
            </div>
        </section>
    </main>
</body>
</html>
