@extends('layouts.app')

@php
    $transactionType = \App\Enums\InventoryTransactionType::from($type);
@endphp

@section('title', $transactionType->label())

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-stone-900">{{ $transactionType->label() }}</h1>
        <p class="mt-1 text-sm text-stone-500">
            @if($type === 'adjustment')
                Enter the physical stock count; the difference will be recorded in the audit history.
            @else
                Record a stock {{ $type === 'stock_in' ? 'receipt' : 'issue' }}.
            @endif
        </p>
    </div>

    @if($items->isEmpty())
        <div class="max-w-2xl rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
            Add an inventory item before recording stock. <a href="{{ route('inventory.items.create') }}" class="font-semibold underline">Add item</a>.
        </div>
    @else
        <div class="max-w-2xl bg-white rounded-xl shadow-sm border border-stone-200 p-6">
            <form method="POST" action="{{ route('inventory.transactions.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <div>
                    <label for="item_id" class="block text-sm font-medium text-stone-700 mb-1">Item <span class="text-red-500">*</span></label>
                    <select id="item_id" name="item_id" required class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                        <option value="">Select item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" @selected(old('item_id') == $item->id)>
                                {{ $item->name }} ({{ $item->sku }}) — {{ number_format((float) $item->current_stock, 2) }} {{ $item->unit }} available
                            </option>
                        @endforeach
                    </select>
                    @error('item_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="quantity" class="block text-sm font-medium text-stone-700 mb-1">
                        {{ $type === 'adjustment' ? 'Actual counted stock' : 'Quantity' }} <span class="text-red-500">*</span>
                    </label>
                    <input id="quantity" type="number" name="quantity" value="{{ old('quantity') }}" required min="{{ $type === 'adjustment' ? '0' : '0.01' }}" step="0.01" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">
                    @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-stone-700 mb-1">Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border border-stone-300 px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-stone-200 pt-5">
                    <a href="{{ route('inventory.transactions.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm">Cancel</a>
                    <button class="rounded-lg bg-primary-700 px-5 py-2 text-sm font-medium text-white">Save Transaction</button>
                </div>
            </form>
        </div>
    @endif
@endsection
