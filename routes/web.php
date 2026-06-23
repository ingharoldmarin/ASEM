<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AprendizController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompetenciaController;
use App\Http\Controllers\CoordinacionController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\InstructorProgramController;
use App\Http\Controllers\MatriculaExcelController;
use App\Http\Controllers\ResultadoRecursoController;
use App\Http\Controllers\ReporteAsistenciaController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\ProgramController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/',         [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout',    [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard',  [AuthController::class, 'dashboard'])->name('dashboard');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                              [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/instructors',                   [AdminController::class, 'instructors'])->name('instructors');
        Route::get('/instructors/create',            [AdminController::class, 'createInstructor'])->name('instructors.create');
        Route::post('/instructors',                  [AdminController::class, 'storeInstructor'])->name('instructors.store');
        Route::delete('/instructors/{user}',         [AdminController::class, 'deleteInstructor'])->name('instructors.delete');
        Route::get('/users',                         [AdminController::class, 'users'])->name('users');
    });

    // Coordinacion
    Route::middleware('role:coordinacion')->prefix('coordinacion')->name('coordinacion.')->group(function () {
        Route::get('/',                              [CoordinacionController::class, 'dashboard'])->name('dashboard');
        Route::get('/instructors/create',            [CoordinacionController::class, 'createInstructor'])->name('instructors.create');
        Route::post('/instructors',                  [CoordinacionController::class, 'storeInstructor'])->name('instructors.store');
    });

    // Instructor
    Route::middleware('role:instructor')->prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/',       [InstructorController::class, 'dashboard'])->name('dashboard');
        Route::get('/fichas', [InstructorController::class, 'myFichas'])->name('fichas');
    });

    // Matricula de aprendices (instructor + admin + coordinacion)
    Route::middleware('role:instructor,admin,coordinacion')->prefix('instructor')->name('instructor.')->group(function () {
        Route::post('/fichas/{ficha}/aprendices',              [InstructorController::class, 'enrollAprendiz'])->name('fichas.enroll');
        Route::delete('/fichas/{ficha}/aprendices/{aprendiz}', [InstructorController::class, 'removeAprendiz'])->name('fichas.remove-aprendiz');
    });

    // Asignación de programas a instructores (admin + coordinacion)
    Route::middleware('role:admin,coordinacion')->group(function () {
        Route::get('/instructors/programs',                              [InstructorProgramController::class, 'index'])->name('instructors.programs');
        Route::post('/instructors/{instructor}/programs',                [InstructorProgramController::class, 'assign'])->name('instructors.programs.assign');
        Route::delete('/instructors/{instructor}/programs/{program}',    [InstructorProgramController::class, 'remove'])->name('instructors.programs.remove');
    });

    // Reporte de asistencia Excel
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/reportes/asistencia',          [ReporteAsistenciaController::class, 'index'])->name('reportes.asistencia');
        Route::post('/reportes/asistencia/generate',[ReporteAsistenciaController::class, 'generate'])->name('reportes.asistencia.generate');
    });

    // Matrícula masiva por Excel
    Route::middleware('role:instructor,admin,coordinacion')->group(function () {
        Route::get('/matricula/template',              [MatriculaExcelController::class, 'downloadTemplate'])->name('matricula.template');
        Route::post('/fichas/{ficha}/matricula/import',[MatriculaExcelController::class, 'import'])->name('matricula.import');
    });

    // Aprendiz
    Route::middleware('role:aprendiz')->prefix('aprendiz')->name('aprendiz.')->group(function () {
        Route::get('/',           [AprendizController::class, 'dashboard'])->name('dashboard');
        Route::get('/resultados', [AprendizController::class, 'misResultados'])->name('resultados');
    });

    // Detalle de resultado (aprendiz también puede ver)
    Route::middleware('role:admin,coordinacion,instructor,aprendiz')->group(function () {
        Route::get('/resultados/{resultado}/detalle', [ResultadoRecursoController::class, 'show'])->name('resultados.detalle');
    });

    // Programas (admin + coordinacion)
    Route::middleware('role:admin,coordinacion')->group(function () {
        Route::resource('programs', ProgramController::class)->except(['show']);
    });

    // Fichas — listado (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/fichas', [FichaController::class, 'index'])->name('fichas.index');
    });

    // Fichas — creación (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/fichas/create', [FichaController::class, 'create'])->name('fichas.create');
        Route::post('/fichas',       [FichaController::class, 'store'])->name('fichas.store');
    });

    // Fichas — rutas con parámetro {ficha}
    // show: admin + coordinacion + instructor
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/fichas/{ficha}',          [FichaController::class, 'show'])->name('fichas.show');
        Route::get('/fichas/{ficha}/download', [FichaController::class, 'downloadZip'])->name('fichas.download');
    });

    // edit/update/delete/assign: solo admin + coordinacion
    Route::middleware('role:admin,coordinacion')->group(function () {
        Route::get('/fichas/{ficha}/edit',                        [FichaController::class, 'edit'])->name('fichas.edit');
        Route::put('/fichas/{ficha}',                             [FichaController::class, 'update'])->name('fichas.update');
        Route::delete('/fichas/{ficha}',                          [FichaController::class, 'destroy'])->name('fichas.destroy');
        Route::post('/fichas/{ficha}/instructors',                [FichaController::class, 'assignInstructor'])->name('fichas.assign-instructor');
        Route::delete('/fichas/{ficha}/instructors/{instructor}', [FichaController::class, 'removeInstructor'])->name('fichas.remove-instructor');
    });

    // Carpetas documentales (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/fichas/{ficha}/folders/{folder}',  [FolderController::class, 'show'])->name('folders.show');
        Route::patch('/folders/{folder}/rename',        [FolderController::class, 'rename'])->name('folders.rename');
        Route::post('/folders/{folder}/upload',         [FolderController::class, 'uploadDocument'])->name('folders.upload');
        Route::delete('/documents/{document}',          [FolderController::class, 'deleteDocument'])->name('documents.delete');
        Route::post('/folders/{folder}/reject',  [FolderController::class, 'reject'])->name('folders.reject');
        Route::post('/folders/{folder}/clear-observation', [FolderController::class, 'clearObservation'])->name('folders.clear-observation');
    });

    // Aprobacion de carpetas (solo admin + coordinacion)
    Route::middleware('role:admin,coordinacion')->group(function () {
        Route::post('/folders/{folder}/approve', [FolderController::class, 'approve'])->name('folders.approve');
    });

    // Informes mensuales generales (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/reports',                        [MonthlyReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/create',                 [MonthlyReportController::class, 'create'])->name('reports.create');
        Route::post('/reports',                       [MonthlyReportController::class, 'store'])->name('reports.store');
        Route::post('/reports/{report}/files',        [MonthlyReportController::class, 'addFiles'])->name('reports.add-files');
        Route::patch('/reports/{report}/status',      [MonthlyReportController::class, 'updateStatus'])->name('reports.status');
        Route::delete('/reports/{report}',            [MonthlyReportController::class, 'destroy'])->name('reports.destroy');
        Route::delete('/reports/files/{file}',        [MonthlyReportController::class, 'destroyFile'])->name('reports.files.delete');
    });

    // Asistencia (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/fichas/{ficha}/attendance',          [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/fichas/{ficha}/attendance/create',   [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/fichas/{ficha}/attendance',         [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/fichas/{ficha}/attendance/{session}',      [AttendanceController::class, 'show'])->name('attendance.show');
        Route::get('/fichas/{ficha}/attendance/{session}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/fichas/{ficha}/attendance/{session}',      [AttendanceController::class, 'update'])->name('attendance.update');
    });

    // Consolidados por ficha (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::get('/consolidado',                       [\App\Http\Controllers\ConsolidadoController::class, 'index'])->name('consolidado.index');
        Route::get('/consolidado/{ficha}',               [\App\Http\Controllers\ConsolidadoController::class, 'show'])->name('consolidado.show');
        Route::get('/consolidado/{ficha}/excel',         [\App\Http\Controllers\ConsolidadoController::class, 'exportExcel'])->name('consolidado.excel');
        Route::get('/consolidado/{ficha}/pdf',           [\App\Http\Controllers\ConsolidadoController::class, 'exportPdf'])->name('consolidado.pdf');
    });

    // Competencias y resultados (admin + coordinacion + instructor)
    Route::middleware('role:admin,coordinacion,instructor')->group(function () {
        Route::resource('competencias', CompetenciaController::class);
        Route::post('/competencias/{competencia}/resultados',  [CompetenciaController::class, 'storeResultado'])->name('resultados.store');
        Route::patch('/resultados/{resultado}',                [CompetenciaController::class, 'updateResultado'])->name('resultados.update');
        Route::delete('/resultados/{resultado}',               [CompetenciaController::class, 'destroyResultado'])->name('resultados.destroy');
        Route::post('/resultados/{resultado}/evaluate',                [CompetenciaController::class, 'evaluateAprendiz'])->name('resultados.evaluate');
        Route::post('/competencias/{competencia}/evaluate-ficha',       [CompetenciaController::class, 'evaluateFicha'])->name('competencias.evaluate-ficha');

        // Guías, actividades y notas por resultado
        Route::get('/resultados/{resultado}/detalle',                   [ResultadoRecursoController::class, 'show'])->name('resultados.detalle');
        Route::post('/resultados/{resultado}/guias',                    [ResultadoRecursoController::class, 'uploadGuia'])->name('resultados.guias.upload');
        Route::delete('/guias/{guia}',                                  [ResultadoRecursoController::class, 'deleteGuia'])->name('resultados.guias.delete');
        Route::post('/resultados/{resultado}/actividades',              [ResultadoRecursoController::class, 'storeActividad'])->name('resultados.actividades.store');
        Route::delete('/actividades/{actividad}',                       [ResultadoRecursoController::class, 'destroyActividad'])->name('resultados.actividades.destroy');
        Route::post('/actividades/{actividad}/calificar-masivo',        [ResultadoRecursoController::class, 'calificarMasivo'])->name('actividades.calificar-masivo');
    });
});
