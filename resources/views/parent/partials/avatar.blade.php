@php
    $photo = ($student->photo_path && is_file(public_path($student->photo_path)))
        ? asset($student->photo_path)
        : asset('default/dummy.png');
@endphp
<img src="{{ $photo }}" alt="" class="pp-avatar" loading="lazy" width="56" height="56">
