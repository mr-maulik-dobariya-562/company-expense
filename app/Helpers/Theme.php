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
            "master" => [
                "name" => __("Master"),
                "url" => '#',
                "icon" => 'fas fa-database', // Database icon for master settings
                "active" => isActive("master.*"),
                "menu" => "master",
                "permission" => ["view"],
                "children" => [
                    "work-category" => [
                        "name" => __("Work Category"),
                        "url" => route("master.work-category.index"),
                        "icon" => "fas fa-tasks", // Tasks icon
                        "active" => isActive("master.work-category.*"),
                        "menu" => "work-category",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "buyer" => [
                        "name" => __("Buyer"),
                        "url" => route("master.buyer.index"),
                        "icon" => "fas fa-shopping-cart", // Cart icon for buyers
                        "active" => isActive("master.buyer.*"),
                        "menu" => "buyer",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "site" => [
                        "name" => __("Site"),
                        "url" => route("master.site.index"),
                        "icon" => "fas fa-map-marker-alt", // Location marker icon
                        "active" => isActive("master.site.*"),
                        "menu" => "site",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "tds-category" => [
                        "name" => __("TDS Category"),
                        "url" => route("master.tds-category.index"),
                        "icon" => "fas fa-percent", // Percentage icon
                        "active" => isActive("master.tds-category.*"),
                        "menu" => "tds-category",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "type" => [
                        "name" => __("Type"),
                        "url" => route("master.type.index"),
                        "icon" => "fas fa-list", // List icon
                        "active" => isActive("master.type.*"),
                        "menu" => "type",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "ip" => [
                        "name" => __("IP Address"),
                        "url" => route("master.ip-address.index"),
                        "icon" => "fas fa-server",
                        "active" => isActive("master.ip-address.*"),
                        "menu" => "ip",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "item" => [
                        "name" => __("Item"),
                        "url" => route("master.item.index"),
                        "icon" => "fas fa-cart-plus", // Cart plus icon for buyers
                        "active" => isActive("master.item.*"),
                        "menu" => "item",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "party" => [
                        "name" => __("Party"),
                        "url" => route("master.party.index"),
                        "icon" => "fas fa-user", // User icon for buyers
                        "active" => isActive("master.party.*"),
                        "menu" => "party",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                ],
            ],
            "sale-purchase" => [
                "name" => __("Import Data"),
                "url" => route("sale-purchase.index"),
                "icon" => 'fas fa-file-import', // Import file icon
                "active" => isActive("sale-purchase.*"),
                "menu" => "sale-purchase",
                "permission" => ["view", "create"],
            ],
            "report" => [
                "name" => __("Report"),
                "url" => '#',
                "icon" => 'fas fa-file-alt', // Report document icon
                "active" => isActive( "report.sale-order.*", "report.pending-sale-order.*", "report.sale.*", "report.purchase.*"),
                "menu" => "report",
                "permission" => ["view"],
                "children" => [
                    "sale-order" => [
                        "name" => __("Sale Order Report"),
                        "url" => route("report.sale-order.index"),
                        "icon" => "fas fa-file-invoice-dollar", // Invoice icon for sales
                        "active" => isActive("report.sale-order.*"),
                        "menu" => "sale-order",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "pending-sale-order" => [
                        "name" => __("Pending Sale Order"),
                        "url" => route("report.pending-sale-order.index"),
                        "icon" => "fas fa-file-invoice-dollar", // Invoice icon for sales
                        "active" => isActive("report.pending-sale-order.*"),
                        "menu" => "pending-sale-order",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "sale" => [
                        "name" => __("Sale Report"),
                        "url" => route("report.sale.index"),
                        "icon" => "fas fa-chart-bar", // Bar chart for sales
                        "active" => isActive("report.sale.*"),
                        "menu" => "sale",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "master-sale" => [
                        "name" => __("Master Sale Report"),
                        "url" => route("report.master-sale.index"),
                        "icon" => "fas fa-chart-bar", // Bar chart for sales
                        "active" => isActive("report.master-sale.*"),
                        "menu" => "master-sale",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "purchase" => [
                        "name" => __("Purchase Report"),
                        "url" => route("report.purchase.index"),
                        "icon" => "fas fa-shopping-bag", // Bag icon for purchases
                        "active" => isActive("report.purchase.*"),
                        "menu" => "purchase",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "stock-report" => [
                        "name" => __("Stock Report"),
                        "url" => route("report.stock-report.index"),
                        "icon" => "fas fa-boxes", // Stockpile icon
                        "active" => isActive("report.stock-report.*"),
                        "menu" => "stock-report",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "sale-return" => [
                        "name" => __("Sale Return Report"),
                        "url" => route("report.sale-return.index"),
                        "icon" => "fas fa-undo-alt", // Undo icon for returns
                        "active" => isActive("report.sale-return.*"),
                        "menu" => "sale-return",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "purchase-return" => [
                        "name" => __("Purchase Return Report"),
                        "url" => route("report.purchase-return.index"),
                        "icon" => "fas fa-exchange-alt", // Exchange icon for purchase returns
                        "active" => isActive("report.purchase-return.*"),
                        "menu" => "purchase-return",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "sale-tds" => [
                        "name" => __("Sale TDS Report"),
                        "url" => route("report.sale-tds.index"),
                        "icon" => "fas fa-exchange-alt", // Exchange icon for purchase returns
                        "active" => isActive("report.sale-tds.*"),
                        "menu" => "sale-tds",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                    "purchase-tds" => [
                        "name" => __("Purchase TDS Report"),
                        "url" => route("report.purchase-tds.index"),
                        "icon" => "fas fa-exchange-alt", // Exchange icon for purchase returns
                        "active" => isActive("report.purchase-tds.*"),
                        "menu" => "purchase-tds",
                        "permission" => ["view", "create", "edit", "delete"]
                    ],
                ]
            ],
            "boq-report" => [
                "name" => __("BOQ Report"),
                "url" => route("boq"),
                "icon" => "fas fa-exchange-alt", // Exchange icon for purchase returns
                "active" => isActive("boq.*"),
                "menu" => "boq-report",
                "permission" => ["view", "create", "edit", "delete"]
            ],
            "pr-report" => [
                "name" => __("PR Report"),
                "url" => route("report.pr-report.index"),
                "icon" => "fas fa-shopping-cart", // Exchange icon for purchase returns
                "active" => isActive("report.pr-report.*"),
                "menu" => "pr-report",
                "permission" => ["view", "create", "edit", "delete"]
            ],
            "po-report" => [
                "name" => __("PO Report"),
                "url" => route("report.po-report.index"),
                "icon" => "fas fa-shopping-bag", // Exchange icon for purchase returns
                "active" => isActive("report.po-report.*"),
                "menu" => "po-report",
                "permission" => ["view", "create", "edit", "delete"]
            ],
            "log-activity" => [
                "name" => __("Log Activity"),
                "url" => route("log-activity"),
                "icon" => 'fas fa-history', // History log icon
                "active" => isActive("log-activity"),
                "menu" => "log-activity",
                "permission" => ["view"]
            ],
            "email-configuration" => [
                "name" => __("Email Configuration"),
                "url" => route("email-configuration.index"),
                "icon" => 'fas fa-envelope-open-text', // Email settings icon
                "active" => isActive("email-configuration.*"),
                "menu" => "email-configuration",
                "permission" => ["view", "create", "edit", "delete"]
            ],
            "manual-email" => [
                "name" => __("Manual Email"),
                "url" => route("manual-email.index"),
                "icon" => 'fas fa-envelope-open-text',
                "active" => isActive("manual-email.*"),
                "menu" => "manual-email",
                "permission" => ["view", "create", "edit", "delete"]
            ],
        ];
    }
}
