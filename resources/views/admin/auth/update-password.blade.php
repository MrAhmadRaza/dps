@extends('admin.layouts.app')
@section('content')
<div class="ad-head">
    <div>
        <h1>Reset password</h1>
        <p>Set a new parent-portal password for {{ $parent->father_name }}.</p>
    </div>
    <div class="ad-head-actions">
        <a href="{{ route('admin.parent.show') }}" class="btn btn-light"><i class="fa-solid fa-arrow-left"></i>Parent accounts</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <form method="POST" action="{{ route('admin.parent.update.password') }}" class="panel">
            @csrf
            <input type="hidden" name="passwordId" value="{{ $passwordId }}">
            <div class="form-section">
                <label class="form-label" for="password">New password</label>
                <div class="input-group">
                    <input type="text" name="password" id="password" value="{{ old('password') }}" autocomplete="off"
                           class="form-control @error('password') is-invalid @enderror" minlength="4" maxlength="50" required>
                    <button type="button" class="btn btn-light" id="genPw"><i class="fa-solid fa-shuffle"></i>Suggest</button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                <div class="form-text">At least 4 characters. The parent can change it later from their account page in the parent portal.</div>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.parent.show') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-key"></i>Save new password</button>
            </div>
        </form>
    </div>
    <div class="col-lg-5">
        <section class="panel">
            <div class="panel-head"><h2>Account</h2></div>
            <div class="panel-body">
                <dl class="dl">
                    <dt>Father</dt><dd>{{ $parent->father_name }}</dd>
                    <dt>Sign-in CNIC</dt><dd class="mono">{{ $parent->father_nic }}</dd>
                    <dt>Contact</dt><dd>{{ $parent->contact_no ?: '—' }}</dd>
                    <dt>Children</dt>
                    <dd>
                        @forelse ($parent->students as $s)
                            <a href="{{ route('admin.view.admission', $s->id) }}">{{ $s->name }}</a>@if (!$loop->last), @endif
                        @empty — @endforelse
                    </dd>
                </dl>
            </div>
        </section>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('genPw').addEventListener('click', function () {
    var chars = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789', out = '';
    var buf = new Uint32Array(6); crypto.getRandomValues(buf);
    buf.forEach(function (n) { out += chars[n % chars.length]; });
    document.getElementById('password').value = out;
});
</script>
@endsection
