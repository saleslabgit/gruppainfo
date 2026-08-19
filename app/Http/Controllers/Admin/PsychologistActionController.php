<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\Exceptions\InvalidStatusTransition;
use App\Domain\User\PsychologistAdminService;
use App\Domain\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePsychologistTariffRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class PsychologistActionController extends Controller
{
    public function approve(User $psychologist, PsychologistAdminService $service): RedirectResponse
    {
        return $this->transition($psychologist, UserStatus::Approved, $service, 'Психолог принят.');
    }

    public function reject(User $psychologist, PsychologistAdminService $service): RedirectResponse
    {
        return $this->transition($psychologist, UserStatus::Rejected, $service, 'Психолог отклонён.');
    }

    public function tariff(ChangePsychologistTariffRequest $request, User $psychologist, PsychologistAdminService $service): RedirectResponse
    {
        $service->changeTariff($psychologist, $request->boolean('free'), $request->user());

        return back()->with('status', 'Тариф психолога изменён.');
    }

    public function disable(User $psychologist, PsychologistAdminService $service): RedirectResponse
    {
        Gate::authorize('manage', $psychologist);
        $service->setDisabled($psychologist, true, request()->user());

        return back()->with('status', 'Доступ психолога отключён.');
    }

    public function enable(User $psychologist, PsychologistAdminService $service): RedirectResponse
    {
        Gate::authorize('manage', $psychologist);
        $service->setDisabled($psychologist, false, request()->user());

        return back()->with('status', 'Доступ психолога включён.');
    }

    private function transition(
        User $psychologist,
        UserStatus $target,
        PsychologistAdminService $service,
        string $message,
    ): RedirectResponse {
        Gate::authorize('manage', $psychologist);

        try {
            $service->transition($psychologist, $target, request()->user());
        } catch (InvalidStatusTransition) {
            return back()->with('error', 'Этот переход статуса недоступен.');
        }

        return back()->with('status', $message);
    }
}
