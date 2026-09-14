<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaceholderController;
use App\Livewire\Approval\ApprovalIndex;
use App\Livewire\Erf\ErfCreate;
use App\Livewire\Erf\ErfIndex;
use App\Livewire\Erf\ErfShow;
use App\Livewire\Ga\GaCreate;
use App\Livewire\Ga\GaIndex;
use App\Livewire\Ga\GaShow;
use App\Livewire\Master\JabatanTtfManager;
use App\Livewire\Master\KalenderKerjaManager;
use App\Livewire\Master\KaryawanManager;
use App\Livewire\Master\UserManager;
use App\Livewire\Setting\GroupAccessManager;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// ── Auth ──────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// ── Aplikasi (butuh login) ───────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/approval-saya', ApprovalIndex::class)->name('approval.index');

    // Placeholder menu untuk modul yang belum dikembangkan
    Route::get('/tech', fn () => (new PlaceholderController)->show('Request Tech', 'Modul Portal Request Tech akan diintegrasikan menyusul. Untuk saat ini silakan gunakan sistem Portal Request Tech yang sudah berjalan.'))->name('placeholder.tech');
    Route::get('/creative-design', fn () => (new PlaceholderController)->show('Request Creative Design', 'Modul request Creative Design akan menyusul. Untuk saat ini silakan gunakan alur ClickUp yang sudah berjalan.'))->name('placeholder.creative');
    Route::get('/business-trip', fn () => (new PlaceholderController)->show('Business Trip', 'Modul Business Trip masih dalam tahap perencanaan bersama tim terkait.'))->name('placeholder.trip');

    // ERF
    Route::prefix('erf')->name('erf.')->group(function () {
        Route::get('/', ErfIndex::class)->name('index');
        Route::get('/create', ErfCreate::class)->name('create');
        Route::get('/{erfRequest}/edit', ErfCreate::class)->name('edit');
        Route::get('/{erfRequest}', ErfShow::class)->name('show');
    });

    // GA
    Route::prefix('ga')->name('ga.')->group(function () {
        Route::get('/', GaIndex::class)->name('index');
        Route::get('/create', GaCreate::class)->name('create');
        Route::get('/{gaRequest}/edit', GaCreate::class)->name('edit');
        Route::get('/{gaRequest}', GaShow::class)->name('show');
    });

    // Master data (HR & Admin)
    Route::prefix('master')->name('master.')->middleware('role:hr,admin')->group(function () {
        Route::get('/karyawan', KaryawanManager::class)->name('karyawan.index');
        Route::get('/jabatan-ttf', JabatanTtfManager::class)->name('jabatan-ttf.index');
        Route::get('/kalender-kerja', KalenderKerjaManager::class)->name('kalender-kerja.index');
        // Konten menyusul — dibuat placeholder dulu supaya menu & submenu sudah tersedia.
        Route::get('/divisi', fn () => (new PlaceholderController)->show('Master Data — Divisi', 'Data Divisi akan dilengkapi menyusul.'))->name('divisi.index');
        Route::get('/jabatan', fn () => (new PlaceholderController)->show('Master Data — Jabatan', 'Data Jabatan akan dilengkapi menyusul.'))->name('jabatan.index');
    });

    // Setting (khusus Super Admin)
    Route::prefix('setting')->name('setting.')->middleware('role:admin')->group(function () {
        Route::get('/users', UserManager::class)->name('users.index');
        Route::get('/grup-akses', GroupAccessManager::class)->name('grup-akses.index');
    });
});
