@extends('layouts.student')

@section('title', 'Bulletin Annuel')

@include('reports.partials.bulletin-print-styles')

@push('styles')
<style>
    .bulletin-toolbar { margin-bottom: 1.25rem; }
    .bulletin-screen-wrap { background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,.1); padding: 8mm; margin-bottom: 1.5rem; }

    /* Consultation uniquement : l'impression du bulletin élève est
       désactivée par mesure de sécurité (voir aussi le blocage JS de
       Ctrl+P/Cmd+P ci-dessous). */
    @media print {
        body * { visibility: hidden !important; }
        .print-blocked-notice {
            visibility: visible !important;
            display: block !important;
            position: fixed; top: 40%; left: 0; right: 0;
            text-align: center; font-size: 14pt; color: #721c24;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <p class="print-blocked-notice d-none">Impression désactivée — consultation en ligne uniquement.</p>

    <div class="bulletin-toolbar no-print">
        <a href="{{ route('student.bulletin', ['semester' => 1]) }}" class="btn btn-outline-success me-2">
            <i class="fas fa-calendar-alt me-2"></i>Semestre 1
        </a>
        <a href="{{ route('student.bulletin', ['semester' => 2]) }}" class="btn btn-outline-success me-2">
            <i class="fas fa-calendar-alt me-2"></i>Semestre 2
        </a>
        <a href="{{ route('student.bulletin.annual') }}" class="btn btn-success">
            <i class="fas fa-graduation-cap me-2"></i>Bulletin Annuel
        </a>
    </div>

    @if(isset($error))
        <div class="alert alert-warning">{{ $error }}</div>
    @else
        <div class="bulletin-screen-wrap">
            @foreach($sheets as $sheet)
                @include('reports.partials.bulletin-print', ['sheet' => $sheet])
            @endforeach
        </div>

        <div class="text-center no-print">
            <a href="{{ route('student.grades') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour aux notes
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Consultation uniquement : bloque le raccourci clavier d'impression
    // (le bouton "Imprimer" a déjà été retiré de l'interface).
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'p') {
            e.preventDefault();
        }
    });
</script>
@endpush
