@php
    $errorKey = trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $serverError = $errorMessage ?? (isset($errors) ? $errors->first($errorKey) : '');
@endphp
<label id="{{ $inputId }}-label" for="{{ $inputId }}-trigger" class="{{ $ui['label'] }}">
    {{ $label }}
    @if($required)
        <span aria-label="الزامی">*</span>
    @endif
</label>
{{-- A visually hidden text input preserves native required/form validity; type=hidden cannot. --}}
<input
    id="{{ $inputId }}"
    x-ref="input"
    class="erfan-datepicker__form-input"
    type="text"
    name="{{ $name }}"
    value="{{ $config['value'] }}"
    x-model="value"
    tabindex="-1"
    autocomplete="off"
    aria-labelledby="{{ $inputId }}-label"
    aria-describedby="{{ $inputId }}-error {{ $inputId }}-server-error"
    @required($required && ! $readonly)
    @disabled($disabled)
    @readonly($readonly)
    @invalid.prevent="show(); error = valueError || 'لطفاً تاریخ را انتخاب و تأیید کنید.'"
>
<button
    id="{{ $inputId }}-trigger"
    x-ref="trigger"
    type="button"
    class="erfan-datepicker__trigger {{ $ui['trigger'] }}"
    aria-haspopup="dialog"
    aria-controls="{{ $inputId }}-panel"
    aria-labelledby="{{ $inputId }}-label {{ $inputId }}-summary"
    :aria-expanded="open"
    :aria-invalid="Boolean(valueError) || @js((bool) $serverError)"
    aria-describedby="{{ $inputId }}-error {{ $inputId }}-server-error"
    @disabled($disabled || $readonly)
    @click="open ? close() : show()"
>
    <span id="{{ $inputId }}-summary" x-text="summary">انتخاب تاریخ</span>
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 5h3"/></svg>
</button>
<p id="{{ $inputId }}-error" x-cloak x-show="valueError && !open" x-text="valueError" class="{{ $ui['error'] }}" role="alert"></p>
<p id="{{ $inputId }}-server-error" class="{{ $ui['error'] }}">{{ $serverError }}</p>

<section
    id="{{ $inputId }}-panel"
    x-ref="panel"
    x-cloak
    x-show="open"
    x-transition.opacity.duration.150ms
    role="dialog"
    aria-modal="false"
    aria-labelledby="{{ $inputId }}-label"
    tabindex="-1"
    class="erfan-datepicker__panel {{ $ui['panel'] }}"
    :class="{ 'erfan-datepicker__panel--days': mode === 'calendar' && step === 2 }"
