<?php

use App\Http\Controllers\{
    CommonController,
    DashboardController,
    User\RoleController,
    User\UserController,
    User\CustomerController,
    SalePurchaseController,
    Master\WorkCategoryController,
    Report\SaleOrderController,
    Report\PRReportController,
    Report\SaleController,
    Report\PurchaseController,
    Report\SaleTdsController,
    Report\PurchaseTdsController,
    Report\PoReportController,
    ReminderEmailsController
};
use App\Http\Controllers\EmailConfigurationController;
use App\Http\Controllers\Log_activityController;
use App\Http\Controllers\ManualEmailController;
use App\Http\Controllers\Master\BuyerController;
use App\Http\Controllers\Master\IPAddressController;
use App\Http\Controllers\Master\SiteController;
use App\Http\Controllers\Master\TdsCategoryController;
use App\Http\Controllers\Master\TypeController;
use App\Http\Controllers\Report\MasterSaleController;
use App\Http\Controllers\Report\PendingSaleOrderController;
use App\Http\Controllers\Report\PurchaseReturnController;
use App\Http\Controllers\Report\SaleReturnController;
use App\Http\Controllers\Report\StockController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\ItemController;
use App\Http\Controllers\Master\PartyController;
use App\Http\Controllers\BoqController;
use App\Http\Controllers\BoqApproveController;


