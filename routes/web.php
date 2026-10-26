<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\StayController;
use App\Http\Controllers\StayBillingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FacilityPackageController;
use App\Http\Controllers\FacilityBookingController;
use App\Http\Controllers\FacilityBookingBillingController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\KitchenOrderBillingController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\BookingRequestController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [FrontController::class, 'home'])->name('home');
Route::get('/book', [FrontController::class, 'book'])->name('book');
Route::post('/book', [FrontController::class, 'submitBooking'])
    ->middleware('throttle:5,1')
    ->name('book.submit');
Route::get('/book/thanks', [FrontController::class, 'thanks'])->name('book.thanks');
Route::get('/about', [FrontController::class, 'about'])->name('about');
Route::get('/contact', [FrontController::class, 'contact'])->name('contact');
Route::post('/contact', [FrontController::class, 'submitContact'])
    ->middleware('throttle:5,1')
    ->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
| Every module is limited to the roles that work in it. Admins pass every
| role check (see EnsureUserHasRole and the Gate::before in AppServiceProvider).
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Any signed-in user
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/data', [ProfileController::class, 'getData'])->name('profile.data');

    /*
    |--------------------------------------------------------------------------
    | Front Office
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:manager,receptionist')->group(function () {
        Route::resource('guests', GuestController::class)->except(['create', 'edit', 'destroy']);
        Route::delete('guests/{guest}', [GuestController::class, 'destroy'])
            ->middleware('role:manager')
            ->name('guests.destroy');

        // Bookings
        Route::post('stays/quote', [StayController::class, 'quote'])->name('stays.quote');
        Route::resource('stays', StayController::class)->except(['destroy']);
        Route::post('stays/{stay}/check-in', [StayController::class, 'checkIn'])->name('stays.check-in');
        Route::post('stays/{stay}/check-out', [StayController::class, 'checkOut'])->name('stays.check-out');
        Route::post('stays/{stay}/cancel', [StayController::class, 'cancel'])->name('stays.cancel');

        // Requests from the public website
        Route::get('booking-requests', [BookingRequestController::class, 'index'])->name('booking-requests.index');
        Route::post('booking-requests/{bookingRequest}/convert', [BookingRequestController::class, 'convert'])->name('booking-requests.convert');
        Route::post('booking-requests/{bookingRequest}/decline', [BookingRequestController::class, 'decline'])->name('booking-requests.decline');
    });

    // Stay folio: front desk takes payments; finance too
    Route::middleware('role:manager,receptionist,finance')->group(function () {
        Route::post('stays/{stay}/payments', [StayBillingController::class, 'storePayment'])->name('stays.payments.store');
        Route::get('stays/{stay}/invoice', [StayBillingController::class, 'invoice'])->name('stays.invoice');
        Route::get('stays/{stay}/payments/{payment}/receipt', [StayBillingController::class, 'receipt'])->name('stays.receipt');
    });
    Route::post('stays/{stay}/charges', [StayBillingController::class, 'storeCharge'])
        ->middleware('role:manager,receptionist,finance')
        ->name('stays.charges.store');
    Route::middleware('role:manager,finance')->group(function () {
        Route::post('stays/{stay}/payments/{payment}/void', [StayBillingController::class, 'voidPayment'])->name('stays.payments.void');
        Route::delete('stays/{stay}/charges/{item}', [StayBillingController::class, 'destroyCharge'])->name('stays.charges.destroy');
        Route::post('stays/{stay}/discount', [StayBillingController::class, 'updateDiscount'])->name('stays.discount');
    });

    Route::middleware('role:manager,receptionist')->group(function () {
        Route::resource('room-types', RoomTypeController::class);
    });

    // Conference & events: front office books, managers set up venues and packages
    Route::middleware('role:manager,receptionist')->group(function () {
        Route::resource('facilities', FacilityController::class)->only(['index', 'show']);
        Route::resource('events', FacilityBookingController::class)->only(['index', 'show', 'store', 'update']);
        Route::post('events/{event}/status', [FacilityBookingController::class, 'updateStatus'])->name('events.status');
    });
    Route::middleware('role:manager')->group(function () {
        Route::resource('facilities', FacilityController::class)->only(['store', 'update', 'destroy']);
        Route::resource('facility-packages', FacilityPackageController::class)->only(['store', 'update', 'destroy']);
    });

    // Event folio: same split as the stay folio
    Route::middleware('role:manager,receptionist,finance')->group(function () {
        Route::post('events/{event}/payments', [FacilityBookingBillingController::class, 'storePayment'])->name('events.payments.store');
        Route::post('events/{event}/charges', [FacilityBookingBillingController::class, 'storeCharge'])->name('events.charges.store');
        Route::get('events/{event}/invoice', [FacilityBookingBillingController::class, 'invoice'])->name('events.invoice');
        Route::get('events/{event}/payments/{payment}/receipt', [FacilityBookingBillingController::class, 'receipt'])->name('events.receipt');
    });
    Route::middleware('role:manager,finance')->group(function () {
        Route::post('events/{event}/payments/{payment}/void', [FacilityBookingBillingController::class, 'voidPayment'])->name('events.payments.void');
        Route::delete('events/{event}/charges/{item}', [FacilityBookingBillingController::class, 'destroyCharge'])->name('events.charges.destroy');
        Route::post('events/{event}/discount', [FacilityBookingBillingController::class, 'updateDiscount'])->name('events.discount');
    });

    // Rooms: front office + housekeeping can view and change status; managers manage
    Route::middleware('role:manager,receptionist,housekeeping')->group(function () {
        Route::get('rooms/availability', [RoomController::class, 'availability'])->name('rooms.availability');
        Route::resource('rooms', RoomController::class)->only(['index', 'show']);
        Route::patch('rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status');
    });
    Route::resource('rooms', RoomController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('role:manager');
    Route::delete('stays/{stay}', [StayController::class, 'destroy'])
        ->middleware('role:manager')
        ->name('stays.destroy');

    /*
    |--------------------------------------------------------------------------
    | Operations
    |--------------------------------------------------------------------------
    */
    // Tasks: every staff role; what each person sees is narrowed in the controller
    Route::middleware('role:manager,receptionist,housekeeping,kitchen,procurement,hr,finance')->group(function () {
        Route::post('tasks/daily-cleaning', [TaskController::class, 'generateDailyCleaning'])
            ->middleware('role:manager,receptionist,housekeeping')
            ->name('tasks.daily-cleaning');
        Route::resource('tasks', TaskController::class)->only(['index', 'show', 'store', 'update']);
        Route::post('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
        Route::post('tasks/{task}/checklist', [TaskController::class, 'toggleChecklist'])->name('tasks.checklist');
        Route::post('tasks/{task}/comments', [TaskController::class, 'comment'])->name('tasks.comments.store');
        Route::post('tasks/{task}/items', [TaskController::class, 'useItem'])->name('tasks.items.store');
        Route::post('tasks/{task}/stop-repeating', [TaskController::class, 'stopRepeating'])->name('tasks.stop-repeating');
    });
    Route::middleware('role:manager')->group(function () {
        Route::post('task-types', [TaskController::class, 'storeType'])->name('task-types.store');
        Route::put('task-types/{taskType}', [TaskController::class, 'updateType'])->name('task-types.update');
    });

    // Restaurant & room service: kitchen and front desk take orders; kitchen and managers run the menu
    Route::prefix('kitchen')->name('kitchen.')->group(function () {
        Route::middleware('role:manager,kitchen,receptionist')->group(function () {
            Route::get('/', [KitchenController::class, 'index'])->name('index');
            Route::get('orders', [KitchenController::class, 'orders'])->name('orders.index');
            Route::post('orders', [KitchenController::class, 'store'])->name('orders.store');
            Route::get('orders/{order}', [KitchenController::class, 'show'])->name('orders.show');
            Route::post('orders/{order}/status', [KitchenController::class, 'updateStatus'])->name('orders.update-status');
        });

        Route::middleware('role:manager,kitchen')->prefix('menu')->name('menu.')->group(function () {
            Route::get('/', [MenuController::class, 'index'])->name('index');
            Route::post('categories', [MenuController::class, 'storeCategory'])->name('categories.store');
            Route::put('categories/{category}', [MenuController::class, 'updateCategory'])->name('categories.update');
            Route::delete('categories/{category}', [MenuController::class, 'destroyCategory'])->name('categories.destroy');
            Route::post('items', [MenuController::class, 'storeItem'])->name('items.store');
            Route::put('items/{item}', [MenuController::class, 'updateItem'])->name('items.update');
            Route::delete('items/{item}', [MenuController::class, 'destroyItem'])->name('items.destroy');
        });

        // Walk-in order folio: same split as the stay folio
        Route::middleware('role:manager,kitchen,receptionist,finance')->group(function () {
            Route::post('orders/{order}/payments', [KitchenOrderBillingController::class, 'storePayment'])->name('orders.payments.store');
            Route::post('orders/{order}/charges', [KitchenOrderBillingController::class, 'storeCharge'])->name('orders.charges.store');
            Route::get('orders/{order}/invoice', [KitchenOrderBillingController::class, 'invoice'])->name('orders.invoice');
            Route::get('orders/{order}/payments/{payment}/receipt', [KitchenOrderBillingController::class, 'receipt'])->name('orders.receipt');
        });
        Route::middleware('role:manager,finance')->group(function () {
            Route::post('orders/{order}/payments/{payment}/void', [KitchenOrderBillingController::class, 'voidPayment'])->name('orders.payments.void');
            Route::delete('orders/{order}/charges/{item}', [KitchenOrderBillingController::class, 'destroyCharge'])->name('orders.charges.destroy');
            Route::post('orders/{order}/discount', [KitchenOrderBillingController::class, 'updateDiscount'])->name('orders.discount');
        });
    });

    // Procurement: buyers draft and receive; a manager approves (checked in the controller); finance can look
    Route::middleware('role:manager,procurement,kitchen,finance')->group(function () {
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'show']);
        Route::resource('suppliers', SupplierController::class)->only(['index', 'show']);
    });
    Route::middleware('role:manager,procurement,kitchen')->group(function () {
        Route::post('purchase-orders/reorder', [PurchaseOrderController::class, 'reorder'])->name('purchase-orders.reorder');
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['store', 'update']);
        Route::post('purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.status');
        Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    });
    Route::resource('suppliers', SupplierController::class)->only(['store', 'update'])->middleware('role:manager,procurement');

    // Store: what each role may do is checked in the controller
    Route::middleware('role:manager,procurement,kitchen,housekeeping')->group(function () {
        Route::get('inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');
        Route::resource('inventory', InventoryController::class)->only(['index', 'show', 'store', 'update']);
        Route::post('inventory/{inventory}/move', [InventoryController::class, 'move'])->name('inventory.move');
    });

    /*
    |--------------------------------------------------------------------------
    | Finance
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:manager,finance')->group(function () {
        Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::post('expenses/{expense}/void', [ExpenseController::class, 'void'])->name('expenses.void');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Human Resource
    |--------------------------------------------------------------------------
    */
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::middleware('role:manager,hr')->group(function () {
            Route::redirect('/', '/hr/staff')->name('index');

            Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
            Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
            Route::get('staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
            Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
            Route::post('staff/{staff}/terminate', [StaffController::class, 'terminate'])->name('staff.terminate');
            Route::post('departments', [StaffController::class, 'storeDepartment'])->name('departments.store');
            Route::delete('departments/{department}', [StaffController::class, 'destroyDepartment'])->name('departments.destroy');

            Route::get('rota', [RotaController::class, 'index'])->name('rota.index');
            Route::post('rota/assign', [RotaController::class, 'assign'])->name('rota.assign');
            Route::post('rota/copy-week', [RotaController::class, 'copyWeek'])->name('rota.copy-week');
            Route::post('shifts', [RotaController::class, 'storeShift'])->name('shifts.store');
            Route::put('shifts/{shift}', [RotaController::class, 'updateShift'])->name('shifts.update');
            Route::delete('shifts/{shift}', [RotaController::class, 'destroyShift'])->name('shifts.destroy');

            Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
            Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
            Route::post('leave/{leave}/decide', [LeaveController::class, 'decide'])->name('leave.decide');

            Route::post('payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
            Route::put('payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
        });

        // Finance sees payroll and pays it; who may approve / pay is checked per action
        Route::middleware('role:manager,hr,finance')->group(function () {
            Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
            Route::post('payroll/status', [PayrollController::class, 'updateStatus'])->name('payroll.status');
            Route::get('payroll/{payroll}/payslip', [PayrollController::class, 'payslip'])->name('payroll.payslip');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Administration (admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/', [SystemController::class, 'index'])->name('index');
            Route::put('/update', [SystemController::class, 'update'])->name('update');
            Route::post('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
            Route::post('/toggle-maintenance', [SystemController::class, 'toggleMaintenance'])->name('toggle-maintenance');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Template pages still linked from the UI
    |--------------------------------------------------------------------------
    */
    Route::view('/calendar', 'calendar')->name('calendar');
});

/*
|--------------------------------------------------------------------------
| Laravel Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';
