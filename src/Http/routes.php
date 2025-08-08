<?php

use Illuminate\Support\Facades\Route;
use Zenobio93\Seat\SkillChecker\Http\Controllers\SkillPlanController;
use Zenobio93\Seat\SkillChecker\Http\Controllers\SkillCheckerController;

Route::group([
    'namespace' => 'Zenobio93\Seat\SkillChecker\Http\Controllers',
    'prefix' => 'skillchecker',
    'middleware' => ['web', 'auth', 'locale'],
], function () {

    // Skill Plans Management Routes
    Route::group([
        'prefix' => 'skill-plans',
        'middleware' => 'can:skillchecker.manage_skill_plans',
    ], function () {
        Route::get('/', [SkillPlanController::class, 'index'])->name('skillchecker.skill-plans.index');
        Route::get('/create', [SkillPlanController::class, 'create'])->name('skillchecker.skill-plans.create');
        Route::post('/', [SkillPlanController::class, 'store'])->name('skillchecker.skill-plans.store');
        Route::get('/import', [SkillPlanController::class, 'import'])->name('skillchecker.skill-plans.import');
        Route::post('/import', [SkillPlanController::class, 'processImport'])->name('skillchecker.skill-plans.process-import');
        Route::get('/{skillplan}', [SkillPlanController::class, 'show'])->name('skillchecker.skill-plans.show');
        Route::get('/{skillplan}/import', [SkillPlanController::class, 'importUpdate'])->name('skillchecker.skill-plans.import-update');
        Route::post('/{skillplan}/import', [SkillPlanController::class, 'processImportUpdate'])->name('skillchecker.skill-plans.process-import-update');
        Route::get('/{skillplan}/copy', [SkillPlanController::class, 'copy'])->name('skillchecker.skill-plans.copy');
        Route::get('/{skillplan}/edit', [SkillPlanController::class, 'edit'])->name('skillchecker.skill-plans.edit');
        Route::put('/{skillplan}', [SkillPlanController::class, 'update'])->name('skillchecker.skill-plans.update');
        Route::delete('/{skillplan}', [SkillPlanController::class, 'destroy'])->name('skillchecker.skill-plans.destroy');
    });

    // Public Skill Plan Routes
    Route::group([
        'prefix' => 'skill-plans',
    ], function () {
        Route::get('/copy-eve/{skillplan}', [SkillPlanController::class, 'copyForEve'])->name('skillchecker.skill-plans.copy-eve');
    });

    // Skill Checker Routes
    Route::group([
        'prefix' => 'checker',
        'middleware' => 'can:skillchecker.check_skills',
    ], function () {
        Route::get('/', [SkillCheckerController::class, 'index'])->name('skillchecker.checker.index');
        Route::post('/check-character', [SkillCheckerController::class, 'checkCharacter'])->name('skillchecker.checker.check-character');
        Route::post('/check-squad', [SkillCheckerController::class, 'checkSquad'])->name('skillchecker.checker.check-squad');
        Route::post('/check-corporation', [SkillCheckerController::class, 'checkCorporation'])->name('skillchecker.checker.check-corporation');
        
        // Lookup routes for autocomplete
        Route::group([
            'prefix' => 'lookup',
        ], function () {
            Route::get('users', [SkillCheckerController::class, 'lookupUsers'])->name('skillchecker.checker.lookup.users');
            Route::get('squads', [SkillCheckerController::class, 'lookupSquads'])->name('skillchecker.checker.lookup.squads');
            Route::get('corporations', [SkillCheckerController::class, 'lookupCorporations'])->name('skillchecker.checker.lookup.corporations');
            Route::get('skill-plans', [SkillCheckerController::class, 'lookupSkillPlans'])->name('skillchecker.checker.lookup.skill-plans');
        });
    });

});

// Character Sheet Integration Route
Route::group([
    'namespace' => 'Zenobio93\Seat\SkillChecker\Http\Controllers',
    'middleware' => ['web', 'auth', 'locale'],
    'prefix' => 'characters',
], function () {
    Route::get('/{character}/skillcheck', [
        'as' => 'character.view.skillcheck',
        'uses' => 'CharacterController@skillCheck',
        'middleware' => 'can:character.skillchecker_skillcheck,character',
    ]);
});