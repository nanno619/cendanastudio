<?php

$names = [
    'Dewi Anggraini', 'Bagas Prasetyo', 'Maya Kusuma', 'Rizky Hidayat', 'Lestari Wulandari', 'Fajar Nugroho',
    'Putri Maharani', 'Andika Saputra', 'Nadia Permata', 'Yoga Pratama', 'Siti Aisyah', 'Hendra Wijaya',
    'Ratna Sari', 'Dimas Aryo', 'Citra Lestari', 'Eko Susanto', 'Indah Kurnia', 'Galih Ramadhan',
    'Wulan Safitri', 'Taufik Hidayah', 'Anisa Rahma', 'Bayu Setiawan', 'Kartika Dewi', 'Rendra Mahesa',
];
$roles = ['Admin', 'Editor', 'Cashier', 'Warehouse', 'Customer'];
$statuses = [['Active', 'success'], ['Active', 'success'], ['Active', 'success'], ['Pending', 'warning'], ['Inactive', 'neutral']];
$avatarTones = ['soft', 'info', 'success', 'warning', 'danger'];
$seen = ['Just now', '5 minutes ago', '1 hour ago', 'Yesterday', '3 days ago', '1 week ago', '2 weeks ago'];
$users = array_map(function (string $name, int $index) use ($roles, $statuses, $avatarTones, $seen): array {
    $status = $statuses[$index % count($statuses)];
    $parts = preg_split('/\s+/', $name) ?: [];
    $initials = implode('', array_map(static fn (string $part): string => mb_substr($part, 0, 1), array_slice($parts, 0, 2)));

    return [
        'name' => $name,
        'initials' => $initials,
        'email' => strtolower(str_replace(' ', '.', $name)).'@email.id',
        'avTone' => $avatarTones[$index % count($avatarTones)],
        'role' => $roles[($index * 3) % count($roles)],
        'status' => $status[0],
        'statusKey' => strtolower($status[0]),
        'tone' => $status[1],
        'orders' => (($index * 37) % 90) + 3,
        'seen' => $seen[$index % count($seen)],
    ];
}, $names, array_keys($names));

