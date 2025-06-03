<?php

use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\AuthenticationPageController;
use App\Http\Controllers\Admin\UsersPageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\InitializeProjectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublicPostController;
use App\Http\Controllers\ReorderPostsController;
use App\Http\Controllers\FilesController;
use App\Http\Middleware\ProjectIsInitialized;
use App\Http\Middleware\ProjectIsNotInitialized;
use App\Http\Middleware\UserIsAdmin;
use App\Http\Middleware\UserIsNotDeactivated;
use App\Http\Middleware\UserIsNotViewer;
use Illuminate\Support\Facades\Route;


Route::resource("initialize", InitializeProjectController::class)
    ->middleware(ProjectIsNotInitialized::class)
    ->only("index", "store");

Route::middleware([ProjectIsInitialized::class])->group(function () {
    Route::get('/login', [AuthController::class, "index"])
        ->middleware(['guest'])
        ->name("login");

    Route::post("/login", [AuthController::class, "login"])
        ->middleware(['guest'])
        ->name("standard-login");

    Route::get("/public/{post_id}", PublicPostController::class)->name("public.post");
    Route::middleware(['auth', UserIsNotDeactivated::class])->group(function (): void {

        Route::get("/posts/search", [PostController::class, 'search'])
            ->name('posts.search');

        Route::resource("posts", PostController::class)
            ->middlewareFor(["store", "destroy", "update"], UserIsNotViewer::class)
            ->only(['store', 'show', 'update', 'destroy']);

        Route::post("/posts/reorder", ReorderPostsController::class)
            ->middleware(UserIsNotViewer::class)
            ->name('posts.reorder');

        Route::middleware(UserIsAdmin::class)->group(function (): void {
            Route::resource('admin/settings', AdminPageController::class)->only(['index', 'store'])->names([
                'index' => 'admin.index',
                'store' => 'admin.store'
            ]);

            Route::resource('admin/users', UsersPageController::class)->only(['index', 'store'])->names([
                'index' => 'users.index',
                'store' => 'user.role.change'
            ]);

            Route::resource('admin/authentication', AuthenticationPageController::class)->only(['index', 'store'])->names([
                'index' => 'authentication.index',
                'store' => 'authentication.update'
            ]);
        });

        Route::get("/", HomepageController::class)->name("homepage");

        Route::get("/docs", DocsController::class)->name("docs");

        Route::post("files", FilesController::class)->name('files.store');

    });

    Route::get('/logout', [AuthController::class, "logout"])
        ->middleware(['auth'])
        ->name('logout');

});
