@include('admin.partials.row-actions', ['actions' => [
    ['href' => $editRoute ?? null, 'icon' => 'fa-regular fa-pen-to-square', 'label' => 'Edit entry'],
    ['type' => 'delete', 'href' => $deleteRoute ?? null, 'label' => 'Delete entry', 'confirm' => 'Delete this entry? This cannot be undone.'],
]])
