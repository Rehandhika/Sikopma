<!-- Navigation -->
<div style="padding: 20px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="margin: 0; color: #495057;">SIKOPMA</h3>
            <small style="color: #6c757d;">Sistem Informasi Koperasi Mahasiswa</small>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            @if(auth()->check())
                <span style="color: #28a745; font-weight: bold;">
                    {{ auth()->user()->name }}
                </span>
                <a href="{{ route('logout') }}" 
                   style="padding: 5px 10px; background: #dc3545; color: white; text-decoration: none; border-radius: 3px; font-size: 12px;"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @else
                <span style="color: #dc3545; font-weight: bold;">Not Logged In</span>
            @endif
        </div>
    </div>
</div>

@if(auth()->check())
<div style="padding: 10px; background: white; border-bottom: 1px solid #dee2e6;">
    <a href="{{ route('dashboard') }}" 
       style="display: inline-block; padding: 8px 15px; margin-right: 10px; background: {{ request()->routeIs('dashboard') ? '#007bff' : '#6c757d' }}; color: white; text-decoration: none; border-radius: 3px; font-size: 14px;">
        Dashboard
    </a>
</div>
@endif
