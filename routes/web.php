<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DevOpsController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebhooksController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\LoginSecurityController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/', [HomeController::class, 'base']);

Route::get('validate-pincode', [HomeController::class, 'checkPincodeAvailability'])->name('product.check.availability');

Route::prefix('devops')->middleware(['guest:devops'])->group(function () {
    Route::get('login', [DevOpsController::class, 'devOpsLogin'])->name('devops.login');
    Route::post('login', [DevOpsController::class, 'checkLogin'])->name('devops.login.submit');
});

Route::prefix('devops')->middleware(['auth:devops'])->group(function () {
    Route::get('dashboard', [DevOpsController::class, 'dashboard'])->name('devops.home');
});

Route::middleware(['auth','super_admin'])->group(function () {
    Route::resource('stores', SuperAdminController::class);
    Route::get('notifications', [SuperAdminController::class, 'sendIndex'])->name('real.time.notifications');
    Route::post('send/message', [SuperAdminController::class, 'sendMessage'])->name('send.web.message');

    //ElasticSearch Routes
    Route::get('elasticsearch/index', [HomeController::class, 'indexElasticSearch'])->name('elasticsearch.index');
    Route::post('search/store', [HomeController::class, 'searchStore'])->name('search.store');
});

Route::middleware(['two_fa', 'auth'])->group(function () {

    Route::get('dashboard', [HomeController::class, 'index'])->name('home');

    Route::middleware(['role:Admin', 'is_public_app'])->group(function () {
        Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
        Route::get('plan/buy/{id}', [BillingController::class, 'buyThisPlan'])->name('plan.buy');
        Route::any('shopify/rac/accept', [BillingController::class, 'acceptSubscriptionCharge'])->name('plan.accept');
        Route::get('consume/credits', [BillingController::class, 'consumeCredits'])->name('consume.credits');
    });

    Route::middleware(['two_fa', 'auth', 'is_private_app'])->group(function () {
        Route::get('subscriptions', [StripeController::class, 'index'])->name('subscriptions.index');
        Route::post('add.card.user', [StripeController::class, 'addCardToUser'])->name('add.card.user');
        Route::get('purchase/subscription/{id}', [StripeController::class, 'purchaseSubscription'])->name('purchase.subscription');
        Route::get('purchase/credits/{id}', [StripeController::class, 'purchaseOneTimeCredits'])->name('purchase.credits');
        Route::get('billing-portal', [StripeController::class, 'billingPortal'])->name('billing.portal');
    });

    Route::prefix('shopify')->group(function () {
            Route::get('products', [ShopifyController::class, 'products'])->name('shopify.products');
            Route::get('locations', [ShopifyController::class, 'locations'])->name('shopify.locations');
            Route::get('products-variants', [ShopifyController::class, 'product_variants'])->name('shopify.product_variants');
            Route::get('sync/locations', [ShopifyController::class, 'syncLocations'])->name('locations.sync');
            Route::get('sync/warehouse-products', [ShopifyController::class, 'syncWarehouseProducts'])->name('shopify.warehouse_products.sync');
            Route::get('products/create', [ProductsController::class, 'create'])->name('shopify.product.create');
          //  Route::get('products/print_barcode/{barcode}',[ProductsController::class, 'printBarcode'])->name('product.printBarcode');
            Route::get('add_variant', [ProductsController::class, 'getHTMLForAddingVariant'])->name('product.add.variant');
            Route::get('sync/products', [ShopifyController::class, 'syncProducts'])->name('shopify.products.sync');
            Route::post('products/publish', [ProductsController::class, 'publishProduct'])->name('shopify.product.publish');
            Route::get('changeProductAddToCartStatus', [ProductsController::class, 'changeProductAddToCartStatus'])->name('change.product.addToCart');

            Route::get('orders', [ShopifyController::class,'orders'])->name('shopify.orders');
            Route::get('orders/pending-payment', [ShopifyController::class,'pending_payment_orders'])->name('shopify.pending_payment');
            Route::get('sync/pending-orders', [ShopifyController::class, 'sync_pending_payment_orders'])->name('orders.sync_pending');
            Route::post('order/fulfill', [ShopifyController::class, 'fulfillOrder'])->name('shopify.order.fulfill');
            Route::post('order/fulfill-items', [ShopifyController::class, 'fulfillOrderItems'])->name('shopify.order.fulfillitems');
            Route::get('order/{id}', [ShopifyController::class, 'showOrder'])->name('shopify.order.show');
            Route::get('order/{id}/sync', [ShopifyController::class, 'syncOrder'])->name('shopify.order.sync');
            Route::get('sync/orders', [ShopifyController::class, 'syncOrders'])->name('orders.sync');

        Route::middleware('permission:write-customers|read-customers')->group(function () {
            Route::get('customers', [ShopifyController::class, 'customers'])->name('shopify.customers');
            Route::any('customerList', [ShopifyController::class, 'list'])->name('customers.list');
            Route::get('sync/customers', [ShopifyController::class, 'syncCustomers'])->name('customers.sync');
        });
        Route::get('profile', [SettingsController::class, 'profile'])->name('my.profile');
        Route::any('accept/charge', [ShopifyController::class, 'acceptCharge'])->name('accept.charge');

    });

    Route::post('cash-register/close/{cashRegister}/store', [CashRegisterController::class,'PostCloseRegister'])->name('register.close.post'); // close register
    Route::get('sales/lims_product_search', [ShopifyController::class,'limsProductSearch'])->name('product_sale.search');
    Route::get('sales/getproduct/{id}', [ShopifyController::class,'getProduct'])->name('sale.getproduct');
    Route::post('customer/storeCustomer', [CustomerController::class,'storeCustomer'])->name('customer.createNew');
    Route::get('get_customer_sale/{phone}', [CustomerController::class,'getCustomerSale'])->name('sale.getCustomerSale');
    Route::resource('customer', CustomerController::class);
    Route::get('cash-register/close/{id}', [CashRegisterController::class,'getCloseRegister'])->name('register.close');
    Route::get('open-register',[CashRegisterController::class,'create'])->name('open.register'); // open register
	Route::post('open-register/store', [CashRegisterController::class,'store'])->name('open.register.store'); // open register store
    Route::get('pos', [ShopifyController::class,'pos'])->name('sale.pos');
    Route::post('orders/store', [ShopifyController::class,'store_order'])->name('orders.store');
    Route::any('all-orders', [ShopifyController::class,'all_orders'])->name('prepare.all');
    Route::any('all-sales', [ShopifyController::class,'all_sales'])->name('sales.all');
    Route::any('reports/staff', [ShopifyController::class,'staff_report'])->name('reports.staff');
    Route::any('reports/cash-registers', [ShopifyController::class,'cash_register_report'])->name('reports.cash_registers');
    Route::any('reports/stock', [ShopifyController::class,'stock_report'])->name('reports.stock');
    Route::any('new-orders', [ShopifyController::class,'new_orders'])->name('prepare.new');
    Route::any('prepared-orders', [ShopifyController::class,'prepared_orders'])->name('prepare.order');
    Route::any('hold-orders', [ShopifyController::class,'hold_orders'])->name('prepare.hold');
    Route::post('/bulk_order_assign', [ShopifyController::class,'bulk_order_assign'])->name('bulk-order-assign');
    Route::any('/staff/export', [ShopifyController::class,'export_staff_report'])->name('staff.export');
    Route::get('order/{id}/prepare', [ShopifyController::class, 'prepareOrder'])->name('shopify.order.prepare');
    Route::any('review-orders/{id}', [ShopifyController::class,'review_order'])->name('prepare.review');
    Route::any('reviewed-orders', [ShopifyController::class,'reviewed_orders'])->name('prepare.reviewed');
    Route::any('generate-invoice/{id}', [ShopifyController::class,'generate_invoice'])->name('prepare.generate-invoice');
    Route::any('generate-return-invoice/{id}', [ShopifyController::class,'generate_return_invoice'])->name('prepare.generate-return-invoice');
    Route::any('order-history/{id}', [ShopifyController::class,'order_history'])->name('prepare.order-history');
    Route::post('review-orders', [ShopifyController::class,'review_post'])->name('prepare.review.post');
    Route::post('bulk-order-shipped', [ShopifyController::class,'bulk_order_shipped'])->name('bulk-order-shipped');
    Route::post('bulk-returns-shipped', [ShopifyController::class,'bulk_returns_shipped'])->name('bulk-returns-shipped');
    Route::get('pickups', [ShopifyController::class, 'pickups'])->name('pickups.index');
    Route::get('return-pickups', [ShopifyController::class, 'return_pickups'])->name('return-pickups.index');
    Route::any('hold-products', [ShopifyController::class,'hold_products'])->name('prepare.hold-products');
    Route::post('/orders/update_delivery_status', [ShopifyController::class,'update_delivery_status'])->name('orders.update_delivery_status');
    Route::get('/orders/update_payment_status/{id?}', [ShopifyController::class,'update_payment_status'])->name('orders.update_payment_status');
    Route::post('/orders/resync', [ShopifyController::class,'resync_order'])->name('orders.resync');
    Route::post('/inventory/import', [ShopifyController::class,'import_inventory'])->name('inventory.import');
    Route::any('inventory-transfers', [ShopifyController::class,'inventory_transfers'])->name('inventories.index');
    Route::any('inventory-transfers/{id}', [ShopifyController::class,'show_inventory_transfers'])->name('inventories.show');
    Route::post('/orders/return', [ShopifyController::class,'return_order'])->name('orders.return');
    Route::any('returned-orders', [ShopifyController::class,'returned_orders'])->name('orders.returned');
    Route::any('returned-products-report', [ShopifyController::class,'returned_products_report'])->name('products.returned');
    Route::any('warehouse-products', [ShopifyController::class,'warehouse_products'])->name('shopify.product_warehouse');
    Route::any('returned-orders-report', [ShopifyController::class,'returned_orders_report'])->name('reports.returned');
    Route::any('cancelled-orders', [ShopifyController::class,'cancelled_Orders'])->name('prepare.cancelled-orders');
    Route::any('resynced-orders', [ShopifyController::class,'resynced_orders'])->name('prepare.resynced-orders');
    Route::get('export-hold-products', [ShopifyController::class,'export_hold_products'])->name('export-hold-products');

    Route::get('settings', [SettingsController::class, 'settings'])->name('settings');
    Route::prefix('two_factor_auth')->group(function () {
        Route::get('/', [LoginSecurityController::class, 'show2faForm'])->name('show2FASettings');
        Route::post('generateSecret', [LoginSecurityController::class, 'generate2faSecret'])->name('generate2faSecret');
        Route::post('enable2fa', [LoginSecurityController::class, 'enable2fa'])->name('enable2fa');
        Route::post('disable2fa', [LoginSecurityController::class, 'disable2fa'])->name('disable2fa');
        Route::middleware('two_fa')->post('/2faVerify', function () { return redirect(URL()->previous()); })->name('2faVerify');
    });

    Route::middleware(['permission:write-members|read-members', 'is_public_app'])->group(function () {
        Route::resource('members', TeamController::class);
    });

});

