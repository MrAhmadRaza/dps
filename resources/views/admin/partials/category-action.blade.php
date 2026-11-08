@include('admin.partials.row-actions', ['actions' => [
    ['href' => $printHeadCategory ?? null, 'icon' => 'fa-solid fa-print', 'label' => 'Print entries', 'blank' => true],
    ['href' => $headCategoryRoute ?? null, 'icon' => 'fa-solid fa-plus', 'label' => 'Add entry'],
    ['href' => $editRoute ?? null, 'icon' => 'fa-regular fa-pen-to-square', 'label' => 'Edit category'],
    ['type' => 'delete', 'href' => $deleteRoute ?? null, 'label' => 'Delete category', 'confirm' => 'Delete this category and all its entries? This cannot be undone.'],
]])
