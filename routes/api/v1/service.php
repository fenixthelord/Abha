<?php

use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('services')->group(function () {

    Route::get('/index', [ServiceController::class, 'index']); //done
    Route::get('/show', [ServiceController::class, 'show']); //done

    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::post('/add', [ServiceController::class, 'store']); //done
            Route::match(['put', 'patch'], '/update', [ServiceController::class, 'update']); //done
            Route::delete('/delete', [ServiceController::class, 'destroy']); //done
        });
    });
});

/*

                                        ****  About Routes  ****


1. Route: GET /services/index
المطلوب: لا أحتاج اضافة في الـ request، لكن يمكن إرسال معلمات
 (مثل page, per_page, search, department_id) لفلترة والبحث عن بيانات حسب القسم أو تحديد عدد العناصر في الصفحة.

2. Route: GET /services/show
 لعرض تفاصيل خدمة معينة بناءً على الـ id.
المطلوب: إرسال id في الـ query string ( مثل : ?id=123).

3. Route: POST /services/add
 لإضافة خدمة جديدة.
المطلوب: department_id (القسم).
name (اسم الخدمة باللغتين الإنجليزية والعربية).
details
image

4. Route: PUT/PATCH /services/update
 لتحديث معلومات خدمة موجودة.
id ( الخدمة التي اريد  تحديثها) :مطلوب
البيانات اللي اريد تحديثها... ( name, details, image, department_id).

5. Route: DELETE /services/delete
 لحذف خدمة معينة.
المطلوب: id (معرف الخدمةالتي اريد حذفها).

*/
