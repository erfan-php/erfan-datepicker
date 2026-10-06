<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>erfan-datepicker · {{ ucfirst($theme) }}</title>
    <script type="module" src="../{{ $theme }}.js"></script>
</head>
<body class="erfan-demo">
<main class="demo">
    <header class="demo-header">
        <h1 dir="ltr">erfan-datepicker</h1>
        <p>انتخاب تاریخ و ساعت شمسی · Laravel + Alpine.js · {{ ucfirst($theme) }}</p>
        <nav class="demo-nav" aria-label="پوسته‌ها">
            <a href="bootstrap.html">Bootstrap</a>
            <a href="tailwind.html">Tailwind</a>
            <a href="https://github.com/erfan-php/erfan-datepicker">GitHub</a>
        </nav>
    </header>
    <section class="demo-card" x-data="{ result: '', selected: '2026-09-27T14:32' }">
        <form @submit.prevent="result = JSON.stringify(Object.fromEntries(new FormData($el)))" @reset="result = ''">
            <x-erfan-datepicker
                name="appointment_at"
                id="demo-date"
                label="تاریخ و ساعت"
                :theme="$theme"
                value="2026-09-27T14:32"
                x-model="selected"
            />
            <p class="demo-note">تاریخ را به روش مستقیم یا در سه مرحله انتخاب کنید.</p>
            <div class="demo-actions">
                <button type="submit">بررسی مقدار فرم</button>
                <button type="reset">بازنشانی</button>
            </div>
            <output class="demo-output" dir="ltr" aria-label="مقدار انتخاب‌شده" x-text="result || selected"></output>
        </form>
    </section>
    <footer class="demo-footer">Persian interface · Gregorian form value · MIT License</footer>
</main>
</body>
</html>
