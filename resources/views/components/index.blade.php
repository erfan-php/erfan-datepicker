<div
    {{ $attributes->class(['erfan-datepicker', 'erfan-datepicker--'.$theme]) }}
    dir="rtl"
    x-data="erfanDatepicker(@js($config))"
    x-modelable="value"
    @keydown.escape.stop.prevent="if (open) close()"
    @click.outside="if (open) close(false)"
    @focusout="setTimeout(() => { if (!$el.contains(document.activeElement)) close(false) }, 0)"
>
    @include('erfan-datepicker::components.'.$theme)
</div>
