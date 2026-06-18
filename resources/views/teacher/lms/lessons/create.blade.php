@extends('teacher.layouts.app')

@section('title', 'Publier un cours')

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all .2s;
        background: #f8fafc;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .upload-zone input[type="file"] { display: none; }
    .file-selected { display: none; }
    .file-selected.visible { display: flex; }
</style>
@endpush

@section('content')

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="h3 mb-0">Publier un cours</h1>
        <p class="text-muted small mb-0">Ajoutez un document PDF, Word ou un lien vidéo</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger small">
        <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body p-4">
        <form action="{{ route('teacher.lms.lesson.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Classe <span class="text-danger">*</span></label>
                    <select name="class_id" class="form-select @error('class_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Matière <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
                        <option value="">— Sélectionner —</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Titre du cours <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title') }}" placeholder="Ex : Cours de Mathématiques — Chapitre 3" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Description <span class="text-muted fw-normal">(facultatif)</span></label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Résumé bref du contenu…">{{ old('description') }}</textarea>
            </div>

            {{-- Type de fichier ────────────────────────────────────────────── --}}
            <div class="mb-4">
                <label class="form-label fw-semibold">Type de contenu <span class="text-danger">*</span></label>
                <div class="d-flex gap-3 flex-wrap">
                    @foreach(['pdf' => ['PDF', 'fa-file-pdf', 'danger'], 'doc' => ['Document Word', 'fa-file-word', 'primary'], 'video' => ['Lien Vidéo', 'fa-video', 'success']] as $type => [$label, $icon, $color])
                    <label class="d-flex align-items-center gap-2 p-3 border rounded-3 cursor-pointer type-option"
                           style="cursor:pointer; transition: all .15s;"
                           data-type="{{ $type }}">
                        <input type="radio" name="file_type" value="{{ $type }}"
                               {{ old('file_type', 'pdf') === $type ? 'checked' : '' }}
                               class="form-check-input mt-0" style="width:18px;height:18px;">
                        <i class="fas {{ $icon }} text-{{ $color }}"></i>
                        <span class="fw-semibold small">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Zone upload fichier ─────────────────────────────────────────── --}}
            <div id="file-upload-zone" class="mb-4">
                <label class="form-label fw-semibold">Fichier <span class="text-danger">*</span></label>
                <div class="upload-zone" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <input type="file" id="fileInput" name="file" accept=".pdf,.doc,.docx">
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-1">Glissez votre fichier ici ou cliquez pour sélectionner</p>
                    <p class="text-muted small">PDF ou Word · Max 20 Mo</p>
                </div>
                <div id="fileSelected" class="file-selected align-items-center gap-3 p-3 border rounded-3 bg-light mt-2">
                    <i class="fas fa-file-check text-success fs-4"></i>
                    <div class="flex-grow-1">
                        <div id="fileName" class="fw-semibold small"></div>
                        <div id="fileSize" class="text-muted" style="font-size:.75rem;"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeFile">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- URL vidéo ────────────────────────────────────────────────────── --}}
            <div id="video-url-zone" class="mb-4 d-none">
                <label class="form-label fw-semibold">URL de la vidéo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fab fa-youtube text-danger"></i></span>
                    <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror"
                           value="{{ old('video_url') }}"
                           placeholder="https://www.youtube.com/watch?v=…">
                    @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <small class="text-muted">YouTube, Vimeo ou tout autre lien vidéo</small>
            </div>

            <div class="d-flex gap-3 justify-content-end">
                <a href="{{ route('teacher.lms.index') }}" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-paper-plane me-2"></i>Publier le cours
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput    = document.getElementById('fileInput');
    const dropZone     = document.getElementById('dropZone');
    const fileSelected = document.getElementById('fileSelected');
    const fileUpZone   = document.getElementById('file-upload-zone');
    const videoZone    = document.getElementById('video-url-zone');
    const radios       = document.querySelectorAll('input[name="file_type"]');

    function updateZoneVisibility() {
        const type = document.querySelector('input[name="file_type"]:checked')?.value;
        if (type === 'video' || type === 'link') {
            fileUpZone.classList.add('d-none');
            videoZone.classList.remove('d-none');
        } else {
            fileUpZone.classList.remove('d-none');
            videoZone.classList.add('d-none');
        }
    }

    radios.forEach(r => r.addEventListener('change', updateZoneVisibility));
    updateZoneVisibility();

    function showFile(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024 / 1024).toFixed(2) + ' Mo';
        fileSelected.classList.add('visible');
        dropZone.style.display = 'none';
    }

    fileInput.addEventListener('change', function () {
        if (this.files[0]) showFile(this.files[0]);
    });

    document.getElementById('removeFile').addEventListener('click', function () {
        fileInput.value = '';
        fileSelected.classList.remove('visible');
        dropZone.style.display = '';
    });

    ['dragover','dragenter'].forEach(e => dropZone.addEventListener(e, ev => {
        ev.preventDefault(); dropZone.classList.add('drag-over');
    }));
    ['dragleave','drop'].forEach(e => dropZone.addEventListener(e, ev => {
        ev.preventDefault(); dropZone.classList.remove('drag-over');
    }));
    dropZone.addEventListener('drop', function(ev) {
        const file = ev.dataTransfer.files[0];
        if (file) {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
            showFile(file);
        }
    });
});
</script>
@endpush
