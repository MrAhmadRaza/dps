@include('admin.partials.row-actions', ['actions' => [
    ['href' => $printRoute ?? null, 'icon' => 'fa-solid fa-print', 'label' => 'Print voucher', 'blank' => true],
    ['href' => $viewRoute ?? null, 'icon' => 'fa-regular fa-eye', 'label' => 'View voucher'],
    ['href' => $editRoute ?? null, 'icon' => 'fa-regular fa-pen-to-square', 'label' => 'Edit voucher'],
    ['type' => 'delete', 'href' => $deleteRoute ?? null, 'label' => 'Delete voucher', 'confirm' => 'Delete this fee voucher? This cannot be undone.'],
]])