>
    <div class="erfan-datepicker__heading">
        <strong>{{ $label }}</strong>
        <button type="button" class="{{ $ui['button'] }}" aria-label="بستن انتخاب تاریخ" @click="close()">×</button>
    </div>
    <div class="erfan-datepicker__modes" role="group" aria-label="روش انتخاب تاریخ">
        <button type="button" class="{{ $ui['button'] }}" :aria-pressed="mode === 'direct'" @click="changeMode('direct')">انتخاب مستقیم</button>
        <button type="button" class="{{ $ui['button'] }}" :aria-pressed="mode === 'calendar'" @click="changeMode('calendar')">تقویم مرحله‌ای</button>
    </div>

    <div data-direct x-show="mode === 'direct'" class="erfan-datepicker__direct" x-transition.opacity.duration.150ms>
        <template x-for="field in fields" :key="field.key">
            <div class="erfan-datepicker__wheel" :data-wheel="field.key" @wheel="wheel(field.key, $event)" @touchstart.passive="touchStart($event)" @touchend="touchEnd(field.key, $event)">
                <span class="erfan-datepicker__caption {{ $ui['muted'] }}" x-text="field.label"></span>
                <button type="button" class="erfan-datepicker__arrow" :aria-label="'کاهش ' + field.label" :disabled="draft[field.key] <= limits(field.key)[0]" @click="move(field.key, -1)">⌃</button>
                <span class="erfan-datepicker__neighbor" aria-hidden="true" x-text="displayField(field.key, -1)"></span>
                <template x-if="field.key !== 'month'">
                    <input
                        type="text"
                        inputmode="numeric"
                        autocomplete="off"
                        class="erfan-datepicker__selected {{ $ui['input'] }}"
                        role="spinbutton"
                        :aria-label="field.label"
                        :aria-valuemin="limits(field.key)[0]"
                        :aria-valuemax="limits(field.key)[1]"
                        :aria-valuenow="draft[field.key]"
                        :value="displayField(field.key)"
                        @focus="$el.select()"
                        @blur="setField(field.key, $el.value, $el)"
                        @keydown.enter.prevent="setField(field.key, $el.value, $el)"
                        @keydown.up.prevent="move(field.key, 1)"
                        @keydown.down.prevent="move(field.key, -1)"
                        @keydown.page-up.prevent="move(field.key, field.key === 'year' ? 10 : 5)"
                        @keydown.page-down.prevent="move(field.key, field.key === 'year' ? -10 : -5)"
                    >
                </template>
                <template x-if="field.key === 'month'">
                    <button type="button" class="erfan-datepicker__selected {{ $ui['input'] }}" :aria-label="'انتخاب ماه، ' + displayField('month')" @click="changeMode('calendar'); goToStep(1)" @keydown.up.prevent="move('month', 1)" @keydown.down.prevent="move('month', -1)" x-text="displayField('month')"></button>
                </template>
                <span class="erfan-datepicker__neighbor" aria-hidden="true" x-text="displayField(field.key, 1)"></span>
                <button type="button" class="erfan-datepicker__arrow" :aria-label="'افزایش ' + field.label" :disabled="draft[field.key] >= limits(field.key)[1]" @click="move(field.key, 1)">⌄</button>
            </div>
        </template>
        @if($withTime)
            <div class="erfan-datepicker__time-wheel">
                <span class="erfan-datepicker__caption {{ $ui['muted'] }}">ساعت</span>
                <div class="erfan-datepicker__clock" dir="ltr">
                    @include('erfan-datepicker::components.time', ['wheelTime' => true])
                </div>
            </div>
        @endif
    </div>

    <p x-show="mode === 'direct'" class="{{ $ui['muted'] }} erfan-datepicker__entry-hint">
        برای انتخاب سریع، روی عدد بزنید و تایپ کنید؛ اسکرول سریع هم با گام‌های بزرگ‌تر حرکت می‌کند.
    </p>

    <div x-show="mode === 'calendar'" x-transition.opacity.duration.150ms>
        <nav class="erfan-datepicker__steps" aria-label="مراحل انتخاب تاریخ">
            <template x-for="(title, index) in steps" :key="title">
                <button type="button" class="{{ $ui['button'] }}" :aria-current="step === index ? 'step' : null" @click="goToStep(index)" x-text="title"></button>
            </template>
        </nav>
        <div data-step="0" x-show="step === 0" x-transition.opacity.duration.150ms class="erfan-datepicker__stage">
            <div class="erfan-datepicker__heading">
                <button type="button" class="{{ $ui['button'] }}" aria-label="سال‌های قبل" :disabled="!canPrevious" @click="pageYears(-1)">→</button>
                <span x-text="fa(yearPage) + ' تا ' + fa(years[years.length - 1])"></span>
                <button type="button" class="{{ $ui['button'] }}" aria-label="سال‌های بعد" :disabled="!canNext" @click="pageYears(1)">←</button>
            </div>
            <div class="erfan-datepicker__grid erfan-datepicker__grid--months">
                <template x-for="year in years" :key="year">
                    <button
                        type="button"
                        class="{{ $ui['option'] }}"
                        :aria-pressed="draft.year === year"
                        :disabled="!yearAllowed(year)"
                        @click="chooseYear(year)"
                        x-text="fa(year)"
                    ></button>
                </template>
            </div>
        </div>
        <div data-step="1" x-show="step === 1" x-transition.opacity.duration.150ms class="erfan-datepicker__stage">
            <p class="erfan-datepicker__stage-title" x-text="'انتخاب ماه سال ' + fa(draft.year)"></p>
            <div class="erfan-datepicker__grid erfan-datepicker__grid--months">
                <template x-for="(month, index) in months" :key="month">
                    <button
                        type="button"
                        class="{{ $ui['option'] }}"
                        :aria-pressed="draft.month === index + 1"
                        :disabled="!monthAllowed(index + 1)"
                        @click="chooseMonth(index + 1)"
                        x-text="month"
                    ></button>
                </template>
            </div>
        </div>
        <div data-step="2" x-show="step === 2" x-transition.opacity.duration.150ms class="erfan-datepicker__stage">
            <p class="erfan-datepicker__stage-title" x-text="months[draft.month - 1] + ' ' + fa(draft.year)"></p>
            <div class="erfan-datepicker__grid erfan-datepicker__grid--days">
                <template x-for="(day, index) in weekdays" :key="index">
                    <abbr class="erfan-datepicker__weekday" :title="weekdayNames[index]" x-text="day"></abbr>
                </template>
                <template x-for="(day, index) in days" :key="index">
                    <div>
                        <template x-if="day !== null">
                            <button
                                type="button"
                                class="erfan-datepicker__day {{ $ui['option'] }}"
                                :aria-label="fa(day) + ' ' + months[draft.month - 1] + ' ' + fa(draft.year)"
                                :aria-pressed="draft.day === day"
                                :disabled="!dayAllowed(day)"
                                @click="chooseDay(day)"
                                x-text="fa(day)"
                            ></button>
                        </template>
                    </div>
                </template>
            </div>
            @if($withTime)
                <div class="erfan-datepicker__calendar-time">
                    <span class="{{ $ui['muted'] }}">ساعت و دقیقه</span>
                    <div class="erfan-datepicker__clock" dir="ltr">
                        @include('erfan-datepicker::components.time', ['wheelTime' => false])
                    </div>
                </div>
            @endif
        </div>
    </div>

    <p class="erfan-datepicker__summary" x-text="draftSummary" aria-live="polite" aria-atomic="true"></p>
    <p x-show="error" x-text="error" class="{{ $ui['error'] }}" role="alert"></p>
    <div class="erfan-datepicker__footer">
        <button type="button" class="{{ $ui['button'] }}" :disabled="!canNow" @click="selectNow()">اکنون</button>
        @if($clearable && ! $required)
            <button type="button" class="{{ $ui['button'] }}" @click="clear()">پاک کردن</button>
        @endif
        <span class="erfan-datepicker__spacer"></span>
        <button type="button" class="{{ $ui['button'] }}" @click="close()">انصراف</button>
        <button type="button" class="{{ $ui['primary'] }}" :disabled="!canApply" @click="apply()">تأیید</button>
    </div>
</section>
