@auth
<div class="flex items-center gap-3">
    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-400 to-purple-500 flex items-center justify-center text-sm shadow-lg flex-shrink-0">
        👤
    </div>
    <div x-show="!collapsed" class="flex-1 min-w-0 overflow-hidden">
        <p class="text-sm font-medium text-[#e4e4f0] truncate">{{ auth()->user()->name }}</p>
        <p class="text-xs text-[#6b6b80] truncate">{{ auth()->user()->email }}</p>
    </div>
    <a href="{{ route('logout') }}" 
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="text-[#6b6b80] hover:text-red-400 transition-colors"
       title="Logout">
        🚪
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>
@else
<div class="flex items-center justify-center">
    <a href="{{ route('login') }}" class="text-sm text-[#a0a0b8] hover:text-[#e4e4f0] transition-colors">
        🔐 Login to LunaOS
    </a>
</div>
@endauth
