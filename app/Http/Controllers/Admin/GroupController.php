<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Group\GroupStatus;
use App\Domain\Group\GroupWorkflowService;
use App\Domain\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CleanupGroupRequest;
use App\Http\Requests\Admin\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\DictionaryItem;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class GroupController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Group::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:'.implode(',', array_column(GroupStatus::cases(), 'value'))],
            'tariff' => ['nullable', 'string', 'in:free,paid'],
            'quick' => ['nullable', 'string', 'in:awaiting_publication,requires_removal'],
            'sort' => ['nullable', 'string', 'in:created_at,published_at,expires_at'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        $groups = Group::query()->with(['owner', 'format', 'gender'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($matches) use ($search): void {
                    $like = '%'.$search.'%';
                    $matches->where('name', 'like', $like)
                        ->orWhereHas('owner', fn ($owner) => $owner->where(fn ($identity) => $identity
                            ->where('email', 'like', $like)->orWhere('first_name', 'like', $like)
                            ->orWhere('last_name', 'like', $like)));
                    if (ctype_digit($search)) {
                        $matches->orWhere('id', (int) $search);
                    }
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['tariff']), fn ($query) => $query->where('free', $filters['tariff'] === 'free'))
            ->when(($filters['quick'] ?? null) === 'awaiting_publication', fn ($query) => $query->where('status', GroupStatus::Approved->value))
            ->when(($filters['quick'] ?? null) === 'requires_removal', fn ($query) => $query->where('status', GroupStatus::Expired->value))
            ->orderBy($sort, $direction)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.groups.index', compact('groups', 'filters'));
    }

    public function create(): View
    {
        Gate::authorize('create', Group::class);

        return view('admin.groups.create', $this->formOptions());
    }

    public function store(StoreGroupRequest $request, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('create', Group::class);
        $owner = User::query()->findOrFail($request->integer('owner_id'));
        $group = $workflow->createDraft($owner);
        $group->update($request->groupData());

        return redirect()->route('admin.groups.show', $group)->with('status', 'Черновик группы создан.');
    }

    public function show(Group $group): View
    {
        Gate::authorize('view', $group);
        $group->load(['owner', 'format', 'gender', 'statusHistory.actor']);

        return view('admin.groups.show', compact('group'));
    }

    public function edit(Group $group): View
    {
        Gate::authorize('update', $group);

        return view('admin.groups.edit', ['group' => $group] + $this->formOptions());
    }

    public function update(UpdateGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('update', $group);
        $workflow->updateContent($group, $request->user(), $request->groupData());

        return redirect()->route('admin.groups.show', $group)->with('status', 'Данные группы обновлены.');
    }

    public function destroy(CleanupGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('cleanup', $group);
        $workflow->delete($group, $request->user(), 'cleanup');

        return redirect()->route('admin.groups.index')->with('status', 'Черновик группы удалён.');
    }

    /** @return array<string, array<int|string, string>> */
    private function formOptions(): array
    {
        $items = DictionaryItem::query()->where('active', true)
            ->whereHas('dictionary', fn ($query) => $query->where('active', true)->whereIn('code', ['group_format', 'gender']))
            ->with('dictionary')->orderBy('sort_order')->orderBy('name')->get();
        $owners = User::query()->where('admin', false)->where('disabled', false)->where('status', UserStatus::Approved->value)
            ->orderBy('last_name')->orderBy('first_name')->get()
            ->mapWithKeys(fn (User $owner) => [$owner->getKey() => $owner->fullName().' · '.$owner->email])->all();

        return [
            'owners' => $owners,
            'formats' => $items->where('dictionary.code', 'group_format')->pluck('name', 'id')->all(),
            'genders' => $items->where('dictionary.code', 'gender')->pluck('name', 'id')->all(),
        ];
    }
}
