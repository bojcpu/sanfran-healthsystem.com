<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CheckupRecordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImmunizationRecordController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MaternalCareRecordController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchSuggestionController;
use App\Http\Controllers\SmsNotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/public-announcements/unread-count', [LandingController::class, 'unreadCount'])
    ->name('public-announcements.unread-count')
    ->middleware('throttle:60,1');
Route::get('/manifest.webmanifest', function () {
    $manifest = file_get_contents(resource_path('pwa/manifest.webmanifest'));

    return response($manifest, 200, [
        'Content-Type' => 'application/manifest+json',
    ]);
})->name('pwa.manifest');
Route::get('/push/public-key', [PushSubscriptionController::class, 'publicKey'])->name('push.public-key')->middleware('throttle:60,1');
Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])->name('push.subscribe')->middleware('throttle:10,1');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])->name('push.unsubscribe')->middleware('throttle:10,1');
Route::post('/push/unread-count', [PushSubscriptionController::class, 'unreadCount'])->name('push.unread-count')->middleware('throttle:30,1');
Route::post('/push/mark-read', [PushSubscriptionController::class, 'markRead'])->name('push.mark-read')->middleware('throttle:30,1');
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');

Route::middleware('auth')->group(function () {
    Route::get('search-suggestions/{source}/{context?}', SearchSuggestionController::class)
        ->name('search.suggestions')
        ->middleware('throttle:240,1');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('role:midwife');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count')
        ->middleware('role:midwife');
    Route::get('notifications/{notification}/open', [NotificationController::class, 'open'])
        ->name('notifications.open')
        ->middleware('role:midwife');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read')->middleware('role:midwife');

    Route::resource('users', UserController::class)->only(['index'])->middleware('role:system_admin,midwife');
    Route::resource('users', UserController::class)->except(['index', 'show', 'destroy'])->middleware('role:system_admin');
    Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate')->middleware('role:system_admin');
    Route::resource('patients', PatientController::class)->only(['index', 'show']);
    Route::resource('checkups', CheckupRecordController::class)->only(['index']);
    Route::resource('immunizations', ImmunizationRecordController::class)->only(['index']);
    Route::middleware('role:midwife,bhw')->group(function () {
        Route::resource('patients', PatientController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('checkups', CheckupRecordController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
        Route::resource('immunizations', ImmunizationRecordController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });
    Route::resource('maternal-care', MaternalCareRecordController::class)->only(['index']);
    Route::middleware('role:midwife')->group(function () {
        Route::get('maternal-care/create', [MaternalCareRecordController::class, 'create'])->name('maternal-care.create');
        Route::post('maternal-care', [MaternalCareRecordController::class, 'store'])->name('maternal-care.store');
        Route::get('maternal-care/{maternal_care}/edit', [MaternalCareRecordController::class, 'edit'])->name('maternal-care.edit');
        Route::put('maternal-care/{maternal_care}', [MaternalCareRecordController::class, 'update'])->name('maternal-care.update');
        Route::patch('maternal-care/{maternal_care}', [MaternalCareRecordController::class, 'update']);
        Route::delete('maternal-care/{maternal_care}', [MaternalCareRecordController::class, 'destroy'])->name('maternal-care.destroy');
    });
    Route::resource('announcements', AnnouncementController::class)->except(['show']);

    Route::get('sms', [SmsNotificationController::class, 'index'])->name('sms.index')->middleware('role:system_admin,midwife');
    Route::get('sms/create', [SmsNotificationController::class, 'create'])->name('sms.create')->middleware('role:system_admin,midwife');
    Route::post('sms', [SmsNotificationController::class, 'store'])->name('sms.store')->middleware('role:system_admin,midwife');

    Route::get('archive', [ArchiveController::class, 'index'])->name('archive.index');
    Route::post('archive/{type}/{id}/restore', [ArchiveController::class, 'restore'])->name('archive.restore');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}/preview', [ReportController::class, 'preview'])->name('reports.preview');
    Route::get('reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/{report}/excel', [ReportController::class, 'excel'])->name('reports.excel');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('role:system_admin,midwife');
});
