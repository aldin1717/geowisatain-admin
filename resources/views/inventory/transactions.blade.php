@extends('layouts.app')

@section('title', 'Stock Transactions')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-stone-900">Stock Transactions</h1>
            <p class="mt-1 text-sm text-stone-500">Audit history for every stock movement and adjustment.</p>
        </div>
        <a href="{{ route('inventory.transactions.create') }}" class="rounded-lg bg-primary-700 px-4 py-2.5 text-sm font-medium text-white">Record Transaction</a>
    </div>

    <form method="GET" action="{{ route('inventory.transactions.index') }}" class="mb-5 flex gap-3">
        <select name="type" class="rounded-lg border border-stone-300 px-3 py-2 text-sm">
            <option value="">All transaction types</option>
            @foreach(\App\Enums\InventoryTransactionType::cases() as $type)
                <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-stone-300 bg-stone-100 px-4 py-2 text-sm">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Date</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Item</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Type</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Change</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Stock Before → After</th>
                        <th class="px-5 py-3 text-xs font-semibold uppercase text-stone-500">Recorded By / Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-5 py-4 text-stone-600 whitespace-nowrap">{{ $transaction->created_at->format('d M Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-stone-900">{{ $transaction->item->name }}</div>
                                <div class="text-xs text-stone-500">{{ $transaction->item->sku }}</div>
                            </td>
                            <td class="px-5 py-4 text-stone-600">{{ $transaction->type->label() }}</td>
                            <td class="px-5 py-4 font-medium {{ (float) $transaction->quantity < 0 || $transaction->type->value === 'stock_out' ? 'text-red-700' : 'text-emerald-700' }}">
                                @if($transaction->type->value === 'stock_out')
                                    −{{ number_format((float) $transaction->quantity, 2) }}
                                @else
                                    {{ (float) $transaction->quantity > 0 ? '+' : '' }}{{ number_format((float) $transaction->quantity, 2) }}
                                @endif
                                {{ $transaction->item->unit }}
                            </td>
                            <td class="px-5 py-4 text-stone-600">{{ number_format((float) $transaction->stock_before, 2) }} → {{ number_format((float) $transaction->stock_after, 2) }}</td>
                            <td class="px-5 py-4 text-stone-600">
                                <div>{{ $transaction->creator->name ?? 'System' }}</div>
                                @if($transaction->notes)<div class="text-xs text-stone-500">{{ $transaction->notes }}</div>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-stone-500">No stock transactions recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-5 py-4 border-t border-stone-200">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
