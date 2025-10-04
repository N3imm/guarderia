<?php
// includes/menu_helpers.php

function getMenuItems($role) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    
    $menus = [
        'admin' => [
            [
                'title' => 'Dashboard',
                'icon' => 'tachometer-alt',
                'url' => ADMIN_URL . 'dashboard.php'
            ],
            [
                'title' => 'Mascotas',
                'icon' => 'dog',
                'url' => ADMIN_URL . 'pets.php'
            ],
            [
                'title' => 'Citas',
                'icon' => 'calendar-alt',
                'url' => ADMIN_URL . 'appointments.php'
            ],
            [
                'title' => 'Visitas',
                'icon' => 'history',
                'url' => ADMIN_URL . 'visits.php'
            ],
            [
                'title' => 'Clientes',
                'icon' => 'users',
                'url' => ADMIN_URL . 'clients.php'
            ]
        ],
        'client' => [
            [
                'title' => 'Mi Panel',
                'icon' => 'tachometer-alt',
                'url' => CLIENT_URL . 'dashboard.php'
            ],
            [
                'title' => 'Mis Mascotas',
                'icon' => 'paw',
                'url' => CLIENT_URL . 'my_pets.php'
            ],
            [
                'title' => 'Mis Citas',
                'icon' => 'calendar-alt',
                'url' => CLIENT_URL . 'appointments.php'
            ],
            [
                'title' => 'Historial',
                'icon' => 'history',
                'url' => CLIENT_URL . 'visits.php'
            ]
        ]
    ];

    return array_map(function($item) use ($currentPage) {
        $item['active'] = (basename($item['url']) === $currentPage);
        return $item;
    }, $menus[$role] ?? []);
}

function renderMenuItem($item) {
    $activeClass = $item['active'] ? 'active' : '';
    return sprintf(
        '<li class="nav-item">
            <a class="nav-link %s" href="%s">
                <i class="fas fa-%s me-1"></i>%s
            </a>
        </li>',
        $activeClass,
        htmlspecialchars($item['url']),
        htmlspecialchars($item['icon']),
        htmlspecialchars($item['title'])
    );
}

function getUserProfileMenu($firstName, $lastName = '', $role = '') {
    return [
        'header' => [
            'name' => $firstName . ($lastName ? ' ' . $lastName : ''),
            'role' => $role
        ],
        'items' => [
            [
                'title' => 'Mi Perfil',
                'icon' => 'user',
                'url' => BASE_URL . 'profile.php', // URL corregida
                'class' => ''
            ],
            [
                'title' => 'Configuración',
                'icon' => 'cog',
                'url' => BASE_URL . 'settings.php', // URL corregida
                'class' => ''
            ],
            [
                'title' => 'Cerrar Sesión',
                'icon' => 'sign-out-alt',
                'url' => BASE_URL . 'logout.php', // URL corregida
                'class' => 'text-danger'
            ]
        ]
    ];
}
