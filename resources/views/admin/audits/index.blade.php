@extends('layouts.app')

@section('title', 'Audit Log')
@section('header_title', 'Audit Log')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">IP Address</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Login Time</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-slate-200">
                @forelse($audits as $audit)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                            {{ $audit->user->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                            {{ $audit->ip_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-800">
                            {{ $audit->created_at->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-slate-400 text-sm">
                            Tidak ada data audit yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($audits->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $audits->links() }}
        </div>
    @endif
</div>
@endsection