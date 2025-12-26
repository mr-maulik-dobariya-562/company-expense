<?php

namespace App\Helpers;

class Theme
{
    /**
     * Create a new class instance.
     */
    public static function getMenu()
    {
        return self::getSideBar();
    }

    public static function getSideBar()
    {
        return [
            "dashboard" => [
                "name" => __("Dashboard"),
                "url" => route("dashboard"),
                "icon" => 'fas fa-chart-line', // Better for dashboards
                "active" => isActive("dashboard"),
                "menu" => "dashboard",
                "permission" => ["view"]
            ],
            'users' => [
                'name' => __('Manage Users'),
                "url" => '#',
                "icon" => 'fas fa-users', // Group user icon
                "active" => isActive("users.*", "customer.*"),
                "permission" => ["view", "create"],
                "children" => [
                    "users" => [
                        "name" => __("Users"),
                        "url" => route("users.index"),
                        "icon" => "fas fa-user", // Single user icon
                        "active" => isActive("users.index"),
                        "menu" => "users",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "roles" => [
                        "name" => __("Roles & Permissions"),
                        "url" => route("users.role.index"),
                        "icon" => "fas fa-user-shield", // Security shield icon for roles
                        "active" => isActive("users.role.*"),
                        "menu" => "role",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                ]
            ],
            "expenses" => [
                "name" => __("Expenses"),
                "url" => route("master.expense.index"),
                "icon" => "fas fa-wallet", // Wallet icon for expenses
                "active" => isActive("expense.*"),
                "menu" => "expense",
                "permission" => ["view", "create", "edit", "delete"]
            ],
        ];
    }
}