Route::get('testing', [CommonController::class, 'testing'])->name('testing');
Route::get('/send-email', [EmailConfigurationController::class, 'sendEmail']);
Route::get('/quotation-print', function () {
    return view('order/quotation_print');
});
Route::middleware('auth')->group(function () {
    Route::post('getSite', [CommonController::class, "getSite"])->name('getSite');
    Route::post('getBuyer', [CommonController::class, "getBuyer"])->name('getBuyer');
    // Route::get('dataClean', [CommonController::class, "dataClean"])->name('dataClean');
    Route::post('log-export', [CommonController::class, "logExport"])->name('logExport');
    Route::get('/items/available-qty', [CommonController::class, 'availableQty'])->name('items.availableQty');

    /* Dashboard Routes */
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('get-production-sale-order-data', [DashboardController::class, 'getProductionSaleOrderData'])->name('getProductionSaleOrderData');
    Route::post('get-production-sale-order-report', [DashboardController::class, 'getProductionSaleOrderReport'])->name('getProductionSaleOrderReport');
    Route::post('production-sale-order-model', [DashboardController::class, 'productionSaleOrderModelData'])->name('productionSaleOrderModelData');
    Route::post('production-sale-order-form', [DashboardController::class, 'productionSaleOrderForm'])->name('productionSaleOrderForm');

    // BOQ
    Route::get('get-boq-modal-data', [BoqController::class, 'getBoqModalData'])->name('getBoqModalData');
    Route::post('/boq-store', [BoqController::class, 'storeBoq'])->name('boqStore');
    Route::get('boq', [BoqController::class, 'index'])->name('boq');
    Route::post('boq/get-list', [BoqController::class, "getList"])->name("boq.getList");
    Route::post('boq-model', [BoqController::class, 'getBoqModal'])->name('boq.boqModelData');
    Route::post('boqEditModel', [BoqController::class, 'getBoqEditModal'])->name('boq.boqEditModel');
    Route::post('updateBoq', [BoqController::class, 'updateBoq'])->name('boq.updateBoq');
    Route::get('approveBoq/{id}', [BoqController::class, 'approveBoq'])->name('boq.approveBoq');
    Route::post('rejectBoq', [BoqController::class, 'rejectBoq'])->name('boq.rejectBoq');
    Route::post('boq-form', [BoqController::class, 'BoqForm'])->name('BoqForm');

    // BOQ Approve
    Route::get('boq-approve', [BoqApproveController::class, 'index'])->name('boq-approve');
    Route::post('boq-updating/get-list', [BoqApproveController::class, "getUpdatingList"])->name("boq-updating.getList");
    Route::post('boq-updating-model', [BoqApproveController::class, 'boqUpdatingModelData'])->name('boq-updating.boqUpdatingModelData');
    Route::post('boq-approve/get-list', [BoqApproveController::class, "getApprovedList"])->name("boq-approve.getList");
    Route::post('boq-approve/final-approve/{id}', [BoqApproveController::class, 'finalApprove'])->name('boq-approve.finalApprove');
    Route::post('boq-approve/final-reject', [BoqApproveController::class, 'finalReject'])->name('boq-approve.finalReject');
    Route::post('boq-pr-updating/get-list', [BoqApproveController::class, 'getPrUpdatingList'])->name('boq-pr-updating.getList');
    Route::post('boq-pr-updating/detail-modal', [BoqApproveController::class, 'prUpdatingModelData'])->name('boq-pr-updating.prUpdatingModelData');
    Route::post('boq-approve/detail-modal', [BoqApproveController::class, 'boqApproveDetailModelData'])->name('boq-approve.detailModal');
    Route::post('boq-pr-approve/get-list', [BoqApproveController::class, "getPrApprovedList"])->name("boq-pr-approve.getList");
    Route::post('boq-pr-approve/final-approve/{id}', [BoqApproveController::class, 'PrApprove'])->name('boq-pr-approve.PrApprove');
    Route::post('boq-pr-approve/final-reject', [BoqApproveController::class, 'PrReject'])->name('boq-pr-approve.PrReject');
    Route::post('pr-approve/detail-modal', [BoqApproveController::class, 'PrApproveDetailModelData'])->name('pr-approve.detailModal');


    Route::get('get-sale-order-data', [DashboardController::class, 'getSaleOrderData'])->name('getSaleOrderData');
    Route::post('get-sale-order-report', [DashboardController::class, 'getSaleOrderReport'])->name('getSaleOrderReport');
    Route::post('sale-order-data-update', [DashboardController::class, 'saleOrderDataUpdate'])->name('saleOrderDataUpdate');
    Route::post('sale-order-model', [DashboardController::class, 'saleOrderModelData'])->name('saleOrderModelData');
    Route::post('sale-order-delete', [DashboardController::class, 'saleOrderDelete'])->name('saleOrderDelete');

    Route::get('get-sale-data', [DashboardController::class, 'getSaleData'])->name('getSaleData');
    Route::post('get-sale-report', [DashboardController::class, 'getSaleReport'])->name('getSaleReport');
    Route::post('sale-data-update', [DashboardController::class, 'saleDataUpdate'])->name('saleDataUpdate');
    Route::post('sale-mrn-data-update', [DashboardController::class, 'saleMrnDataUpdate'])->name('saleMrnDataUpdate');
    Route::post('sale-payment-data-update', [DashboardController::class, 'salePaymentDataUpdate'])->name('salePaymentDataUpdate');
    Route::post('sale-model', [DashboardController::class, 'saleModelData'])->name('saleModelData');
    Route::post('sale-delete', [DashboardController::class, 'saleDelete'])->name('saleDelete');

    Route::get('get-purchase-data', [DashboardController::class, 'getPurchaseData'])->name('getPurchaseData');
    Route::post('get-purchase-report', [DashboardController::class, 'getPurchaseReport'])->name('getPurchaseReport');
    Route::post('purchase-data-update', [DashboardController::class, 'purchaseDataUpdate'])->name('purchaseDataUpdate');
    Route::post('purchase-payment-data-update', [DashboardController::class, 'purchasePaymentDataUpdate'])->name('purchasePaymentDataUpdate');
    Route::post('purchase-model', [DashboardController::class, 'purchaseModelData'])->name('purchaseModelData');
    Route::post('purchase-delete', [DashboardController::class, 'purchaseDelete'])->name('purchaseDelete');
    /* Master Routes */
    Route::prefix('master')->name('master.')->group(function () {

        /* Work Category Routes */
        Route::resource('work-category', WorkCategoryController::class);
        Route::post('work-category/get-list', [WorkCategoryController::class, "getList"])->name("work-category.getList");
        Route::get('work-category/delete/{workCategory}', [WorkCategoryController::class, "destroy"])->name("work-category.delete");

        /* Buyer Routes */
        Route::resource('buyer', BuyerController::class);
        Route::post('buyer/get-list', [BuyerController::class, "getList"])->name("buyer.getList");
        Route::get('buyer/delete/{buyer}', [BuyerController::class, "destroy"])->name("buyer.delete");

        /* Site Routes */
        Route::resource('site', SiteController::class);
        Route::post('site/get-list', [SiteController::class, "getList"])->name("site.getList");
        Route::post('site/changeStatus', [SiteController::class, "changeStatus"])->name("site.changeStatus");

        Route::get('site/delete/{site}', [SiteController::class, "destroy"])->name("site.delete");

        /* TDS Category */
        Route::resource('tds-category', TdsCategoryController::class);
        Route::post('tds-category/get-list', [TdsCategoryController::class, "getList"])->name("tds-category.getList");
        Route::get('tds-category/delete/{tdsCategory}', [TdsCategoryController::class, "destroy"])->name("tds-category.delete");

        /* Type */
        Route::resource('type', TypeController::class);
        Route::post('type/get-list', [TypeController::class, "getList"])->name("type.getList");
        Route::get('type/delete/{type}', [TypeController::class, "destroy"])->name("type.delete");

        /* IP Address */
        Route::resource('ip-address', IPAddressController::class);
        Route::post('ip-address/get-list', [IPAddressController::class, "getList"])->name("ip-address.getList");
        Route::get('ip-address/delete/{ipAddress}', [IPAddressController::class, "destroy"])->name("ip-address.delete");

        /* Item */
        Route::resource('item', ItemController::class);
        Route::post('item/get-list', [ItemController::class, "getList"])->name("item.getList");
        Route::get('item/delete/{item}', [ItemController::class, "destroy"])->name("item.delete");

        /* Party */
        Route::resource('party', PartyController::class);
        Route::post('party/get-list', [PartyController::class, "getList"])->name("party.getList");
        Route::get('party/delete/{party}', [PartyController::class, "destroy"])->name("party.delete");
    });

    /* User Management Routes */
    Route::prefix('manage-user')->name("users.")->group(function () {
        /* Users */
        Route::resource('/', UserController::class)->except(['update']);
        Route::put('users/{user}', [UserController::class, "update"])->name("update");
        Route::post('get-list', [UserController::class, "getList"])->name("getList");
        Route::get('delete/{user}', [UserController::class, "destroy"])->name("user.delete");

        /* Roles & Permissions */
        Route::resource('role', RoleController::class);
        Route::post('role/get-list', [RoleController::class, "getList"])->name("role.getList");
        Route::get('role/delete/{role}', [RoleController::class, "destroy"])->name("role.delete");
    });

    /* Sale Purchase Routes */
    Route::prefix('sale-purchase')->name('sale-purchase.')->group(function () {
        Route::resource('/', SalePurchaseController::class);
        Route::get('send-email', [SalePurchaseController::class, "sendEmail"])->name("sendEmail");
    });

    /* Customers */
    Route::resource('customer', CustomerController::class);
    Route::post('customer/get-list', [CustomerController::class, "getList"])->name("customer.getList");
    Route::get('customer/delete/{customer}', [CustomerController::class, "destroy"])->name("customer.delete");
    Route::post('customer/coverPrint', [CustomerController::class, "coverPrint"])->name("customer.coverPrint");

    /* Report */
    Route::prefix('report')->name('report.')->group(function () {
        /* Sale Order */
        Route::resource('sale-order', SaleOrderController::class);
        Route::post('get-list', [SaleOrderController::class, "getList"])->name("getList");
        Route::post('sale-order-model', [SaleOrderController::class, 'saleOrderModelData'])->name('saleOrderModelData');

        /* Pending Sale Order */
        Route::resource('pending-sale-order', PendingSaleOrderController::class);
        Route::post('pending-sale-order/get-list', [PendingSaleOrderController::class, "getList"])->name("pending-sale-order.getList");

        /* Sale */
        Route::get('sale', [SaleController::class, "index"])->name("sale.index");
        Route::post('sale/get-list', [SaleController::class, "getList"])->name("sale.getList");
        Route::post('sale-model', [SaleController::class, 'saleModelData'])->name('saleModelData');

        /* Master Sale */
        Route::get('master-sale', [MasterSaleController::class, "index"])->name("master-sale.index");
        Route::post('master-sale/get-list', [MasterSaleController::class, "getList"])->name("master-sale.getList");

        /* Purchase */
        Route::get('purchase', [PurchaseController::class, "index"])->name("purchase.index");
        Route::post('purchase/get-list', [PurchaseController::class, "getList"])->name("purchase.getList");
        Route::post('purchase-model', [PurchaseController::class, 'purchaseModelData'])->name('purchaseModelData');

        /* Stock Report */
        Route::get('stock-report', [StockController::class, "index"])->name("stock-report.index");
        Route::post('stock-report/get-list', [StockController::class, "getList"])->name("stock-report.getList");

        /* Sale Return */
        Route::resource('sale-return', SaleReturnController::class);
        Route::post('sale-return/get-list', [SaleReturnController::class, "getList"])->name("sale-return.getList");
        Route::post('sale-return-model', [SaleReturnController::class, 'saleReturnModelData'])->name('saleReturnModelData');
        Route::post('sale-return-delete', [SaleReturnController::class, 'saleReturnDelete'])->name('saleReturnDelete');

        /* Purchase Return */
        Route::resource('purchase-return', PurchaseReturnController::class);
        Route::post('purchase-return/get-list', [PurchaseReturnController::class, "getList"])->name("purchase-return.getList");
        Route::post('purchase-return-model', [PurchaseReturnController::class, 'purchaseReturnModelData'])->name('purchaseReturnModelData');
        Route::post('purchase-return-delete', [PurchaseReturnController::class, 'purchaseReturnDelete'])->name('purchaseReturnDelete');

        /* Sale TDS Report */
        Route::resource('sale-tds', SaleTdsController::class);
        Route::post('sale-tds/get-list', [SaleTdsController::class, "getList"])->name("sale-tds.getList");
        Route::post('sale-tds-model', [SaleTdsController::class, 'saleTdsModelData'])->name('sale-tds.saleTdsModelData');

        /* Purchase TDS Report */
        Route::resource('purchase-tds', PurchaseTdsController::class);
        Route::post('purchase-tds/get-list', [PurchaseTdsController::class, "getList"])->name("purchase-tds.getList");
        Route::post('purchase-tds-model', [PurchaseTdsController::class, 'purchaseTdsModelData'])->name('purchase-tds.purchaseTdsModelData');

        /* PR Report */
        Route::resource('pr-report', PRReportController::class);
        Route::post('pr-report/get-list', [PRReportController::class, "getList"])->name("pr-report.getList");
        Route::post('pr-report/model', [PRReportController::class, 'modelData'])->name('pr-report.modelData');
        Route::post('pr-report/pr-model', [PRReportController::class, 'prModelData'])->name('pr-report.prModelData');
        Route::post('pr-report/pr-edit-model', [PRReportController::class, 'prEditModelData'])->name('pr-report.prEditModelData');
        Route::post('/pr-report/submit', [PRReportController::class, 'submitPr'])->name('pr-report.submit');
        Route::post('/pr-report/approve', [PRReportController::class, 'approvePr'])->name('pr-report.approve');
        Route::post('/pr-report/reject', [PRReportController::class, 'rejectPr'])->name('pr-report.reject');
        Route::post('/pr-report/prForm', [PRReportController::class, 'prForm'])->name('pr-report.prForm');


        /* PO Report */
        Route::resource('po-report', PoReportController::class);
        Route::post('po-report/get-list', [PoReportController::class, "getList"])->name("po-report.getList");
        Route::post('po-report/model', [PoReportController::class, 'modelData'])->name('po-report.modelData');
    });

    /* Log Activity Routes */
    Route::get('log-activity', [Log_activityController::class, 'index'])->name('log-activity');
    Route::post('log-activity/get-list', [Log_activityController::class, "getList"])->name("log-activity.getList");

    /* Email Configuration */
    Route::resource('email-configuration', EmailConfigurationController::class);
    Route::post('email-configuration/get-list', [EmailConfigurationController::class, "getList"])->name("email-configuration.getList");
    Route::get('email-configuration/delete/{emailConfiguration}', [EmailConfigurationController::class, "destroy"])->name("email-configuration.delete");
    Route::post("email-configuration/emailEnable", [EmailConfigurationController::class, "emailEnable"])->name("email-configuration.emailEnable");

    /* Manual Email */
    Route::get('manual-email', [ManualEmailController::class, 'index'])->name('manual-email.index');

});
/* Emails routes */


Route::get("/notify-mrn-emails", [ReminderEmailsController::class, 'sendMRNReminders'])->name("notify-mrn-emails");
Route::get("/notify-payment-emails", [ReminderEmailsController::class, 'sendPaymentReminders'])->name("notify-payment-emails");

Route::fallback(function () {
    return view('404-page');
});

/* Authentication Routes  */
require __DIR__ . '/auth.php';
