<?php

use App\Http\Controllers\PendingUserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\RiskMitigationController;
use App\Http\Controllers\Admin\RiskEvaluationController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Auth\OTPController;

// GET: Show OTP form (only for authenticated users)
Route::get('/2fa', function () {
    return view('auth.otp');
})->middleware('auth');

// POST: Submit OTP code for verification (also requires auth)
Route::post('/2fa', [OTPController::class, 'verify'])->middleware('auth')->name('otp.verify');

Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard'])
    ->middleware('auth') // if you use auth
    ->name('superadmin.dashboard');


Route::get('/', function () {
    return view('welcome');
});

Route::get('/1', function () {
    return view('mine');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Use the checkRole helper for superadmin route
    Route::get('/superadmin/pending-users', function () {
        checkRole('super-admin');  // ✅ Role check here
        return app(\App\Http\Controllers\PendingUserController::class)->index();
    })->name('pending.users');

    // Approve user
Route::post('/superadmin/approve-user/{user}', function (User $user) {
    checkRole('super-admin');
    return app(\App\Http\Controllers\PendingUserController::class)->approve($user);
})->name('approve.user');

// Reject user
Route::post('/superadmin/reject-user/{user}', function (User $user) {
    checkRole('super-admin');
    return app(\App\Http\Controllers\PendingUserController::class)->reject($user);
})->name('reject.user');

    // Admin dashboard
    Route::get('/admin/dashboard', function () {
        checkRole('admin');  // ✅ Role check here for admin
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Staff dashboard
    Route::get('/staff/dashboard', function () {
        checkRole('staff');  // ✅ Role check here for staff
        return view('staff.dashboard');
    })->name('staff.dashboard');

    Route::middleware(['auth'])->group(function () {
        Route::get('/staff/logrisk', [RiskController::class, 'create'])->name('staff.logrisk');
        Route::post('/staff/logrisk', [RiskController::class, 'store'])->name('staff.logrisk.store');
        Route::get('/admin/risks', [RiskController::class, 'indexForAdmin'])->name('admin.risks.index');
        Route::get('/staff/tasks', [\App\Http\Controllers\Staff\TaskController::class, 'index'])->name('staff.tasks.my_task');
        Route::post('/staff/tasks/{task}/update', [\App\Http\Controllers\Staff\TaskController::class, 'update'])->name('staff.tasks.update');
        Route::put('/staff/tasks/{id}', [\App\Http\Controllers\Staff\TaskController::class, 'update'])->name('staff.tasks.update');

    });


    // Superadmin dashboard
    /*Route::get('/superadmin/dashboard', function () {
        checkRole('super-admin');  // ✅ Role check here for superadmin
        return view('superadmin.dashboard');
    })->name('superadmin.dashboard');*/

    //Route::post('/admin/risk/{risk}/evaluate', [RiskEvaluationController::class, 'store'])->name('admin.risk.evaluate');

    //Route::post('/admin/risks/{id}/evaluate', [RiskController::class, 'evaluate'])->name('admin.risk.evaluate');
Route::post('/admin/risks/{risk}/evaluate', [RiskController::class, 'evaluate'])->name('admin.risk.evaluate');



    Route::middleware(['auth'])->group(function () {

    // ✅ Admin-only: View all Risk Mitigations
    Route::get('/admin/risks/mitigation', function () {
        checkRole('admin');

        $riskMitigations = \App\Models\RiskMitigation::with(['risk', 'assignedStaff'])->get();

        return view('admin.mitigation.index', compact('riskMitigations'));
    })->name('admin.risks.mitigation');

    // ✅ Admin-only: Create Risk Mitigation (after evaluation)
    Route::get('/admin/risks/{risk}/mitigation/create', function (\App\Models\Risk $risk) {
        checkRole('admin');

        $staffs = \App\Models\User::where('company_id', $risk->company_id)
                ->where('role', 'staff')->get();

        return view('admin.mitigation.create', compact('risk', 'staffs'));
    })->name('admin.risks.mitigation.create');


    // ✅ Admin-only: Store Risk Mitigation
    Route::post('/admin/risks/{risk}/mitigation', [RiskMitigationController::class, 'store'])
        ->name('admin.risks.mitigation.store');

    // ✅ Admin-only: View Risk Mitigation Table
Route::get('/admin/risks/mitigation', [RiskMitigationController::class, 'index'])
    ->name('admin.risks.mitigation');

});

Route::get('/admin/mitigation/{id}/edit', [RiskMitigationController::class, 'edit'])->name('admin.risks.mitigation.edit');
Route::put('/admin/mitigation/{id}', [RiskMitigationController::class, 'update'])->name('admin.risks.mitigation.update');

Route::get('/staff/my-reported-risks', [\App\Http\Controllers\Staff\RiskController::class, 'myReportedRisks'])->name('staff.risks.my_history');

Route::put('/admin/tasks/{id}/approve', [App\Http\Controllers\Admin\TaskApprovalController::class, 'approve'])->name('admin.tasks.approve');

Route::get('/admin/completed-tasks', [App\Http\Controllers\Admin\TaskController::class, 'completedTasks'])->name('admin.tasks.completed');

Route::get('/admin/completed-tasks', [RiskMitigationController::class, 'completed'])->name('admin.tasks.completed');

});

require __DIR__.'/auth.php';
