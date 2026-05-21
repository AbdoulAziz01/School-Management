<meta name="form-draft-scope" content="{{ auth()->id() ?? 'guest' }}_{{ auth()->user()->school_id ?? '0' }}">
<meta name="form-has-errors" content="{{ $errors->any() ? '1' : '0' }}">
@if(session('success'))
<meta name="form-draft-clear" content="1">
@endif
