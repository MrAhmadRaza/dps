@include('admin.partials.row-actions', ['actions' => [
    ['href' => $passwordRoute ?? null, 'icon' => 'fa-solid fa-key', 'label' => 'Reset password'],
]])