// /shopify/auth
Route::prefix('shopify/auth')->group(function () {
    Route::get('/', [InstallationController::class, 'startInstallation']);
    Route::get('redirect', [InstallationController::class, 'handleRedirect'])->name('app_install_redirect');
    Route::get('complete', [InstallationController::class, 'completeInstallation'])->name('app_install_complete');
});

Route::prefix('webhook')->group(function () {
    Route::any('order/created', [WebhooksController::class, 'orderCreated']);
    Route::any('order/updated', [WebhooksController::class, 'orderUpdated']);
    Route::any('product/created', [WebhooksController::class, 'productCreated']);
    Route::any('app/uninstall', [WebhooksController::class, 'appUninstalled']);
    Route::any('shop/updated', [WebhooksController::class, 'shopUpdated']);
});

//Testing scripts
Route::get('configure/webhooks/{id}', [WebhooksController::class, 'configureWebhooks']);
Route::get('delete/webhooks/{id}', [WebhooksController::class, 'deleteWebhooks']);
Route::get('test/docker', [HomeController::class, 'testDocker']);
Route::get('listUsers', [HomeController::class, 'listUsers']);

//Fulfillment Service Routes
Route::prefix('service_callback')->group(function () {
    Route::any('/', [HomeController::class, 'service_callback'])->name('service_callback');
    Route::any('fulfillment_order_notification', [HomeController::class, 'receiveFulfillmentNotification'])->name('receive.fulfillment.notification');
    Route::any('fetch_tracking_numbers ', [HomeController::class, 'fetchTrackingNumbers'])->name('fetch.tracking.numbers');
    Route::any('fetch_stock', [HomeController::class, 'fetchStock'])->name('fetch.stock');
});

//GDPR endpoints
Route::prefix('gdpr')->group(function () {
    Route::any('webhooks/customer_data', [WebhooksController::class, 'returnCustomerData']);
    Route::any('webhooks/customer_data_delete', [WebhooksController::class, 'deleteCustomerData']);
    Route::any('webhooks/shop_data_delete', [WebhooksController::class, 'deleteShopData']);
});
