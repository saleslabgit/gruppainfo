<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Domain\User\PsychologistAdminService;
use App\Domain\User\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePsychologistRequest;
use App\Http\Requests\Admin\UpdatePsychologistRequest;
use App\Models\DictionaryItem;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PsychologistController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:'.implode(',', array_column(UserStatus::cases(), 'value'))],
            'tariff' => ['nullable', 'string', 'in:free,paid'],
            'access' => ['nullable', 'string', 'in:enabled,disabled'],
        ]);
        $search = trim((string) ($filters['search'] ?? ''));

        $psychologists = User::query()
            ->where('admin', false)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($identity) use ($search): void {
                    $like = '%'.$search.'%';
                    $identity
                        ->where('last_name', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like);
                });
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(isset($filters['tariff']), fn ($query) => $query->where('free', $filters['tariff'] === 'free'))
            ->when(isset($filters['access']), fn ($query) => $query->where('disabled', $filters['access'] === 'disabled'))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.psychologists.index', [
            'psychologists' => $psychologists,
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.psychologists.create', [
            'educationTypes' => $this->educationTypes(),
        ]);
    }

    public function store(StorePsychologistRequest $request, PsychologistAdminService $service): RedirectResponse
    {
        $psychologist = $service->create(
            $request->profileData(),
            $request->boolean('free'),
            $request->user(),
        );

        return redirect()
            ->route('admin.psychologists.show', $psychologist)
            ->with('status', 'Психолог создан.');
    }

    public function show(User $psychologist): View
    {
        Gate::authorize('view', $psychologist);
        $psychologist->load(['educationType', 'documents']);

        return view('admin.psychologists.show', compact('psychologist'));
    }

    public function edit(User $psychologist): View
    {
        Gate::authorize('update', $psychologist);

        return view('admin.psychologists.edit', [
            'psychologist' => $psychologist,
            'educationTypes' => $this->educationTypes(),
        ]);
    }

    public function update(UpdatePsychologistRequest $request, User $psychologist): RedirectResponse
    {
        $psychologist->update($request->profileData());

        return redirect()
            ->route('admin.psychologists.show', $psychologist)
            ->with('status', 'Данные психолога обновлены.');
    }

    public function destroy(User $psychologist, PsychologistAdminService $service): RedirectResponse
    {
        Gate::authorize('delete', $psychologist);
        $service->delete($psychologist, request()->user());

        return redirect()
            ->route('admin.psychologists.index')
            ->with('status', 'Психолог удалён.');
    }

    /** @return array<int|string, string> */
    private function educationTypes(): array
    {
        return DictionaryItem::query()
            ->where('active', true)
            ->whereHas('dictionary', fn ($query) => $query->where('code', 'education_type')->where('active', true))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
