@extends('admin.layouts.app')

@section('title', 'Associer les colonnes')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <p class="mb-3">
                <a href="{{ route('admin.teachers.import') }}" class="text-decoration-none small">
                    <i class="fas fa-arrow-left me-1"></i> Recommencer avec un autre fichier
                </a>
            </p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-columns me-2"></i>Associer les colonnes de votre fichier</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Nous avons deviné automatiquement les correspondances les plus probables. Vérifiez et
                        corrigez si besoin, puis passez à l'aperçu.
                    </p>

                    <form method="POST" action="{{ route('admin.teachers.import.preview', $token) }}">
                        @csrf
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Champ attendu</th>
                                    <th>Colonne de votre fichier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fields as $field => $meta)
                                    <tr>
                                        <td>
                                            {{ $meta['label'] }}
                                            @if($meta['required'])
                                                <span class="text-danger">*</span>
                                            @endif
                                        </td>
                                        <td>
                                            <select name="mapping[{{ $field }}]" class="form-select form-select-sm" {{ $meta['required'] ? 'required' : '' }}>
                                                <option value="">— Ignorer —</option>
                                                @foreach($headers as $index => $header)
                                                    <option value="{{ $index }}" {{ ($guessedMapping[$field] ?? null) === $index ? 'selected' : '' }}>
                                                        {{ $header !== '' ? $header : '(colonne '.($index + 1).')' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p class="small text-muted"><span class="text-danger">*</span> champ obligatoire.</p>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-arrow-right me-1"></i> Voir l'aperçu
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
