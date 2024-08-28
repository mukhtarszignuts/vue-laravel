<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\SubCategoryController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('registration', 'registration');
    Route::post('forget-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
});

// Authentication api 
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::post('change-password', 'changePassword');
    });
    // user route 
    Route::prefix('user')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::post('list', 'index');
            Route::get('/{id}', 'show');
            Route::post('create', 'store');
            Route::post('update', 'update');
            Route::get('delete/{id}', 'delete');
            Route::post('bulk-delete', 'bulkDelete');
        });
    });

    // Admin Route 
    Route::prefix('admin')->group(function () {

        Route::controller(DashboardController::class)->group(function () {
            Route::get('dashboard', 'dashboard');
        });

        //! Need to change route for admin 
        Route::prefix('user')->group(function () {
            Route::controller(UserController::class)->group(function () {
                Route::post('list', 'index');
                Route::get('/{id}', 'show');
                Route::post('create', 'store');
                Route::post('update', 'update');
                Route::get('delete/{id}', 'delete');
                Route::post('bulk-delete', 'bulkDelete');
            });
        });

        // Category route
        Route::prefix('category')->group(function () {
            Route::controller(CategoryController::class)->group(function () {
                Route::post('list', 'index');
                Route::get('/{id}', 'show');
                Route::post('create', 'store');
                Route::post('update', 'update');
                Route::get('delete/{id}', 'delete');
                Route::get('status-change/{id}', 'statusChange');
                Route::post('bulk-delete', 'bulkDelete');
            });
        });
        
        // Company route
        Route::prefix('company')->group(function () {
            Route::controller(CompanyController::class)->group(function () {
                Route::post('list', 'index');
                Route::get('/{id}', 'show');
                Route::post('create', 'store');
                Route::post('update', 'update');
                Route::get('delete/{id}', 'delete');
                Route::get('status-change/{id}', 'statusChange');
                // Route::post('bulk-delete', 'bulkDelete');
            });
        });

        //Sub Category route
        Route::prefix('subcategory')->group(function () {
            Route::controller(SubCategoryController::class)->group(function () {
                Route::post('list', 'index');
                Route::get('/{id}', 'show');
                Route::post('create', 'store');
                Route::post('update', 'update');
                Route::get('delete/{id}', 'delete');
                Route::get('status-change/{id}', 'statusChange');
                Route::post('bulk-delete', 'bulkDelete');
            });
        });

        // Product Route
        Route::prefix('product')->group(function () {
            Route::controller(ProductController::class)->group(function () {
                Route::post('list', 'index');
                Route::get('/{id}', 'show');
                Route::post('create', 'store');
                Route::post('update', 'update');
                Route::get('delete/{id}', 'delete');
                Route::post('bulk-delete', 'bulkDelete');
                Route::post('image-delete', 'deleteImage');
                Route::get('status-change/{id}', 'statusChange');
                Route::post('bulk-status', 'bulkStatusChange');
            });
        });

        //Project Route
        Route::prefix('project')->group(function () {
            Route::controller(ProjectController::class)->group(function () {
                Route::post('list', 'index');
                Route::get('/{id}', 'show');
                Route::post('create', 'store');
                Route::post('update', 'update');
                Route::get('delete/{id}', 'delete');
            });
        });
    });
});
