@extends('layouts.admin-modern')

@section('title', 'IP Bloquées')

@section('content')
<div class="page-header">
    <h1 class="page-title">🚫 IP Bloquées</h1>
    <p class="page-subtitle">Gérez les adresses IP bloquées</p>
</div>

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif

<div class="card-base mb-6">
    <h3 class="font-semibold mb-4">Bloquer une IP</h3>
    <form action="{{ route('admin.security.blocked-ips.store') }}" method="POST" class="flex gap-4">
        @csrf
        <input type="text" name="ip_address" placeholder="Ex: 192.168.1.1" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        <input type="text" name="reason" placeholder="Raison (optionnel)" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
            <i class="fas fa-ban mr-2"></i> Bloquer
        </button>
    </form>
</div>

<div class="card-base">
    <h3 class="font-semibold mb-4">IPs actuellement bloquées</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left py-3 px-4">IP</th>
                    <th class="text-left py-3 px-4">Raison</th>
                    <th class="text-left py-3 px-4">Date de blocage</th>
                    <th class="text-right py-3 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blockedIps ?? [] as $ip)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4 font-mono">{{ $ip->ip_address }}</td>
                        <td class="py-3 px-4">{{ $ip->reason ?? '-' }}</td>
                        <td class="py-3 px-4">{{ $ip->created_at ? $ip->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3 px-4 text-right">
                            <form action="{{ route('admin.security.blocked-ips.destroy', $ip->id) }}" method="POST" onsubmit="return confirm('Débloquer cette IP ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-unlock"></i> Débloquer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-500">
                            Aucune IP bloquée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
