@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 sm:py-6 lg:py-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 sm:mb-8">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">User Management</h1>
            <p class="text-gray-600 mt-1 text-sm sm:text-base">Manage platform users and permissions</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Total Users</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mt-1">{{ $totalUsers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Verified</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600 mt-1">{{ $verifiedUsers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Pending</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600 mt-1">{{ $pendingVerification }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <p class="text-gray-600 text-xs sm:text-sm">Suspended</p>
            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600 mt-1">{{ $suspendedUsers }}</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 sm:mb-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Search</label>
                <input type="text" id="searchInput" placeholder="Name, email, phone..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Role</label>
                <select id="roleFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Roles</option>
                    <option value="client">Client</option>
                    <option value="prestataire">Prestataire</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Status</label>
                <select id="statusFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="verified">Verified</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">Joined</label>
                <select id="dateFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Time</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                    <option value="year">Last Year</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="applyFilters()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                    Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table (Desktop) -->
    <div class="bg-white rounded-lg shadow overflow-hidden hidden sm:block">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">User</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase hidden md:table-cell">Email</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Role</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase">Status</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase hidden lg:table-cell">Joined</th>
                    <th class="px-4 lg:px-6 py-3 text-left text-xs font-semibold text-gray-900 uppercase hidden lg:table-cell">Activity</th>
                    <th class="px-4 lg:px-6 py-3 text-right text-xs font-semibold text-gray-900 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 lg:px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-600">ID: {{ $user->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm hidden md:table-cell">
                        <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-700">
                            {{ $user->email }}
                        </a>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                            {{ $user->role === 'client' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $user->role === 'prestataire' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                        ">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                            {{ $user->is_verified && !$user->is_suspended ? 'bg-green-100 text-green-800' : '' }}
                            {{ !$user->is_verified ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $user->is_suspended ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ $user->is_suspended ? 'Suspended' : ($user->is_verified ? 'Verified' : 'Pending') }}
                        </span>
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm text-gray-600 hidden lg:table-cell">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm hidden lg:table-cell">
                        @if ($user->last_login_at)
                        <p class="text-gray-600">{{ $user->last_login_at->diffForHumans() }}</p>
                        @else
                        <p class="text-gray-500 italic">Never</p>
                        @endif
                    </td>
                    <td class="px-4 lg:px-6 py-4 text-sm text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick="openUserModal('{{ $user->id }}')" class="text-blue-600 hover:text-blue-700 font-semibold">
                                View
                            </button>
                            @if (!$user->is_verified)
                            <form action="{{ route('admin.user.verify', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-700 font-semibold">
                                    Verify
                                </button>
                            </form>
                            @endif
                            @if (!$user->is_suspended)
                            <form action="{{ route('admin.user.suspend', $user) }}" method="POST" style="display: inline;"
                                onsubmit="return confirm('Suspend this user?');">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                                    Suspend
                                </button>
                            </form>
                            @else
                            <form action="{{ route('admin.user.unsuspend', $user) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-700 font-semibold">
                                    Restore
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                        <p>Aucun utilisateur trouvé correspondant à vos filtres</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Users Cards (Mobile) -->
    <div class="sm:hidden space-y-3">
        @forelse ($users as $user)
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                </div>
                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $user->role === 'client' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $user->role === 'prestataire' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : '' }}
                ">{{ ucfirst($user->role) }}</span>
            </div>
            <div class="flex items-center justify-between text-xs mb-3">
                <span class="inline-block px-2 py-0.5 rounded-full font-semibold
                    {{ $user->is_verified && !$user->is_suspended ? 'bg-green-100 text-green-800' : '' }}
                    {{ !$user->is_verified ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $user->is_suspended ? 'bg-red-100 text-red-800' : '' }}
                ">{{ $user->is_suspended ? 'Suspended' : ($user->is_verified ? 'Verified' : 'Pending') }}</span>
                <span class="text-gray-500">{{ $user->created_at->format('d M Y') }}</span>
            </div>
            <div class="flex gap-2">
                <button onclick="openUserModal('{{ $user->id }}')" class="flex-1 text-center text-blue-600 bg-blue-50 py-1.5 rounded-lg text-xs font-semibold">View</button>
                @if (!$user->is_verified)
                <form action="{{ route('admin.user.verify', $user) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full text-center text-green-600 bg-green-50 py-1.5 rounded-lg text-xs font-semibold">Verify</button>
                </form>
                @endif
                @if (!$user->is_suspended)
                <form action="{{ route('admin.user.suspend', $user) }}" method="POST" class="flex-1" onsubmit="return confirm('Suspend this user?');">
                    @csrf
                    <button type="submit" class="w-full text-center text-red-600 bg-red-50 py-1.5 rounded-lg text-xs font-semibold">Suspend</button>
                </form>
                @else
                <form action="{{ route('admin.user.unsuspend', $user) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full text-center text-green-600 bg-green-50 py-1.5 rounded-lg text-xs font-semibold">Restore</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-6 text-center text-gray-600">Aucun utilisateur trouvé correspondant à vos filtres</div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($users->hasPages())
    <div class="mt-6">
        {{ $users->links() }}
    </div>
    @endif
</div>

<!-- User Detail Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg p-8 max-w-2xl w-full max-h-screen overflow-y-auto">
        <h3 class="text-xl font-bold text-gray-900 mb-6">User Details</h3>
        <div id="userContent">
            <!-- Content loaded via JS -->
        </div>
        <button type="button" onclick="closeUserModal()" class="mt-6 w-full bg-gray-300 hover:bg-gray-400 text-gray-800 py-2 rounded-lg font-semibold">
            Close
        </button>
    </div>
</div>

<script>
    const userModal = document.getElementById('userModal');

    function openUserModal(userId) {
        userModal.classList.remove('hidden');
        fetch(`/admin/users/${userId}/details`)
            .then(r => r.text())
            .then(html => document.getElementById('userContent').innerHTML = html);
    }

    function closeUserModal() {
        userModal.classList.add('hidden');
    }

    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const role = document.getElementById('roleFilter').value;
        const status = document.getElementById('statusFilter').value;
        const date = document.getElementById('dateFilter').value;
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (role) params.append('role', role);
        if (status) params.append('status', status);
        if (date) params.append('date', date);
        
        window.location.href = `{{ route('admin.users') }}?${params.toString()}`;
    }
</script>
@endsection
