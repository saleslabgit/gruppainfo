<x-ui.card variant="section" title="История статусов">
    @if($group->statusHistory->isEmpty())
        <p class="ui-muted">Переходов статуса пока нет.</p>
    @else
        <x-ui.timeline>
            @foreach($group->statusHistory->sortByDesc('created_at') as $entry)
                <x-ui.timeline-item
                    :title="$entry->to_status->label()"
                    :variant="$entry->to_status->badgeVariant()"
                    :meta="($entry->actor?->fullName() ?? 'Система').' · '.App\Support\DateTimeFormatter::format($entry->created_at)"
                    :comment="$entry->comment"
                />
            @endforeach
        </x-ui.timeline>
    @endif
</x-ui.card>
