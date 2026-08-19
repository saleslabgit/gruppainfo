<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class CabinetController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('cabinet.groups');
    }

    public function profile(Request $request): View
    {
        /** @var User $psychologist */
        $psychologist = $request->user();
        Gate::authorize('viewOwnProfile', $psychologist);
        $psychologist->load(['educationType', 'documents']);

        return view('cabinet.profile', compact('psychologist'));
    }
}
