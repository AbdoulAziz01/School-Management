@extends('layouts.student')

@section('title', 'Bulletin Semestriel')

@include('reports.partials.bulletin-print-styles')

@push('styles')
<style>
    /* Barre d'outils écran (masquée à l'impression) */
    .bulletin-toolbar { margin-bottom: 1.25rem; }
    .bulletin-toolbar .nav-link {
        border: 2px solid #1a5f2a; color: #1a5f2a;
        margin-right: 0.5rem; border-radius: 20px;
        padding: 0.5rem 1.5rem; font-weight: 600;
    }
    .bulletin-toolbar .nav-link.active { background-color: #1a5f2a; color: white; }

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

    @if(!empty($error))
        <div class="alert alert-warning">{{ $error }}</div>
    @else
        <div class="bulletin-toolbar no-print">
            <nav class="nav">
                <a class="nav-link {{ $semester == 1 ? 'active' : '' }}" href="{{ route('student.bulletin', ['semester' => 1]) }}">
                    <i class="fas fa-calendar-alt me-2"></i>Semestre 1
                </a>
                <a class="nav-link {{ $semester == 2 ? 'active' : '' }}" href="{{ route('student.bulletin', ['semester' => 2]) }}">
                    <i class="fas fa-calendar-alt me-2"></i>Semestre 2
                </a>
                <a class="nav-link" href="{{ route('student.bulletin.annual') }}">
                    <i class="fas fa-graduation-cap me-2"></i>Bulletin Annuel
                </a>
            </nav>
        </div>

        <div class="bulletin-screen-wrap">
            @include('reports.partials.bulletin-print', ['sheet' => $sheet])
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
