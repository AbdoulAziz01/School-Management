@if(($academicYears ?? collect())->isNotEmpty())
    <form method="GET" action="{{ $action }}" class="d-flex align-items-center gap-2">
        @foreach($queryParams ?? [] as $param => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endif
        @endforeach
        <span class="text-muted small d-none d-sm-inline"><i class="fas fa-calendar-alt me-1"></i>Année :</span>
        <select name="academic_year_id" class="form-select form-select-sm" style="min-width: 11rem;" onchange="this.form.submit()" aria-label="Choisir l'année scolaire">
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected(($selectedYear?->id ?? null) === $year->id)>
                    {{ $year->name }}
                    @if($year->is_current) — courante @endif
                    @if($year->isClosed()) — terminée @endif
                </option>
            @endforeach
        </select>
    </form>
@endif
