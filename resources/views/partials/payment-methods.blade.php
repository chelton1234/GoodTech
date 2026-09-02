@php
    $methods = $paymentMethods ?? [];
    $selected = $selectedPaymentMethod ?? null;
    $minWarnings = $minWarnings ?? [];
@endphp
<div class="space-y-3">
    @foreach($methods as $value => $m)
        <label class="block cursor-pointer">
            <input type="radio" name="payment_method" value="{{ $value }}"
                class="hidden peer"
                {{ $selected ? ($selected === $value ? 'checked' : '') : ($loop->first ? 'checked' : '') }}>
            <div class="border-2 border-gray-200 peer-checked:border-brand-600 peer-checked:bg-brand-50 rounded-xl p-4 flex items-center gap-4 transition">
                <span class="text-2xl">{{ $m['icon'] ?? '💳' }}</span>
                <div class="flex-1">
                    <span class="font-medium text-sm">{{ $m['label'] }}</span>
                    <p class="text-xs text-gray-500">{!! $m['desc'] !!}</p>
                </div>
                <svg class="w-5 h-5 text-brand-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            @if(isset($minWarnings[$value]))
                <p class="text-xs text-orange-600 mt-1 ml-2">⚠ Este pedido está abaixo do mínimo para {{ $m['label'] }} ({{ $m['min'] }} MT).</p>
            @endif
        </label>
    @endforeach
</div>