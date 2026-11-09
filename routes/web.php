<?php

use App\Http\Controllers\AdminVerificationController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JournalistController;
use App\Http\Controllers\MediaSubmissionController;
use App\Http\Controllers\NewsClippingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PressReleaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    return redirect()->route($user->isAdminArea() ? 'admin.dashboard' : ($user->role === 'jurnalis' ? 'journalist.dashboard' : 'media.dashboard'));
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:media'])->prefix('media')->name('media.')->group(function () {
    Route::get('/dashboard', [MediaSubmissionController::class, 'dashboard'])->name('dashboard');
    Route::get('/pengajuan', [MediaSubmissionController::class, 'create'])->name('submissions.create');
    Route::post('/pengajuan', [MediaSubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/pengajuan/{submission}/edit', [MediaSubmissionController::class, 'edit'])->name('submissions.edit');
    Route::patch('/pengajuan/{submission}', [MediaSubmissionController::class, 'update'])->name('submissions.update');
    Route::get('/pengajuan/{submission}/dokumen/{document}', [MediaSubmissionController::class, 'download'])->name('documents.download');
    Route::middleware('media.approved')->group(function (): void {
        Route::get('/kontrak', [ContractController::class, 'mediaIndex'])->name('contracts.index');
        Route::get('/kontrak/{contract}/dokumen', [ContractController::class, 'download'])->name('contracts.download');
        Route::get('/kliping', [NewsClippingController::class, 'index'])->name('clippings.index');
        Route::get('/kliping/buat', [NewsClippingController::class, 'create'])->name('clippings.create');
        Route::post('/kliping', [NewsClippingController::class, 'store'])->name('clippings.store');
        Route::get('/siaran-pers', [PressReleaseController::class, 'mediaIndex'])->name('press-releases.index');
        Route::get('/e-billing', [InvoiceController::class, 'mediaIndex'])->name('invoices.index');
        Route::get('/e-billing/buat', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/e-billing', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/e-billing/{invoice}/dokumen', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::get('/jurnalis', [JournalistController::class, 'index'])->name('journalists.index');
        Route::get('/jurnalis/buat', [JournalistController::class, 'create'])->name('journalists.create');
        Route::post('/jurnalis', [JournalistController::class, 'store'])->name('journalists.store');
    });
});

Route::middleware(['auth', 'role:jurnalis'])->prefix('jurnalis')->name('journalist.')->group(function () {
    Route::get('/dashboard', [PressReleaseController::class, 'mediaIndex'])->name('dashboard');
    Route::get('/siaran-pers', [PressReleaseController::class, 'mediaIndex'])->name('press-releases.index');
});

Route::middleware(['auth', 'role:super_admin,admin,admin_media,admin_keuangan'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminVerificationController::class, 'index'])->name('dashboard');
    Route::middleware('permission:contracts.manage')->group(function (): void {
        Route::get('/kontrak', [ContractController::class, 'adminIndex'])->name('contracts.index');
        Route::get('/kontrak/buat', [ContractController::class, 'create'])->name('contracts.create');
        Route::post('/kontrak', [ContractController::class, 'store'])->name('contracts.store');
    });
    Route::get('/kontrak/{contract}/dokumen', [ContractController::class, 'download'])->name('contracts.download');
    Route::middleware('permission:clipping.review')->group(function (): void {
        Route::get('/kliping', [NewsClippingController::class, 'adminIndex'])->name('clippings.index');
        Route::patch('/kliping/{clipping}', [NewsClippingController::class, 'update'])->name('clippings.update');
    });
    Route::middleware('permission:press.manage')->group(function (): void {
        Route::get('/siaran-pers', [PressReleaseController::class, 'adminIndex'])->name('press-releases.index');
        Route::get('/siaran-pers/buat', [PressReleaseController::class, 'create'])->name('press-releases.create');
        Route::post('/siaran-pers', [PressReleaseController::class, 'store'])->name('press-releases.store');
        Route::patch('/siaran-pers/{pressRelease}/publish', [PressReleaseController::class, 'publish'])->name('press-releases.publish');
    });
    Route::middleware('role:super_admin,admin,admin_keuangan')->group(function (): void {
        Route::get('/e-billing', [InvoiceController::class, 'financeIndex'])->name('invoices.index');
        Route::patch('/e-billing/{invoice}', [InvoiceController::class, 'review'])->name('invoices.review');
        Route::post('/e-billing/{invoice}/pembayaran', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/e-billing/{invoice}/dokumen', [InvoiceController::class, 'download'])->name('invoices.download');
    });
    Route::middleware('permission:audit.view')->get('/audit-log', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/pengaturan/kata-kunci', [SystemSettingController::class, 'keywords'])->name('settings.keywords');
    Route::put('/pengaturan/kata-kunci', [SystemSettingController::class, 'updateKeywords'])->name('settings.keywords.update');
    Route::get('/laporan/kinerja.csv', [ReportController::class, 'clippings'])->name('reports.clippings');
    Route::get('/pengajuan/{submission}', [AdminVerificationController::class, 'show'])->name('submissions.show');
    Route::patch('/pengajuan/{submission}', [AdminVerificationController::class, 'update'])->name('submissions.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifikasi/{notification}', [NotificationController::class, 'update'])->name('notifications.update');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
