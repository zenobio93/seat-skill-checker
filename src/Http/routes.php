<?php

use Illuminate\Support\Facades\Route;
use Zenobio93\Seat\SkillChecker\Http\Controllers\SkillListController;
use Zenobio93\Seat\SkillChecker\Http\Controllers\SkillCheckerController;

Route::group([
    'namespace' => 'Zenobio93\Seat\SkillChecker\Http\Controllers',
    'prefix' => 'skillchecker',
    'middleware' => ['web', 'auth', 'locale'],
], function () {

    // Skill Lists Management Routes
    Route::group([
        'prefix' => 'skill-lists',
        'middleware' => 'can:skillchecker.manage_skill_lists',
    ], function () {
        Route::get('/', [SkillListController::class, 'index'])->name('skillchecker.skill-lists.index');
        Route::get('/create', [SkillListController::class, 'create'])->name('skillchecker.skill-lists.create');
        Route::post('/', [SkillListController::class, 'store'])->name('skillchecker.skill-lists.store');
        Route::get('/{skillList}', [SkillListController::class, 'show'])->name('skillchecker.skill-lists.show');
        Route::get('/{skillList}/edit', [SkillListController::class, 'edit'])->name('skillchecker.skill-lists.edit');
        Route::put('/{skillList}', [SkillListController::class, 'update'])->name('skillchecker.skill-lists.update');
        Route::delete('/{skillList}', [SkillListController::class, 'destroy'])->name('skillchecker.skill-lists.destroy');
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