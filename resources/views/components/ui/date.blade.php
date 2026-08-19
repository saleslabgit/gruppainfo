@props(['value'])
<time {{ $attributes }}>{{ \App\Support\DateTimeFormatter::format($value) }}</time>
