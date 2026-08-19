<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cabinet;

use App\Domain\Group\GroupWorkflowService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\DeleteGroupRequest;
use App\Http\Requests\SubmitGroupRequest;
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
        /** @var User $owner */
        $owner = $request->user();
        $groups = Group::query()->whereBelongsTo($owner, 'owner')->with(['format', 'gender'])
            ->latest('created_at')->paginate(20)->withQueryString();

        return view('cabinet.groups.index', compact('groups'));
    }

    public function store(CreateGroupRequest $request, GroupWorkflowService $workflow): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        Gate::authorize('create', Group::class);
        $group = $workflow->createDraft($owner);

        return redirect()->route('cabinet.groups.edit', $group)->with('status', 'Черновик группы создан.');
    }

    public function show(Group $group): View
    {
        Gate::authorize('view', $group);
        $group->load(['owner', 'format', 'gender', 'statusHistory.actor']);

        return view('cabinet.groups.show', compact('group'));
    }

    public function edit(Group $group): View
    {
        Gate::authorize('update', $group);

        return view('cabinet.groups.edit', ['group' => $group] + $this->dictionaries());
    }

    public function update(UpdateGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('update', $group);
        $workflow->updateContent($group, $request->user(), $request->groupData());

        return redirect()->route('cabinet.groups.show', $group)->with('status', 'Данные группы сохранены.');
    }

    public function submit(SubmitGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('submit', $group);
        $workflow->submit($group, $request->user(), $request->groupData());

        return redirect()->route('cabinet.groups.show', $group)->with('status', 'Группа отправлена на модерацию.');
    }

    public function destroy(DeleteGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('delete', $group);
        $workflow->delete($group, $request->user(), 'delete');

        return redirect()->route('cabinet.groups')->with('status', 'Группа удалена.');
    }

    /** @return array{formats: array<int|string, string>, genders: array<int|string, string>} */
    private function dictionaries(): array
    {
        $items = DictionaryItem::query()->where('active', true)
            ->whereHas('dictionary', fn ($query) => $query->where('active', true)->whereIn('code', ['group_format', 'gender']))
            ->with('dictionary')->orderBy('sort_order')->orderBy('name')->get();

        return [
            'formats' => $items->where('dictionary.code', 'group_format')->pluck('name', 'id')->all(),
            'genders' => $items->where('dictionary.code', 'gender')->pluck('name', 'id')->all(),
        ];
    }
}
