import { ascii, createCalendar, months, pad, persian } from './erfan-datepicker-calendar.js';

export function erfanDatepicker(config) {
    const calendar = createCalendar(config);
    return {
        value: config.value || '',
        mode: config.mode || 'direct',
        open: false,
        step: 0,
        yearPage: config.minYear,
        draft: calendar.clamp({ year: config.minYear, month: 1, day: 1, hour: 0, minute: 0 }),
        error: '',
        inputError: false,
        valueError: '',
        months,
        weekdays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],
        weekdayNames: ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه'],
        steps: ['سال', 'ماه', 'روز'],
        fields: [{ key: 'year', label: 'سال' }, { key: 'month', label: 'ماه' }, { key: 'day', label: 'روز' }],
        timeFields: [{ key: 'hour', label: 'ساعت' }, { key: 'minute', label: 'دقیقه' }],
        wheelGesture: null,
        touchY: null,
        resetHandler: null,
        initialValue: '',
        fa: persian,

        init() {
            if (!this.value && config.defaultNow && !config.disabled && !config.readonly) {
                const now = calendar.now();
                if (calendar.allowed(now)) this.value = calendar.format(now);
            }
            this.initialValue = this.value;
            this.receiveValue();
            this.$watch('value', () => this.receiveValue());
            this.resetHandler = () => this.$nextTick(() => {
                this.value = this.initialValue;
                this.receiveValue();
                this.close(false);
            });
            this.$el.closest('form')?.addEventListener('reset', this.resetHandler);
        },

        destroy() {
            this.$el.closest('form')?.removeEventListener('reset', this.resetHandler);
        },

        receiveValue() {
            const parsed = calendar.parse(this.value);
            this.valueError = this.value && (!parsed || !calendar.allowed(parsed)) ? 'مقدار تاریخ معتبر نیست یا خارج از محدوده است.' : '';
            if (parsed && !this.valueError) {
                this.draft = { ...parsed };
                const normalized = calendar.format(parsed);
                if (normalized !== this.value) this.value = normalized;
            }
            this.$nextTick(() => this.$refs.input.setCustomValidity(this.valueError));
        },

        get summary() {
            const parsed = calendar.parse(this.value);
            return parsed && !this.valueError ? this.describe(parsed) : (this.value ? 'تاریخ نامعتبر' : 'انتخاب تاریخ');
        },

        describe(date) {
            const text = `${persian(date.day)} ${months[date.month - 1]} ${persian(date.year)}`;
            return config.withTime ? `${text}، ساعت ${persian(pad(date.hour))}:${persian(pad(date.minute))}` : text;
        },

        get draftSummary() { return this.describe(this.draft); },
        get canApply() { return !this.inputError && calendar.allowed(this.draft); },
        get canNow() { return calendar.allowed(calendar.now()); },
        get years() { return Array.from({ length: Math.min(12, config.maxYear - this.yearPage + 1) }, (_, i) => this.yearPage + i); },
        get canPrevious() { return this.yearPage > config.minYear; },
        get canNext() { return this.yearPage + 12 <= config.maxYear; },
        get days() {
            const blanks = calendar.weekday(this.draft);
            const count = calendar.daysInMonth(this.draft.year, this.draft.month);
            const cells = Math.ceil((blanks + count) / 7) * 7;
            return Array.from({ length: cells }, (_, i) => i >= blanks && i < blanks + count ? i - blanks + 1 : null);
        },

        show() {
            if (config.disabled || config.readonly) return;
            const parsed = calendar.parse(this.value);
            this.draft = parsed && calendar.allowed(parsed) ? { ...parsed } : calendar.clamp(calendar.now() ?? this.draft);
            this.error = '';
            this.wheelGesture = null;
            this.inputError = false;
            this.step = 0;
            this.yearPage = config.minYear + Math.floor((this.draft.year - config.minYear) / 12) * 12;
            this.open = true;
            this.focusStage();
        },

        close(restoreFocus = true) {
            this.open = false;
            if (restoreFocus) this.$refs.trigger.focus();
        },

        focusStage() {
            this.$nextTick(() => {
                const region = this.$refs.panel.querySelector(this.mode === 'direct' ? '[data-direct]' : `[data-step="${this.step}"]`);
                const selected = region?.querySelector('[aria-pressed="true"]:not(:disabled)');
                const fallback = region?.querySelector('input:not(:disabled), button:not(:disabled)');
                (selected ?? fallback ?? this.$refs.panel).focus();
            });
        },

        changeMode(mode) {
            this.wheelGesture = null;
            this.mode = mode;
            this.step = 0;
            this.yearPage = config.minYear + Math.floor((this.draft.year - config.minYear) / 12) * 12;
            this.focusStage();
        },

        goToStep(step) { this.step = step; this.focusStage(); },
        pageYears(direction) { this.yearPage += direction * 12; },
        yearAllowed(year) { return calendar.intersects(year); },
        monthAllowed(month) { return calendar.intersects(this.draft.year, month); },
        dayAllowed(day) { return calendar.intersects(this.draft.year, this.draft.month, day); },

        chooseYear(year) {
            if (!this.yearAllowed(year)) return;
            this.assign('year', year);
            this.goToStep(1);
        },

        chooseMonth(month) {
            if (!this.monthAllowed(month)) return;
            this.assign('month', month);
            this.goToStep(2);
        },

        chooseDay(day) {
            if (!this.dayAllowed(day)) return;
            this.draft = calendar.clamp({ ...this.draft, day });
            this.error = '';
            this.inputError = false;
        },

        limits(key) {
            return { year: [config.minYear, config.maxYear], month: [1, 12], day: [1, calendar.daysInMonth(this.draft.year, this.draft.month)], hour: [0, 23], minute: [0, 59] }[key];
        },

        displayField(key, offset = 0) {
            const value = this.draft[key] + offset;
            const [min, max] = this.limits(key);
            if (value < min || value > max) return '—';
            return key === 'month' ? months[value - 1] : persian(key === 'hour' || key === 'minute' ? pad(value) : value);
        },

        assign(key, value) {
            const next = { ...this.draft, [key]: value };
            next.day = Math.min(next.day, calendar.daysInMonth(next.year, next.month));
            this.draft = ['year', 'month', 'day'].includes(key) ? calendar.clamp(next) : next;
            this.inputError = false;
            this.error = this.canApply ? '' : 'زمان انتخاب‌شده خارج از محدودهٔ مجاز است.';
            this.animateWheel(key);
        },

        animateWheel(key) {
            if (typeof window === 'undefined' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            this.$refs?.panel.querySelectorAll(`[data-wheel="${key}"] .erfan-datepicker__neighbor`).forEach((item) => {
                item.animate([{ transform: 'translateY(.3rem)', opacity: .1 }, { transform: 'translateY(0)', opacity: .4 }], { duration: 160, easing: 'ease-out' });
            });
        },

        setField(key, value, input = null) {
            const text = ascii(value).trim();
            const number = Number(text);
            const [min, max] = this.limits(key);
            if (!/^\d+$/.test(text) || number < min || number > max) {
                this.error = `مقدار ${persian(min)} تا ${persian(max)} را وارد کنید.`;
                this.inputError = true;
            } else if ((key === 'year' && !this.yearAllowed(number)) || (key === 'month' && !this.monthAllowed(number)) || (key === 'day' && !this.dayAllowed(number))) {
                this.error = 'تاریخ انتخاب‌شده خارج از محدودهٔ مجاز است.';
                this.inputError = true;
            } else {
                this.assign(key, number);
            }
            if (input) input.value = this.displayField(key);
        },

        move(key, amount) {
            const [min, max] = this.limits(key);
            this.setField(key, Math.max(min, Math.min(max, this.draft[key] + amount)));
        },

        wheel(key, event) {
            // Pinch zoom and horizontal trackpad gestures must not edit a date.
            if (event.ctrlKey || !event.deltaY || Math.abs(event.deltaX || 0) > Math.abs(event.deltaY)) return;
            event.preventDefault?.();

            const direction = Math.sign(event.deltaY);
            const now = event.timeStamp;
            let gesture = this.wheelGesture;
            if (!gesture || gesture.key !== key || gesture.direction !== direction || now - gesture.lastAt > 240) {
                gesture = { key, direction, distance: 0, streak: 0, lastAt: now, lastStepAt: null };
                this.wheelGesture = gesture;
            }

            // Accumulate small trackpad deltas instead of discarding rapid events.
            // Three line-mode units correspond to a conventional 120px mouse notch.
            const unit = event.deltaMode === 1 ? 40 : event.deltaMode === 2 ? 800 : 1;
            gesture.distance += Math.min(Math.abs(event.deltaY) * unit, 1200);
            gesture.lastAt = now;
            const notches = Math.floor(gesture.distance / 120);
            if (!notches) return;

            gesture.distance %= 120;
            gesture.streak = gesture.lastStepAt !== null && now - gesture.lastStepAt <= 140 ? gesture.streak + 1 : 1;
            gesture.lastStepAt = now;
            const multiplier = gesture.streak >= 7 ? 10 : gesture.streak >= 4 ? 5 : gesture.streak >= 2 ? 2 : 1;
            const cap = key === 'month' ? 2 : ['year', 'minute'].includes(key) ? 10 : 5;
            this.move(key, direction * Math.min(cap, notches * multiplier));
        },

        touchStart(event) { this.touchY = event.touches[0].clientY; },
        touchEnd(key, event) {
            if (this.touchY === null) return;
            const delta = this.touchY - event.changedTouches[0].clientY;
            this.touchY = null;
            if (Math.abs(delta) > 18) this.move(key, delta > 0 ? 1 : -1);
        },

        selectNow() {
            const now = calendar.now();
            if (!calendar.allowed(now)) return;
            this.draft = now;
            this.error = '';
            this.inputError = false;
            this.yearPage = config.minYear + Math.floor((now.year - config.minYear) / 12) * 12;
            if (this.mode === 'calendar') this.goToStep(2);
        },

        commit(value) {
            if (config.disabled || config.readonly) return;
            this.value = value;
            this.valueError = '';
            this.$refs.input.setCustomValidity('');
            this.$nextTick(() => {
                this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
                this.$dispatch('erfan-datepicker:change', { name: this.$refs.input.name, value: this.value, timezone: config.timezone, withTime: config.withTime });
            });
            this.close();
        },

        apply() { if (this.canApply) this.commit(calendar.format(this.draft)); },
        clear() { this.commit(''); },
    };
}

export default function registerErfanDatepicker(Alpine) {
    Alpine.data('erfanDatepicker', erfanDatepicker);
}
