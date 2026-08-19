<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Group\GroupWorkflowService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ActivateGroupRequest;
use App\Http\Requests\Admin\ModerateGroupRequest;
use App\Http\Requests\Admin\ModerationCommentRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class GroupModerationController extends Controller
{
    public function revision(ModerationCommentRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('moderate', $group);
        $workflow->requestRevision($group, $request->user(), $request->string('comment')->toString());

        return $this->done($group, 'Группа отправлена на доработку.');
    }

    public function reject(ModerationCommentRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('moderate', $group);
        $workflow->reject($group, $request->user(), $request->string('comment')->toString());

        return $this->done($group, 'Группа отклонена.');
    }

    public function approve(ModerateGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('moderate', $group);
        $workflow->approve($group, $request->user());

        return $this->done($group, 'Группа одобрена.');
    }

    public function activate(ActivateGroupRequest $request, Group $group, GroupWorkflowService $workflow): RedirectResponse
    {
        Gate::authorize('moderate', $group);
        $workflow->activate($group, $request->user(), $request->validated('external_catalog_id'));

        return $this->done($group, 'Группа отмечена активной.');
    }

    private function done(Group $group, string $message): RedirectResponse
    {
        return redirect()->route('admin.groups.show', $group)->with('status', $message);
    }
}
