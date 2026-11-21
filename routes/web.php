<?php

use App\Enums\ModuleKey;
use App\Enums\Role;
use App\Http\Controllers\Administration\AuditTrailController;
use App\Http\Controllers\Administration\AuditTrailEntryController;
use App\Http\Controllers\Administration\BackupController;
use App\Http\Controllers\Administration\CreateLookupItemController;
use App\Http\Controllers\Administration\DataDictionaryCategoryController;
use App\Http\Controllers\Administration\DataDictionaryController;
use App\Http\Controllers\Administration\DownloadBackupController;
use App\Http\Controllers\Administration\EditLookupItemController;
use App\Http\Controllers\Administration\EditModuleController;
use App\Http\Controllers\Administration\FileSecurityController;
use App\Http\Controllers\Administration\ModuleController;
use App\Http\Controllers\Administration\RunBackupController;
use App\Http\Controllers\Administration\StoreLookupItemController;
use App\Http\Controllers\Administration\ToggleLookupItemController;
use App\Http\Controllers\Administration\UpdateFileSecurityController;
use App\Http\Controllers\Administration\UpdateLookupItemController;
use App\Http\Controllers\Administration\UpdateModuleController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Projects\ClientController;
use App\Http\Controllers\Projects\CreateClientController;
use App\Http\Controllers\Projects\CreateProjectController;
use App\Http\Controllers\Projects\EditClientController;
use App\Http\Controllers\Projects\EditProjectController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Projects\ShowClientController;
use App\Http\Controllers\Projects\ShowProjectController;
use App\Http\Controllers\Projects\StoreClientController;
use App\Http\Controllers\Projects\StoreProjectController;
use App\Http\Controllers\Projects\UpdateClientController;
use App\Http\Controllers\Projects\UpdateProjectController;
use App\Http\Controllers\Settings\AttendanceOvertimeSettingsController;
use App\Http\Controllers\Settings\AttendanceSettingsController;
use App\Http\Controllers\Settings\ClaimApprovalController;
use App\Http\Controllers\Settings\ClaimSettingsController;
use App\Http\Controllers\Settings\CompanyBankAccountDetailsController;
use App\Http\Controllers\Settings\CompanyDetailsController;
use App\Http\Controllers\Settings\CreateClaimApprovalRouteController;
use App\Http\Controllers\Settings\CreateLeaveApprovalRouteController;
use App\Http\Controllers\Settings\DestroyClaimApprovalRouteController;
use App\Http\Controllers\Settings\DestroyLeaveApprovalRouteController;
use App\Http\Controllers\Settings\EditClaimApprovalRouteController;
use App\Http\Controllers\Settings\EditLeaveApprovalRouteController;
use App\Http\Controllers\Settings\EditLeavePolicyController;
use App\Http\Controllers\Settings\EpfSettingsController;
use App\Http\Controllers\Settings\LeaveApprovalController;
use App\Http\Controllers\Settings\LeavePolicyController;
use App\Http\Controllers\Settings\LeaveSettingsController;
use App\Http\Controllers\Settings\OvertimeSettingsController;
use App\Http\Controllers\Settings\PayrollSettingsController;
use App\Http\Controllers\Settings\PayslipSettingsController;
use App\Http\Controllers\Settings\PreviewPayslipController;
use App\Http\Controllers\Settings\ProRataSettingsController;
use App\Http\Controllers\Settings\StoreClaimApprovalRouteController;
use App\Http\Controllers\Settings\StoreLeaveApprovalRouteController;
use App\Http\Controllers\Settings\UpdateAttendanceOvertimeSettingsController;
use App\Http\Controllers\Settings\UpdateAttendanceSettingsController;
use App\Http\Controllers\Settings\UpdateClaimApprovalRouteController;
use App\Http\Controllers\Settings\UpdateClaimSettingsController;
use App\Http\Controllers\Settings\UpdateCompanyBankAccountDetailsController;
use App\Http\Controllers\Settings\UpdateCompanyDetailsController;
use App\Http\Controllers\Settings\UpdateEpfSettingsController;
use App\Http\Controllers\Settings\UpdateLeaveApprovalRouteController;
use App\Http\Controllers\Settings\UpdateLeaveCancellationController;
use App\Http\Controllers\Settings\UpdateLeavePolicyController;
use App\Http\Controllers\Settings\UpdateLeaveSettingsController;
use App\Http\Controllers\Settings\UpdateOvertimeSettingsController;
use App\Http\Controllers\Settings\UpdatePayrollSettingsController;
use App\Http\Controllers\Settings\UpdatePayslipSettingsController;
use App\Http\Controllers\Settings\UpdateProRataSettingsController;
use App\Http\Controllers\Settings\UpdateVisitSettingsController;
use App\Http\Controllers\Settings\VisitSettingsController;
use App\Http\Controllers\Staff\CreateEmployeeController;
use App\Http\Controllers\Staff\DeleteEmployeeDocumentController;
use App\Http\Controllers\Staff\DownloadEmployeeDocumentController;
use App\Http\Controllers\Staff\DownloadMyDocumentController;
use App\Http\Controllers\Staff\EditEmployeeController;
use App\Http\Controllers\Staff\EmployeeController;
use App\Http\Controllers\Staff\MyProfileController;
use App\Http\Controllers\Staff\SendStaffInvitationController;
use App\Http\Controllers\Staff\ShowEmployeeController;
use App\Http\Controllers\Staff\StoreEmployeeController;
use App\Http\Controllers\Staff\UpdateEmployeeController;
use App\Http\Controllers\Staff\UpdateMyProfileController;
use App\Http\Controllers\Staff\UploadEmployeeDocumentController;
use App\Models\Client;
use App\Models\Employee;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::post('/locale', LocaleController::class)->name('locale.update');

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/analytics', 'admin.analytics')->name('admin.analytics');
    Route::view('/settings', 'admin.settings')->name('admin.settings');
    Route::get('/my-profile', MyProfileController::class)->name('my-profile.show');
    Route::put('/my-profile', UpdateMyProfileController::class)->name('my-profile.update');
    Route::get('/my-profile/documents/{document}', DownloadMyDocumentController::class)->whereUuid('document')->name('my-profile.documents.download');

    Route::middleware('role:'.collect(Employee::RECORD_MANAGER_ROLES)->map->value->implode('|'))->prefix('staff')->name('staff.')->group(function (): void {
        Route::get('/', EmployeeController::class)->name('index');
        Route::get('/create', CreateEmployeeController::class)->name('create');
        Route::post('/', StoreEmployeeController::class)->name('store');
        Route::get('/{employee}', ShowEmployeeController::class)->name('show');
        Route::get('/{employee}/edit', EditEmployeeController::class)->name('edit');
        Route::put('/{employee}', UpdateEmployeeController::class)->name('update');
        Route::post('/{employee}/invitation', SendStaffInvitationController::class)->middleware('throttle:3,10')->name('invitation');
        Route::post('/{employee}/documents', UploadEmployeeDocumentController::class)->name('documents.store');
        Route::get('/{employee}/documents/{document}', DownloadEmployeeDocumentController::class)->whereUuid('document')->name('documents.download');
        Route::delete('/{employee}/documents/{document}', DeleteEmployeeDocumentController::class)->whereUuid('document')->name('documents.destroy');
    });

    Route::middleware('module:'.ModuleKey::Projects->value)->group(function (): void {
        // Clients first, so /projects/clients isn't read as a project.
        Route::middleware('role:'.collect(Client::VIEWER_ROLES)->map->value->implode('|'))->prefix('projects/clients')->name('clients.')->group(function (): void {
            Route::get('/', ClientController::class)->name('index');
            Route::get('/create', CreateClientController::class)->name('create');
            Route::post('/', StoreClientController::class)->name('store');
            Route::get('/{client}', ShowClientController::class)->name('show');
            Route::get('/{client}/edit', EditClientController::class)->name('edit');
            Route::put('/{client}', UpdateClientController::class)->name('update');
        });

        Route::prefix('projects')->name('projects.')->group(function (): void {
            Route::get('/', ProjectController::class)->name('index');
            Route::get('/create', CreateProjectController::class)->name('create');
            Route::post('/', StoreProjectController::class)->name('store');
            Route::get('/{project}', ShowProjectController::class)->name('show');
            Route::get('/{project}/edit', EditProjectController::class)->name('edit');
            Route::put('/{project}', UpdateProjectController::class)->name('update');
        });
    });

    Route::middleware('role:'.Role::SuperAdmin->value)->prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/company/basic-details', CompanyDetailsController::class)->name('company.basic-details');
        Route::put('/company/basic-details', UpdateCompanyDetailsController::class)->name('company.basic-details.update');
        Route::get('/company/bank-account-details', CompanyBankAccountDetailsController::class)->name('company.bank-account-details');
        Route::put('/company/bank-account-details', UpdateCompanyBankAccountDetailsController::class)->name('company.bank-account-details.update');
        Route::middleware('module:'.ModuleKey::Leave->value)->group(function (): void {
            Route::get('/leave/general', LeaveSettingsController::class)->name('leave.general');
            Route::put('/leave/general', UpdateLeaveSettingsController::class)->name('leave.general.update');
            Route::get('/leave/policies', LeavePolicyController::class)->name('leave.policies.index');
            Route::get('/leave/policies/{leaveType}/edit', EditLeavePolicyController::class)->where('leaveType', '[a-z0-9-]+')->name('leave.policies.edit');
            Route::put('/leave/policies/{leaveType}', UpdateLeavePolicyController::class)->where('leaveType', '[a-z0-9-]+')->name('leave.policies.update');
            Route::get('/leave/approval', LeaveApprovalController::class)->name('leave.approval.index');
            Route::put('/leave/approval/cancellation', UpdateLeaveCancellationController::class)->name('leave.approval.cancellation');
            Route::get('/leave/approval/routes/create', CreateLeaveApprovalRouteController::class)->name('leave.approval.routes.create');
            Route::post('/leave/approval/routes', StoreLeaveApprovalRouteController::class)->name('leave.approval.routes.store');
            Route::get('/leave/approval/routes/{approvalRoute}/edit', EditLeaveApprovalRouteController::class)->name('leave.approval.routes.edit');
            Route::put('/leave/approval/routes/{approvalRoute}', UpdateLeaveApprovalRouteController::class)->name('leave.approval.routes.update');
            Route::delete('/leave/approval/routes/{approvalRoute}', DestroyLeaveApprovalRouteController::class)->name('leave.approval.routes.destroy');
        });
        Route::middleware('module:'.ModuleKey::Payroll->value)->group(function (): void {
            Route::get('/payroll/general', PayrollSettingsController::class)->name('payroll.general');
            Route::put('/payroll/general', UpdatePayrollSettingsController::class)->name('payroll.general.update');
            Route::get('/payroll/epf', EpfSettingsController::class)->name('payroll.epf');
            Route::put('/payroll/epf', UpdateEpfSettingsController::class)->name('payroll.epf.update');
            Route::get('/payroll/overtime', OvertimeSettingsController::class)->name('payroll.overtime');
            Route::put('/payroll/overtime', UpdateOvertimeSettingsController::class)->name('payroll.overtime.update');
            Route::get('/payroll/pro-rata', ProRataSettingsController::class)->name('payroll.pro-rata');
            Route::put('/payroll/pro-rata', UpdateProRataSettingsController::class)->name('payroll.pro-rata.update');
            Route::get('/payroll/payslip', PayslipSettingsController::class)->name('payroll.payslip');
            Route::put('/payroll/payslip', UpdatePayslipSettingsController::class)->name('payroll.payslip.update');
            Route::get('/payroll/payslip/preview', PreviewPayslipController::class)->name('payroll.payslip.preview');
        });
        Route::middleware('module:'.ModuleKey::Claim->value)->group(function (): void {
            Route::get('/claim/general', ClaimSettingsController::class)->name('claim.general');
            Route::put('/claim/general', UpdateClaimSettingsController::class)->name('claim.general.update');
            Route::get('/claim/approval', ClaimApprovalController::class)->name('claim.approval.index');
            Route::get('/claim/approval/routes/create', CreateClaimApprovalRouteController::class)->name('claim.approval.routes.create');
            Route::post('/claim/approval/routes', StoreClaimApprovalRouteController::class)->name('claim.approval.routes.store');
            Route::get('/claim/approval/routes/{claimApprovalRoute}/edit', EditClaimApprovalRouteController::class)->name('claim.approval.routes.edit');
            Route::put('/claim/approval/routes/{claimApprovalRoute}', UpdateClaimApprovalRouteController::class)->name('claim.approval.routes.update');
            Route::delete('/claim/approval/routes/{claimApprovalRoute}', DestroyClaimApprovalRouteController::class)->name('claim.approval.routes.destroy');
        });
        Route::middleware('module:'.ModuleKey::Hadir->value)->group(function (): void {
            Route::get('/hadir/general', AttendanceSettingsController::class)->name('hadir.general');
            Route::put('/hadir/general', UpdateAttendanceSettingsController::class)->name('hadir.general.update');
            Route::get('/hadir/overtime', AttendanceOvertimeSettingsController::class)->name('hadir.overtime');
            Route::put('/hadir/overtime', UpdateAttendanceOvertimeSettingsController::class)->name('hadir.overtime.update');
            Route::get('/hadir/visits', VisitSettingsController::class)->name('hadir.visits');
            Route::put('/hadir/visits', UpdateVisitSettingsController::class)->name('hadir.visits.update');
        });
    });

    Route::middleware('role:'.Role::SuperAdmin->value)->prefix('administrations')->name('administrations.')->group(function (): void {
        Route::get('/audit-trail', AuditTrailController::class)->name('audit-trail.index');
        Route::get('/audit-trail/{activity}', AuditTrailEntryController::class)->whereNumber('activity')->name('audit-trail.show');
        Route::get('/backups', BackupController::class)->name('backups.index');
        Route::post('/backups', RunBackupController::class)->middleware('throttle:3,10')->name('backups.store');
        Route::get('/backups/{disk}/{file}', DownloadBackupController::class)->where('file', '[A-Za-z0-9._-]+\.zip')->name('backups.download');
        Route::view('/log-viewer', 'administrations.log-viewer.index')->name('log-viewer.index');
        Route::get('/file-security', FileSecurityController::class)->name('file-security.index');
        Route::put('/file-security', UpdateFileSecurityController::class)->name('file-security.update');
        Route::get('/modules', ModuleController::class)->name('modules.index');
        Route::get('/modules/{moduleKey}/edit', EditModuleController::class)->name('modules.edit');
        Route::put('/modules/{moduleKey}', UpdateModuleController::class)->name('modules.update');
        Route::get('/data-dictionary', DataDictionaryController::class)->name('data-dictionary.index');
        Route::get('/data-dictionary/{category}', DataDictionaryCategoryController::class)->name('data-dictionary.show');
        Route::prefix('/data-dictionary/{category}/items')->name('data-dictionary.items.')->group(function (): void {
            Route::get('/create', CreateLookupItemController::class)->name('create');
            Route::post('/', StoreLookupItemController::class)->name('store');
            Route::get('/{item}/edit', EditLookupItemController::class)->whereNumber('item')->name('edit');
            Route::put('/{item}', UpdateLookupItemController::class)->whereNumber('item')->name('update');
            Route::patch('/{item}/status', ToggleLookupItemController::class)->whereNumber('item')->name('status');
        });
    });

    if (config('cendana.showcase')) {
        Route::prefix('components')->name('showcase.')->group(function (): void {
            Route::view('/cards', 'showcase.cards')->name('cards');
            Route::view('/tables', 'showcase.tables')->name('tables');
            Route::view('/table-states', 'showcase.table-states')->name('table-states');
            Route::view('/forms', 'showcase.forms')->name('forms');
            Route::view('/filters', 'showcase.filters')->name('filters');
            Route::view('/charts', 'showcase.charts')->name('charts');
            Route::view('/buttons', 'showcase.buttons')->name('buttons');
            Route::view('/feedback', 'showcase.feedback')->name('feedback');
            Route::view('/navigation', 'showcase.navigation')->name('navigation');
            Route::view('/data-patterns', 'showcase.data-patterns')->name('data-patterns');
            Route::post('/data-patterns/preview', fn () => redirect()->route('showcase.data-patterns')->with('status', __('Confirmation preview complete; no data was deleted.')))->name('data-patterns.preview');
        });

        Route::prefix('examples/records')->name('examples.records.')->group(function (): void {
            Route::view('/', 'examples.records.index')->name('index');
            Route::view('/new', 'examples.records.create')->name('create');
            Route::view('/detail', 'examples.records.show')->name('show');
            Route::view('/edit', 'examples.records.edit')->name('edit');
        });
    }
});

Route::view('/demo/404', 'errors.404')->name('demo.404');
Route::view('/testing', 'testing')->name('testing');
