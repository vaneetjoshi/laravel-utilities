<?php

use Illuminate\Support\Facades\Route;
use Vaneetjoshi\LaravelUtilities\Http\Controllers\SettingsController;

Route::prefix(config('utilities.ui.prefix', 'utilities/setting'))->middleware(config('utilities.ui.middleware', ['web', 'auth']))->group(function () {
    Route::put('/update/{group}', [SettingsController::class, 'update'])->name('utilities.settings.update');
});
