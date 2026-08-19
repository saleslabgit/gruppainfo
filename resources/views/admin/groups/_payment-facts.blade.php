<x-ui.description-list :columns="2">
    @foreach($payments as $payment)
        <x-ui.description-item label="Сумма"><x-ui.money :minor-units="$payment->amount" :currency="$payment->currency" /></x-ui.description-item>
        <x-ui.description-item label="ID транзакции">{{ $payment->transaction_id ?: 'Не указан' }}</x-ui.description-item>
        <x-ui.description-item label="Оплачено">@if($payment->paid_at)<x-ui.date :value="$payment->paid_at" />@else Не указано @endif</x-ui.description-item>
    @endforeach
</x-ui.description-list>
