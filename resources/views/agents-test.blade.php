<!DOCTYPE html>
<html>
<head>
    <title>Agents Test</title>
    <style>
        body { font-family: sans-serif; padding: 40px; background: #1a1a2e; color: white; }
        .card { background: #2a2a40; padding: 20px; margin: 10px 0; border-radius: 8px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .agent { background: #252542; border: 2px solid #7c3aed; padding: 20px; border-radius: 12px; }
    </style>
</head>
<body>
    <h1>🤖 Agents Test Page (Pure Blade, No Livewire)</h1>
    
    <div class="card">
        <p><strong>Debug:</strong></p>
        <p>Agents in database: {{ $agents->count() }}</p>
        @if($agents->first())
        <p>First agent: {{ $agents->first()->name }}</p>
        @endif
    </div>
    
    @if($agents->count() > 0)
    <div class="grid">
        @foreach($agents as $agent)
        <div class="agent">
            <div style="font-size: 48px;">{{ $agent->emoji ?? '🤖' }}</div>
            <h2>{{ ucfirst($agent->name) }}</h2>
            <p style="color: #a0a0b8;">{{ $agent->role ?? 'No role' }}</p>
            <p>Strategy: {{ $agent->strategy_class ?? 'None' }}</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="card">
        <p>No agents found!</p>
    </div>
    @endif
</body>
</html>
