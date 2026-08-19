@extends('layouts.app')

@section('title', 'Gruppa Info — Stage 1')

@section('content')
    <main class="container py-5">
    <div class="stage-one-card card border-0 shadow-sm mx-auto">
        <div class="card-body p-4 p-md-5">
            <span class="badge text-bg-success mb-3">Stage 1 ready</span>
            <h1 class="display-6 fw-semibold">Gruppa Info</h1>
            <p class="lead text-secondary">
                Laravel, MySQL and local frontend assets are connected.
            </p>

            <dl class="row mb-4">
                <dt class="col-sm-5">Europe/Minsk time</dt>
                <dd class="col-sm-7" data-testid="display-time">{{ $displayedAt }}</dd>
                <dt class="col-sm-5">Money sample</dt>
                <dd class="col-sm-7" data-testid="money-value">{{ $samplePrice }}</dd>
            </dl>

            <button class="btn btn-primary" type="button" data-stage-one-button>
                Verify project JavaScript
            </button>
            <p class="small text-success mt-3 mb-0" data-stage-one-result hidden>
                Project JavaScript is active.
            </p>
        </div>
    </div>
    </main>
@endsection
