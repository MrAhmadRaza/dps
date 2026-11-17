@include('admin.partials.row-actions', ['actions' => [
    ['href' => $viewRoute ?? null, 'icon' => 'fa-regular fa-eye', 'label' => 'View classes'],
    ['href' => $editRoute ?? null, 'icon' => 'fa-regular fa-pen-to-square', 'label' => 'Edit session'],
    ['type' => 'delete', 'href' => $deleteRoute ?? null, 'label' => 'Delete session', 'confirm' => 'Delete this academic session and its classes? This cannot be undone.'],
]])
