# erfan-datepicker

تقویم شمسی و انتخاب‌گر تاریخ و ساعت برای **Laravel، Blade و Alpine.js** با دو پوستهٔ **Bootstrap 5** و **Tailwind CSS**.

[English documentation](README.md) · [دریافت نسخه‌ها](https://github.com/erfan-php/erfan-datepicker/releases) · [گزارش مشکل](https://github.com/erfan-php/erfan-datepicker/issues) · [مجوز MIT](LICENSE)

اگر تاریخ را می‌دانید، سال، ماه، روز و ساعت را مستقیم انتخاب یا تایپ کنید. برای پیدا کردن تاریخ از تقویم سه‌مرحله‌ای **سال ← ماه ← روز** استفاده کنید. هنگام ویرایش می‌توانید بین این دو حالت جابه‌جا شوید؛ انتخاب جاری حفظ می‌شود.

## راهنمای تصویری

| انتخاب مستقیم | تقویم مرحله‌ای؛ انتخاب ماه |
| --- | --- |
| ![انتخاب مستقیم تاریخ و ساعت](docs/images/illustrations/01-direct.png) | ![مرحلهٔ انتخاب ماه](docs/images/illustrations/02-months.png) |

| تقویم مرحله‌ای؛ انتخاب روز | انتخاب روز با تم نارنجی |
| --- | --- |
| ![مرحلهٔ انتخاب روز](docs/images/illustrations/03-days.png) | ![انتخاب روز با تم نارنجی](docs/images/illustrations/04-orange-days.png) |

تصاویر طراحی‌شده‌اند؛ فونت و رنگ به استایل پروژه بستگی دارد. نارنجی نمونهٔ تم سفارشی است.

رابط تقویم فارسی و راست‌به‌چپ است.

## پیش‌نیازها

PHP 8.3+، Laravel 12/13، Alpine.js 3 و Bootstrap 5 یا Tailwind CSS 3/4. Alpine و CSS فریم‌ورک باید در پروژه بارگذاری شده باشند. Composer کتابخانهٔ تبدیل تاریخ `morilog/jalali` را نصب می‌کند.

## نصب

### ۱. نصب از گیت‌هاب

در پوشهٔ پروژهٔ لاراول اجرا کنید:

```sh
composer config repositories.erfan-datepicker vcs https://github.com/erfan-php/erfan-datepicker
composer require "erfan-php/erfan-datepicker:^1.0"
php artisan vendor:publish --tag=erfan-datepicker-assets
```

کامپوننت خودکار ثبت می‌شود. اگر package discovery غیرفعال است، `Erfan\Datepicker\ErfanDatepickerServiceProvider` را در `bootstrap/providers.php` ثبت کنید.

### ۲. اتصال به Alpine موجود

در `resources/js/app.js` فایل‌های منتشرشده را import کنید و کامپوننت را **پیش از `Alpine.start()` موجود در برنامه** ثبت کنید:

```js
import Alpine from 'alpinejs';
import registerErfanDatepicker from './vendor/erfan-datepicker/components/erfan-datepicker.js';
import '../css/vendor/erfan-datepicker/erfan-datepicker.css';

window.Alpine = Alpine;
registerErfanDatepicker(Alpine);
Alpine.start();
```

اگر Alpine از قبل راه‌اندازی شده، importها و ثبت کامپوننت را به همان بخش اضافه کنید؛ `Alpine.start()` باید یک بار اجرا شود.

### ۳. تنظیم پوسته

**Bootstrap:** فایل CSS مربوط به Bootstrap 5 را مانند قبل در پروژه بارگذاری کنید و `theme="bootstrap"` بدهید. تقویم به جاوااسکریپت Bootstrap یا قالب Vuexy نیاز ندارد.

**Tailwind 4:** مسیر قالب‌های پکیج را به منابع اسکن اضافه کنید. برای فایل `resources/css/app.css`:

```css
@import "tailwindcss";
@source "../../vendor/erfan-php/erfan-datepicker/resources/views";
```

**Tailwind 3:** مسیر قالب‌های پکیج را به آرایهٔ `content` موجود در `tailwind.config.js` اضافه کنید:

```js
'./vendor/erfan-php/erfan-datepicker/resources/views/**/*.blade.php',
```

فایل‌ها را build کنید و ورودی‌های JS/CSS را با `@vite` در layout بارگذاری کنید:

```sh
npm run build
```

## نمونهٔ استفاده

### تاریخ و ساعت با Bootstrap

```blade
<x-erfan-datepicker
    name="scheduled_at"
    label="زمان برنامه‌ریزی"
    theme="bootstrap"
    :value="$scheduledAt ?? null"
    :default-now="false"
/>
```

### تاریخ تولد با Tailwind، بدون ساعت

```blade
<x-erfan-datepicker
    name="birth_date"
    label="تاریخ تولد"
    theme="tailwind"
    mode="calendar"
    :with-time="false"
    :default-now="false"
    :max="now('Asia/Tehran')->format('Y-m-d')"
    required
/>
```

برای مقدار بولی از `:` استفاده کنید: `:with-time="false"`. برای فیلد خالی، `:default-now="false"` بگذارید.

## فرمت مقدار و منطقهٔ زمانی

**تاریخ نمایشی شمسی است، اما مقدار ارسالی فرم میلادی با ارقام انگلیسی است.**

| حالت | فرمت ارسالی | نمونه |
| --- | --- | --- |
| تاریخ و ساعت | `YYYY-MM-DDTHH:mm` | `2026-09-27T14:32` |
| فقط تاریخ | `YYYY-MM-DD` | `2026-09-27` |

رشتهٔ تاریخ و ساعت بر اساس `timezone` است؛ پیش‌فرض `Asia/Tehran` است و مقدار ارسالی offset ندارد.

گزینه‌های `value`، `min` و `max` رشته یا شیء `DateTimeInterface`/Carbon می‌پذیرند. در رشته‌ها فاصله به جای `T`، ارقام فارسی/عربی و ثانیهٔ صفر هم پذیرفته می‌شوند. رشتهٔ ISO دارای offset و ثانیهٔ غیرصفر پذیرفته نمی‌شود؛ برای زمان دارای منطقهٔ زمانی، شیء تاریخ بدهید. در حالت تاریخ و ساعت، شیء به timezone کامپوننت تبدیل می‌شود؛ در حالت فقط تاریخ، روز تقویمی آن بدون جابه‌جایی timezone حفظ می‌شود.

اگر `max` فقط تاریخ باشد، در حالت دارای ساعت تمام آن روز مجاز است. حدود باید با بازهٔ سال شمسی همپوشانی داشته باشند. مقدار اولیهٔ نامعتبر به‌صورت خطا نمایش داده می‌شود و خودکار با امروز جایگزین نمی‌شود.

مقدار ارسالی را در سرور اعتبارسنجی کنید. نمونهٔ FormRequest:

```php
public function rules(): array
{
    return [
        'scheduled_at' => ['nullable', 'date_format:Y-m-d\TH:i'],
        'birth_date' => ['nullable', 'date_format:Y-m-d'],
    ];
}
```

اگر می‌خواهید زمان را به UTC ذخیره کنید، ابتدا رشته را در همان timezone کامپوننت تفسیر کنید:

```php
use Carbon\CarbonImmutable;

$value = $request->validated('scheduled_at');

$scheduledAt = $value
    ? CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $value, 'Asia/Tehran')->utc()
    : null;
```

محدودیت‌های تاریخ و قوانین برنامه نیز باید در سرور اعمال شوند. تاریخ تولد یک تاریخ تقویمی است؛ آن را به UTC تبدیل نکنید.

## تنظیمات کامپوننت

| ویژگی | پیش‌فرض | کاربرد |
| --- | --- | --- |
| `name` | الزامی | نام فیلد ارسالی؛ نامی مانند `dates[birth]` هم پشتیبانی می‌شود. |
| `label` | `تاریخ و ساعت` | عنوان فیلد. |
| `theme` | `tailwind` | یکی از `tailwind` یا `bootstrap`. |
| `mode` | `direct` | حالت اولیهٔ `direct` یا `calendar`؛ کاربر می‌تواند تغییر دهد. |
| `value` | `null` | مقدار اولیهٔ میلادی یا شیء تاریخ. |
| `with-time` | `true` | نمایش ساعت و دقیقه. |
| `default-now` | `true` | مقداردهی فیلد خالی با زمان فعلی، در صورت مجاز بودن. |
| `timezone` | `Asia/Tehran` | منطقهٔ زمانی برای اکنون، اشیای تاریخ و زمان محلی ارسالی. |
| `min` و `max` | `null` | حدود میلادی به شکل رشته یا شیء تاریخ. |
| `min-year` و `max-year` | `1300` و `1450` | بازهٔ سال شمسی؛ ابتدا و انتها بین ۱۰۰۰ تا ۲۹۹۹ و حداکثر ۴۰۱ سال. |
| `required` | `false` | اعتبارسنجی الزامی مرورگر و پنهان کردن دکمهٔ پاک‌کردن. |
| `disabled` | `false` | غیرفعال کردن و حذف مقدار از ارسال فرم. |
| `readonly` | `false` | جلوگیری از ویرایش؛ مقدار همچنان ارسال می‌شود. |
| `clearable` | `true` | نمایش پاک‌کردن برای فیلد اختیاری. |
| `use-old` | `true` | اولویت old input لاراول نسبت به `value`، حتی مقدار خالی. |
| `error-message` | `null` | جایگزینی خطای فیلد؛ رشتهٔ `''` خطای این نمونه را مخفی می‌کند. |
| `id` | خودکار و یکتا | شناسهٔ input؛ در صورت نیاز مقدار مشخص بدهید. |

old input بر انتخاب پیش‌فرض زمان حال اولویت دارد. فیلد readonly یا disabled مقدار فعلی جدید تولید نمی‌کند. برای فیلد اختیاری یا تاریخ تولد، `:default-now="false"` بگذارید.

اگر چند فرم نام فیلد مشترک دارند، در فرم‌های غیرفعال `:use-old="false"` بگذارید و `:error-message` را متناسب با فرم تنظیم کنید. ویژگی‌های ریشه مانند `class` و listenerهای Alpine به عنصر ریشه منتقل می‌شوند.

## اتصال به Alpine و رویدادها

```blade
<div x-data="{ scheduledAt: '' }">
    <x-erfan-datepicker
        name="scheduled_at"
        theme="tailwind"
        :default-now="false"
        x-model="scheduledAt"
        x-on:erfan-datepicker:change="console.log($event.detail)"
    />
    <output x-text="scheduledAt"></output>
</div>
```

تأیید یا پاک‌کردن، رویدادهای `input`، `change` و `erfan-datepicker:change` را منتشر می‌کند. اطلاعات رویداد سفارشی شامل `name`، `value`، `timezone` و `withTime` است. انتخاب‌ها تا زدن «تأیید» موقت‌اند؛ انصراف، Escape و کلیک بیرون مقدار قبلی را نگه می‌دارند. reset فرم مقدار اولیه را برمی‌گرداند.

## شخصی‌سازی و به‌روزرسانی

فقط اگر به تغییر HTML نیاز دارید، قالب‌ها را منتشر کنید:

```sh
php artisan vendor:publish --tag=erfan-datepicker-views
```

قالب‌ها در `resources/views/vendor/erfan-datepicker` قرار می‌گیرند. در صورت تغییر پوستهٔ Tailwind، این مسیر هم باید اسکن شود. برای تغییرات ظاهری کوچک، CSS اختصاصی و محدود به کامپوننت مناسب‌تر است.

پس از به‌روزرسانی پکیج، فایل‌های فرانت را دوباره منتشر و build کنید. گزینهٔ `--force` فایل‌های JS/CSS منتشرشدهٔ قبلی را بازنویسی می‌کند؛ تغییرات اختصاصی برنامه را در فایل جدا نگه دارید:

```sh
composer update erfan-php/erfan-datepicker
php artisan vendor:publish --tag=erfan-datepicker-assets --force
npm run build
```

قالب‌های overrideشدهٔ برنامه خودکار به‌روز نمی‌شوند؛ هنگام ارتقا آن‌ها را با نسخهٔ جدید پکیج مقایسه کنید.

## رفع مشکل

| مشکل | موردی که باید بررسی کنید |
| --- | --- |
| خطای `erfanDatepicker is not defined` | ثبت کامپوننت روی همان نمونهٔ Alpine و قبل از `Alpine.start()`؛ سپس build مجدد. |
| ظاهر ناقص Tailwind | مسیر قالب‌های vendor در اسکن، بارگذاری ورودی Tailwind و build مجدد. |
| ظاهر ناقص Bootstrap | بارگذاری Bootstrap 5 CSS و CSS منتشرشدهٔ پکیج. |
| مقدار قبلی دوباره ظاهر می‌شود | old input اولویت دارد؛ فرم‌های متعدد را با `use-old` و `error-message` تفکیک کنید. |
| کامپوننت Blade شناخته نمی‌شود | package discovery را بررسی و `php artisan optimize:clear` را اجرا کنید. |

## توسعه

پشتیبانی اختصاصی Livewire و رابط انگلیسی ارائه نشده است.

برای گزارش مشکل، نسخه‌ها، پوسته و نمونهٔ Blade را در [Issues](https://github.com/erfan-php/erfan-datepicker/issues) بنویسید.

برای اجرای تست‌ها و نمونه‌ها، مخزن را clone کنید. Node.js 22.12+ لازم است:

```sh
composer install
npm ci
composer test
npm test
npm run build
php -S 127.0.0.1:8796 -t examples/dist
```

نمونه‌ها در http://127.0.0.1:8796 در دسترس‌اند.

[تغییرات نسخه‌ها](CHANGELOG.md) · [مجوز MIT](LICENSE)
