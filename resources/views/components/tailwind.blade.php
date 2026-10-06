@php
    $ui = [
        'label' => 'mb-2 block text-sm font-semibold text-slate-800',
        'trigger' => 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-right text-sm text-slate-800 shadow-sm',
        'panel' => 'rounded-xl border border-slate-200 bg-white text-slate-800 shadow-xl',
        'muted' => 'text-sm text-slate-500',
        'button' => 'rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 hover:bg-slate-50',
        'primary' => 'rounded-lg border border-indigo-600 bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700',
        'option' => 'rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm text-slate-800 hover:bg-indigo-50',
        'input' => 'w-full rounded-lg border border-slate-200 bg-white px-1 py-2 text-center text-sm text-slate-800',
        'error' => 'mt-1 text-sm text-red-600',
    ];
@endphp
@include('erfan-datepicker::components.control')