return [
    'name' => env('ADMIN_NAME', 'Cendana Studio'),
    'brand' => [
        'name' => env('ADMIN_BRAND_NAME', 'Cendana Studio Admin'),
        'workspace' => env('ADMIN_WORKSPACE', 'Cendana Studio'),
        'description' => env('ADMIN_WORKSPACE_DESCRIPTION', 'Cendana Studio CRM'),
        'footer' => env('ADMIN_FOOTER', '© Cendana Studio'),
    ],
    'showcase' => (bool) env('ADMIN_SHOWCASE', true),
    // First admin login created by `php artisan db:seed`. Required in production; never commit real values.
    'seed_admin' => [
        'name' => env('ADMIN_SEED_NAME', 'Administrator'),
        'email' => env('ADMIN_SEED_EMAIL'),
        'password' => env('ADMIN_SEED_PASSWORD'),
    ],
    'locales' => [
        'en' => 'English',
        'ms' => 'Bahasa Melayu',
    ],
    'navigation' => [
        ['label' => 'Platform', 'items' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'dashboard'],
            ['label' => 'Analytics', 'route' => 'admin.analytics', 'active' => 'admin.analytics', 'icon' => 'chart', 'badge' => 'New'],
        ]],
        ['label' => 'Components', 'showcase' => true, 'items' => [
            ['label' => 'Cards', 'route' => 'showcase.cards', 'active' => 'showcase.cards', 'icon' => 'grid'],
            ['label' => 'Tables', 'route' => 'showcase.tables', 'active' => 'showcase.tables', 'icon' => 'table'],
            ['label' => 'Table states', 'route' => 'showcase.table-states', 'active' => 'showcase.table-states', 'icon' => 'layers'],
            ['label' => 'Forms', 'route' => 'showcase.forms', 'active' => 'showcase.forms', 'icon' => 'file-text'],
            ['label' => 'Filters and inputs', 'route' => 'showcase.filters', 'active' => 'showcase.filters', 'icon' => 'filter'],
            ['label' => 'Charts', 'route' => 'showcase.charts', 'active' => 'showcase.charts', 'icon' => 'pie'],
            ['label' => 'Buttons and badges', 'route' => 'showcase.buttons', 'active' => 'showcase.buttons', 'icon' => 'pointer'],
            ['label' => 'Feedback', 'route' => 'showcase.feedback', 'active' => 'showcase.feedback', 'icon' => 'message'],
            ['label' => 'Navigation', 'route' => 'showcase.navigation', 'active' => 'showcase.navigation', 'icon' => 'compass'],
            ['label' => 'Data patterns', 'route' => 'showcase.data-patterns', 'active' => 'showcase.data-patterns', 'icon' => 'layers'],
        ]],
        ['label' => 'Sample pages', 'showcase' => true, 'items' => [
            ['label' => 'Entry list', 'route' => 'examples.records.index', 'active' => 'examples.records.index', 'icon' => 'table'],
            ['label' => 'Entry details', 'route' => 'examples.records.show', 'active' => 'examples.records.show', 'icon' => 'eye'],
            ['label' => 'Entry form', 'route' => 'examples.records.create', 'active' => 'examples.records.create', 'icon' => 'edit'],
        ]],
        ['label' => 'Pages', 'items' => [
            ['label' => 'Account settings', 'route' => 'admin.settings', 'active' => 'admin.settings', 'icon' => 'settings'],
            ['id' => 'auth', 'label' => 'Authentication', 'icon' => 'lock', 'children' => [
                ['label' => 'Error 404', 'route' => 'demo.404', 'active' => 'demo.404'],
            ]],
        ]],
    ],
    'accents' => [
        ['id' => 'zinc', 'name' => 'Zinc', 'hex' => '#18181b'], ['id' => 'blue', 'name' => 'Blue', 'hex' => '#2563eb'],
        ['id' => 'indigo', 'name' => 'Indigo', 'hex' => '#4f46e5'], ['id' => 'violet', 'name' => 'Violet', 'hex' => '#7c3aed'],
        ['id' => 'pink', 'name' => 'Pink', 'hex' => '#db2777'], ['id' => 'rose', 'name' => 'Rose', 'hex' => '#e11d48'],
        ['id' => 'orange', 'name' => 'Orange', 'hex' => '#c2410c'], ['id' => 'amber', 'name' => 'Amber', 'hex' => '#f59e0b'],
        ['id' => 'green', 'name' => 'Green', 'hex' => '#15803d'], ['id' => 'emerald', 'name' => 'Emerald', 'hex' => '#059669'],
        ['id' => 'teal', 'name' => 'Teal', 'hex' => '#0d9488'], ['id' => 'cyan', 'name' => 'Cyan', 'hex' => '#0e7490'],
    ],
    'radii' => [
        ['value' => '0', 'name' => 'Sharp'], ['value' => '0.3', 'name' => 'Small'],
        ['value' => '0.5', 'name' => 'Medium'], ['value' => '0.75', 'name' => 'Large'], ['value' => '1', 'name' => 'Round'],
    ],
    'demo' => [
        'records' => [
            ['code' => 'ENT-001', 'title' => 'Onboarding guide', 'category' => 'Guides', 'owner' => 'Ayu Rahmawati', 'status' => 'Active', 'statusKey' => 'active', 'tone' => 'success', 'updated' => 'Today'],
            ['code' => 'ENT-002', 'title' => 'Team meeting notes', 'category' => 'Notes', 'owner' => 'Bagas Prasetyo', 'status' => 'Draft', 'statusKey' => 'draft', 'tone' => 'warning', 'updated' => 'Yesterday'],
            ['code' => 'ENT-003', 'title' => 'Launch checklist', 'category' => 'Guides', 'owner' => 'Maya Kusuma', 'status' => 'In review', 'statusKey' => 'in-review', 'tone' => 'info', 'updated' => '28 Sep 2026'],
            ['code' => 'ENT-004', 'title' => 'Monthly report', 'category' => 'Reports', 'owner' => 'Rizky Hidayat', 'status' => 'Active', 'statusKey' => 'active', 'tone' => 'success', 'updated' => '27 Sep 2026'],
            ['code' => 'ENT-005', 'title' => 'Old procedures archive', 'category' => 'Archive', 'owner' => 'Fajar Nugroho', 'status' => 'Archived', 'statusKey' => 'archived', 'tone' => 'neutral', 'updated' => '25 Sep 2026'],
            ['code' => 'ENT-006', 'title' => 'Next quarter plan', 'category' => 'Notes', 'owner' => 'Ayu Rahmawati', 'status' => 'Draft', 'statusKey' => 'draft', 'tone' => 'warning', 'updated' => '24 Sep 2026'],
            ['code' => 'ENT-007', 'title' => 'Service standards', 'category' => 'Guides', 'owner' => 'Bagas Prasetyo', 'status' => 'Active', 'statusKey' => 'active', 'tone' => 'success', 'updated' => '23 Sep 2026'],
        ],
        'users' => $users,
        'orders' => [
            ['id' => '#KK-2841', 'customer' => 'Dewi Anggraini', 'product' => 'Arabika Gayo coffee 1 kg × 2', 'status' => 'Completed', 'tone' => 'success', 'date' => '28 Sep 2026', 'total' => 'RM 96.00', 'struck' => ''],
            ['id' => '#KK-2840', 'customer' => 'Bagas Prasetyo', 'product' => '5-variety sampler pack', 'status' => 'Shipped', 'tone' => 'info', 'date' => '28 Sep 2026', 'total' => 'RM 61.00', 'struck' => ''],
            ['id' => '#KK-2839', 'customer' => 'Maya Kusuma', 'product' => 'Toraja coffee 500 g', 'status' => 'Processing', 'tone' => 'warning', 'date' => '27 Sep 2026', 'total' => 'RM 36.00', 'struck' => ''],
            ['id' => '#KK-2838', 'customer' => 'Rizky Hidayat', 'product' => 'French press 600 ml', 'status' => 'Completed', 'tone' => 'success', 'date' => '27 Sep 2026', 'total' => 'RM 76.00', 'struck' => ''],
            ['id' => '#KK-2837', 'customer' => 'Lestari Wulandari', 'product' => 'Kintamani coffee 1 kg', 'status' => 'Cancelled', 'tone' => 'danger', 'date' => '26 Sep 2026', 'total' => 'RM 54.00', 'struck' => 'text-muted-foreground line-through'],
            ['id' => '#KK-2836', 'customer' => 'Fajar Nugroho', 'product' => 'Manual grinder', 'status' => 'Shipped', 'tone' => 'info', 'date' => '26 Sep 2026', 'total' => 'RM 135.00', 'struck' => ''],
        ],
        'sales' => [
            ['initials' => 'DA', 'name' => 'Dewi Anggraini', 'email' => 'dewi.anggraini@email.com', 'amount' => '+RM 96.00', 'tone' => 'soft'],
            ['initials' => 'BP', 'name' => 'Bagas Prasetyo', 'email' => 'bagas.p@email.com', 'amount' => '+RM 61.00', 'tone' => 'info'],
            ['initials' => 'MK', 'name' => 'Maya Kusuma', 'email' => 'maya.kusuma@email.com', 'amount' => '+RM 36.00', 'tone' => 'success'],
            ['initials' => 'RH', 'name' => 'Rizky Hidayat', 'email' => 'rizky.h@email.com', 'amount' => '+RM 76.00', 'tone' => 'warning'],
            ['initials' => 'FN', 'name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@email.com', 'amount' => '+RM 135.00', 'tone' => 'danger'],
        ],
        'products' => [
            ['name' => 'Arabika Gayo 1 kg', 'sku' => 'KP-001', 'category' => 'Coffee beans', 'price' => 'RM 48.00', 'stock' => 128, 'pct' => 86, 'bar' => 'bg-positive', 'rating' => '4.9', 'status' => 'In stock', 'tone' => 'success'],
            ['name' => 'Robusta Temanggung 1 kg', 'sku' => 'KP-002', 'category' => 'Coffee beans', 'price' => 'RM 34.00', 'stock' => 74, 'pct' => 52, 'bar' => 'bg-positive', 'rating' => '4.7', 'status' => 'In stock', 'tone' => 'success'],
            ['name' => 'Toraja Sapan 500 g', 'sku' => 'KP-003', 'category' => 'Coffee beans', 'price' => 'RM 36.00', 'stock' => 18, 'pct' => 14, 'bar' => 'bg-caution', 'rating' => '4.8', 'status' => 'Low stock', 'tone' => 'warning'],
            ['name' => 'French press 600 ml', 'sku' => 'AL-104', 'category' => 'Equipment', 'price' => 'RM 76.00', 'stock' => 41, 'pct' => 34, 'bar' => 'bg-positive', 'rating' => '4.6', 'status' => 'In stock', 'tone' => 'success'],
            ['name' => 'Manual grinder', 'sku' => 'AL-108', 'category' => 'Equipment', 'price' => 'RM 135.00', 'stock' => 0, 'pct' => 0, 'bar' => 'bg-destructive', 'rating' => '4.8', 'status' => 'Sold out', 'tone' => 'danger'],
            ['name' => '5-variety sampler pack', 'sku' => 'PK-201', 'category' => 'Bundles', 'price' => 'RM 61.00', 'stock' => 96, 'pct' => 70, 'bar' => 'bg-positive', 'rating' => '4.9', 'status' => 'In stock', 'tone' => 'success'],
        ],
        'activity' => [
            ['icon' => 'check-circle', 'tile' => 'bg-success-soft text-success', 'text' => 'Order #KK-2841 completed', 'time' => '10 minutes ago'],
            ['icon' => 'truck', 'tile' => 'bg-info-soft text-info', 'text' => 'Order #KK-2840 shipped via JNE', 'time' => '42 minutes ago'],
            ['icon' => 'alert-triangle', 'tile' => 'bg-warning-soft text-warning', 'text' => 'Toraja Sapan 500 g is running low', 'time' => '2 hours ago'],
            ['icon' => 'userplus', 'tile' => 'bg-soft text-soft-foreground', 'text' => 'New customer: Putri Maharani', 'time' => '5 hours ago'],
            ['icon' => 'alert-circle', 'tile' => 'bg-danger-soft text-danger', 'text' => 'Order #KK-2837 cancelled', 'time' => 'Yesterday'],
        ],
    ],
];
