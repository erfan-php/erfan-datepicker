// Calendar arithmetic uses Morilog year boundaries supplied by Blade. No second
// Jalali algorithm, network request, or browser-dependent date parsing is needed.
const DAY = 86400000;
export const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
export const ascii = (value) => String(value).replace(/[۰-۹٠-٩]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.includes(digit) ? '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit) : '٠١٢٣٤٥٦٧٨٩'.indexOf(digit)));
export const pad = (value) => String(value).padStart(2, '0');
export const persian = (value) => String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]);

export function civilDay(value) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value);
    if (!match) return null;
    const [, year, month, day] = match.map(Number);
    const stamp = Date.UTC(year, month - 1, day);
    const date = new Date(stamp);
    return date.getUTCFullYear() === year && date.getUTCMonth() === month - 1 && date.getUTCDate() === day ? stamp / DAY : null;
}

export function createCalendar(config) {
    const starts = Object.fromEntries(Object.entries(config.yearStarts).map(([year, date]) => [year, civilDay(date)]));
    const first = starts[config.minYear];
    const last = starts[config.maxYear + 1];
    if (!Number.isFinite(first) || !Number.isFinite(last)) throw new Error('Missing Erfan datepicker year boundaries.');

    function daysInMonth(year, month) {
        if (month <= 6) return 31;
        if (month <= 11) return 30;
        return starts[year + 1] - starts[year] === 366 ? 30 : 29;
    }

    function ordinal(date) {
        return starts[date.year] + (date.month <= 6 ? (date.month - 1) * 31 : 186 + (date.month - 7) * 30) + date.day - 1;
    }

    function fromOrdinal(day, hour = 0, minute = 0) {
        if (day < first || day >= last) return null;
        let year = config.minYear;
        while (starts[year + 1] <= day) year++;
        const offset = day - starts[year];
        return {
            year,
            month: offset < 186 ? Math.floor(offset / 31) + 1 : Math.floor((offset - 186) / 30) + 7,
            day: offset < 186 ? offset % 31 + 1 : (offset - 186) % 30 + 1,
            hour,
            minute,
        };
    }

    function parseCivil(value, endOfDay = false) {
        const match = /^(\d{4}-\d{2}-\d{2})(?:[T ](\d{2}):(\d{2})(?::00)?)?$/.exec(ascii(value));
        if (!match) return null;
        const day = civilDay(match[1]);
        const hour = match[2] === undefined ? (endOfDay ? 23 : 0) : Number(match[2]);
        const minute = match[3] === undefined ? (endOfDay ? 59 : 0) : Number(match[3]);
        if (day === null || hour > 23 || minute > 59) return null;
        return { day, hour, minute, stamp: day * 1440 + (config.withTime ? hour * 60 + minute : 0) };
    }

    const min = config.min ? parseCivil(config.min) : null;
    const max = config.max ? parseCivil(config.max, true) : null;
    if ((config.min && !min) || (config.max && !max)) throw new Error('Invalid Erfan datepicker bounds.');
    const lower = Math.max(first * 1440, min?.stamp ?? -Infinity);
    const upper = Math.min(last * 1440 - (config.withTime ? 1 : 1440), max?.stamp ?? Infinity);
    if (lower > upper) throw new Error('Erfan datepicker bounds do not overlap the year range.');

    const stamp = (date) => ordinal(date) * 1440 + (config.withTime ? date.hour * 60 + date.minute : 0);
    const valid = (date) => date && Number.isInteger(date.year) && date.year >= config.minYear && date.year <= config.maxYear
        && Number.isInteger(date.month) && date.month >= 1 && date.month <= 12
        && Number.isInteger(date.day) && date.day >= 1 && date.day <= daysInMonth(date.year, date.month)
        && Number.isInteger(date.hour) && date.hour >= 0 && date.hour <= 23
        && Number.isInteger(date.minute) && date.minute >= 0 && date.minute <= 59;

    return {
        daysInMonth, ordinal, fromOrdinal,
        allowed: (date) => valid(date) && stamp(date) >= lower && stamp(date) <= upper,
        intersects(year, month = null, day = null) {
            const start = ordinal({ year, month: month ?? 1, day: day ?? 1 }) * 1440;
            const endMonth = month ?? 12;
            const end = ordinal({ year, month: endMonth, day: day ?? daysInMonth(year, endMonth) }) * 1440 + (config.withTime ? 1439 : 0);
            return start <= upper && end >= lower;
        },
        clamp(date) {
            const bounded = Math.max(lower, Math.min(upper, stamp(date)));
            return fromOrdinal(Math.floor(bounded / 1440), Math.floor((bounded % 1440) / 60), bounded % 60);
        },
        parse(value) {
            const parsed = parseCivil(value);
            return parsed ? fromOrdinal(parsed.day, config.withTime ? parsed.hour : 0, config.withTime ? parsed.minute : 0) : null;
        },
        format(date) {
            if (!valid(date)) return '';
            const day = new Date(ordinal(date) * DAY).toISOString().slice(0, 10);
            return config.withTime ? `${day}T${pad(date.hour)}:${pad(date.minute)}` : day;
        },
        weekday: (date) => (new Date(ordinal({ ...date, day: 1 }) * DAY).getUTCDay() + 1) % 7,
        now(now = new Date()) {
            const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
                timeZone: config.timezone, calendar: 'gregory', numberingSystem: 'latn',
                year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
            }).formatToParts(now).map(({ type, value }) => [type, value]));
            const day = civilDay(`${parts.year}-${parts.month}-${parts.day}`);
            return fromOrdinal(day, config.withTime ? Number(parts.hour) : 0, config.withTime ? Number(parts.minute) : 0);
        },
    };
}
