import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createCalendar, ascii } from '../../resources/js/components/erfan-datepicker-calendar.js';
import { erfanDatepicker } from '../../resources/js/components/erfan-datepicker.js';

const config = {
    minYear: 1403, maxYear: 1405, withTime: true, timezone: 'Asia/Tehran',
    yearStarts: { 1403: '2024-03-20', 1404: '2025-03-21', 1405: '2026-03-21', 1406: '2027-03-21' },
};

test('Jalali conversion round trips every day across leap and non-leap years', () => {
    const calendar = createCalendar(config);
    for (let year = 1403; year <= 1405; year++) {
        for (let month = 1; month <= 12; month++) {
            for (let day = 1; day <= calendar.daysInMonth(year, month); day++) {
                const date = { year, month, day, hour: 14, minute: 32 };
                assert.deepEqual(calendar.parse(calendar.format(date)), date);
            }
        }
    }
    assert.equal(calendar.format({ year: 1405, month: 7, day: 5, hour: 14, minute: 32 }), '2026-09-27T14:32');
    assert.equal(calendar.daysInMonth(1403, 12), 30);
    assert.equal(calendar.daysInMonth(1404, 12), 29);
    assert.equal(calendar.weekday({ year: 1405, month: 7 }), 4);
});

test('invalid civil dates, hours and out-of-range years are rejected', () => {
    const calendar = createCalendar(config);
    for (const value of ['2026-02-30', '2026-09-27T24:00', '2026-09-27T14:60', '1900-01-01', '2026-09-27T14:32Z', 'no date']) {
        assert.equal(calendar.parse(value), null, value);
    }
    assert.equal(ascii('۱۴۰۵ ٠٥'), '1405 05');
});

test('date-only and exact minute bounds disable unavailable periods', () => {
    const calendar = createCalendar({ ...config, min: '2026-09-27T14:32', max: '2026-09-28' });
    assert.equal(calendar.allowed(calendar.parse('2026-09-27T14:31')), false);
    assert.equal(calendar.allowed(calendar.parse('2026-09-27T14:32')), true);
    assert.equal(calendar.allowed(calendar.parse('2026-09-28T23:59')), true);
    assert.equal(calendar.allowed(calendar.parse('2026-09-29T00:00')), false);
    assert.equal(calendar.intersects(1404), false);
    assert.equal(calendar.intersects(1405, 6), false);
    assert.equal(calendar.intersects(1405, 7, 5), true);
    assert.equal(calendar.format(calendar.clamp(calendar.parse('2026-09-27T00:00'))), '2026-09-27T14:32');
    assert.throws(() => createCalendar({ ...config, min: '2026-09-28', max: '2026-09-27' }));
});

test('now uses Tehran regardless of machine timezone and date-only values never shift', () => {
    const calendar = createCalendar(config);
    assert.equal(calendar.format(calendar.now(new Date('2026-09-27T21:00:00Z'))), '2026-09-28T00:30');
    assert.equal(calendar.now(new Date('2030-01-01T00:00:00Z')), null);
    const dateOnly = createCalendar({ ...config, withTime: false });
    assert.equal(dateOnly.format(dateOnly.parse('2026-09-27')), '2026-09-27');
});

test('changing month or leap year clamps the selected day and supports Persian typing', () => {
    const picker = erfanDatepicker(config);
    picker.draft = { year: 1403, month: 12, day: 30, hour: 14, minute: 32 };
    picker.setField('year', '۱۴۰۴');
    assert.equal(picker.draft.day, 29);
    picker.draft = { year: 1405, month: 6, day: 31, hour: 14, minute: 32 };
    picker.setField('month', '۷');
    assert.equal(picker.draft.day, 30);
    picker.setField('hour', '۲۴');
    assert.equal(picker.draft.hour, 14);
    assert.ok(picker.error);
    assert.equal(picker.canApply, false);
    picker.setField('hour', '۱۵');
    assert.equal(picker.canApply, true);
});

test('draft edits and mode switches do not commit or affect another instance', () => {
    const picker = erfanDatepicker({ ...config, value: '2026-09-27T14:32' });
    const second = erfanDatepicker({ ...config, value: '2026-09-28T11:00' });
    picker.focusStage = () => {};
    picker.draft = { year: 1405, month: 7, day: 5, hour: 14, minute: 32 };
    picker.setField('day', '۶');
    picker.changeMode('calendar');
    assert.equal(picker.value, '2026-09-27T14:32');
    assert.equal(picker.draft.day, 6);
    assert.equal(second.value, '2026-09-28T11:00');
    assert.equal(picker.days.filter(Boolean).length, 30);
    assert.deepEqual(picker.days.slice(0, 7), [null, null, null, null, 1, 2, 3]);
});

test('slow wheel movement is precise while rapid movement accelerates and resets on reversal or pause', () => {
    const slow = erfanDatepicker(config);
    const fast = erfanDatepicker(config);
    slow.draft.minute = 0;
    fast.draft.minute = 0;
    for (let index = 0; index < 7; index++) {
        slow.wheel('minute', { deltaY: 120, timeStamp: index * 400 });
        fast.wheel('minute', { deltaY: 120, timeStamp: index * 60 });
    }
    assert.equal(slow.draft.minute, 7);
    assert.equal(fast.draft.minute, 30);
    fast.wheel('minute', { deltaY: -120, timeStamp: 420 });
    assert.equal(fast.draft.minute, 29);
    fast.wheel('minute', { deltaY: -120, timeStamp: 1000 });
    assert.equal(fast.draft.minute, 28);
    const day = fast.draft.day;
    fast.wheel('day', { deltaY: 120, timeStamp: 1060 });
    assert.equal(fast.draft.day, day + 1);
});

test('small trackpad deltas accumulate and line-mode mouse events work', () => {
    const picker = erfanDatepicker(config);
    picker.draft.minute = 0;
    for (let index = 0; index < 24; index++) {
        picker.wheel('minute', { deltaY: 5, timeStamp: index * 8 });
    }
    assert.equal(picker.draft.minute, 1);
    picker.wheel('minute', { deltaY: 3, deltaMode: 1, timeStamp: 600 });
    assert.equal(picker.draft.minute, 2);
    picker.wheel('minute', { deltaY: 120, deltaX: 240, timeStamp: 900 });
    picker.wheel('minute', { deltaY: 120, ctrlKey: true, timeStamp: 1200 });
    assert.equal(picker.draft.minute, 2);
});

test('accelerated scrolling and large keyboard steps stop at field boundaries without invalidating the draft', () => {
    const picker = erfanDatepicker(config);
    picker.draft = { year: 1405, month: 12, day: 28, hour: 23, minute: 58 };
    picker.wheel('minute', { deltaY: 1200, timeStamp: 0 });
    assert.equal(picker.draft.minute, 59);
    picker.move('year', 10);
    assert.equal(picker.draft.year, 1405);
    picker.move('day', 5);
    assert.equal(picker.draft.day, 29);
    picker.move('month', 5);
    assert.equal(picker.draft.month, 12);
    assert.equal(picker.canApply, true);
    assert.equal(picker.error, '');
});
