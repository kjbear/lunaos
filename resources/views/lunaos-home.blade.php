<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LunaOS Workspace</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .container {
            max-width: 800px;
            padding: 40px;
            text-align: center;
        }
        h1 {
            font-size: 3em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle {
            font-size: 1.2em;
            color: #94a3b8;
            margin-bottom: 40px;
        }
        .links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 16px;
            padding: 24px;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
            display: block;
        }
        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(168, 85, 247, 0.5);
            box-shadow: 0 10px 40px rgba(168, 85, 247, 0.2);
        }
        .card h3 {
            margin: 0 0 10px 0;
            font-size: 1.4em;
        }
        .card p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.95em;
        }
        .emoji {
            font-size: 2em;
            margin-bottom: 12px;
            display: block;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            margin-top: 12px;
            background: rgba(168, 85, 247, 0.2);
            border: 1px solid rgba(168, 85, 247, 0.3);
            color: #a855f7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌙 LunaOS</h1>
        <div class="subtitle">Agent-Powered Development Pipeline</div>
        
        <div class="links">
            <a href="/kanban" class="card">
                <span class="emoji">📋</span>
                <h3>Kanban Board</h3>
                <p>View and manage development pipeline tasks</p>
                <span class="badge">NEW</span>
            </a>
            
            <a href="/mission-control-polished" class="card">
                <span class="emoji">🎯</span>
                <h3>Mission Control</h3>
                <p>System overview and agent status</p>
            </a>
            
            <a href="/projects" class="card">
                <span class="emoji">📊</span>
                <h3>Projects</h3>
                <p>Project management and tracking</p>
            </a>
            
            <a href="/workspace" class="card">
                <span class="emoji">📁</span>
                <h3>Workspace</h3>
                <p>Browse workspace files</p>
            </a>
            
            <a href="/org-chart" class="card">
                <span class="emoji">👥</span>
                <h3>Org Chart</h3>
                <p>Agent organization structure</p>
            </a>
            
            <a href="/hr/personas" class="card">
                <span class="emoji">🤖</span>
                <h3>HR - Personas</h3>
                <p>Manage AI agent personas</p>
            </a>
        </div>
    </div>
</body>
</html>
