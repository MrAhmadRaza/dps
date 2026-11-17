@include('admin.partials.row-actions', ['actions' => [
    ['href' => $viewRoute ?? null, 'icon' => 'fa-regular fa-eye', 'label' => 'View student'],
    ['href' => $editRoute ?? null, 'icon' => 'fa-regular fa-pen-to-square', 'label' => 'Edit admission'],
    ['href' => $printRoute ?? null, 'icon' => 'fa-solid fa-print', 'label' => 'Print application', 'blank' => true],
    ['href' => $voucherRoute ?? null, 'icon' => 'fa-solid fa-file-invoice', 'label' => 'Print admission voucher', 'blank' => true],
    ['type' => 'delete', 'href' => $deleteRoute ?? null, 'label' => 'Delete admission', 'confirm' => 'Delete this admission? The student and their challans are removed. This cannot be undone.'],
]])
