@extends('admin.classes.form')

@push('scripts')
<script>
    // Scripts spécifiques à la modification d'une classe
    document.addEventListener('DOMContentLoaded', function() {
        // Désactiver la modification de l'année scolaire après la création
        const academicYearSelect = document.getElementById('academic_year_id');
        if (academicYearSelect) {
            academicYearSelect.disabled = true;
        }
    });
</script>
@endpush
