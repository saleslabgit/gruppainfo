@props(['minorUnits', 'currency' => 'BYN'])
<span {{ $attributes }}>{{ \App\Support\MoneyFormatter::format($minorUnits, $currency) }}</span>
