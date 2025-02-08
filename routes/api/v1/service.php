<?php

use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DepartmentsControllers;

Route::prefix('services')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::middleware('activeVerify')->group(function () {
            Route::get('/index', [ServiceController::class, 'index']); //done
            Route::get('/show/{id}', [ServiceController::class, 'show']); //done
            Route::post('/add', [ServiceController::class, 'store']); //done
            Route::put('/update/{id}', [ServiceController::class, 'update']); //done
            Route::delete('/delete/{id}', [ServiceController::class, 'destroy']); //done
        });
    });
});
                                             /*             /*
                                        ****  About Routes  ****
                                          */              /*

/* Route: GET /api/services/index
* URL: GET http://your-api-url/api/services?page=1&per_page=10&search=service_name&department_id=
* page (اختياري): رقم الصفحة للنتائج، الافتراضي هو 1.
* per_page (اختياري): عدد النتائج لكل صفحة، الافتراضي هو 10.
* search (اختياري): نص البحث للبحث في أسماء أو تفاصيل الخدمات.
* department_id (اختياري): تحديد القسم الذي ينتمي إليه الخدمات.
*/

/* Route: GET /api/services/show
 *  URL: GET /api/services/show/{id}
 */

/* Route: POST /api/services/add
                                   store
  *  URL: POST /api/services/add
 * department_id: معرف القسم الذي تنتمي إليه الخدمة (يجب أن يكون موجودًا في جدول الأقسام).
 * name: اسم الخدمة بلغتين (إنجليزية وعربية):
 * name.en: الاسم باللغة الإنجليزية.
 * name.ar: الاسم باللغة العربية.
 * details: تفاصيل الخدمة بلغتين (إنجليزية وعربية):
 * details.en: التفاصيل باللغة الإنجليزية.
 * details.ar: التفاصيل باللغة العربية.
 * image: رابط الصورة أو اسم الملف المرتبط بالخدمة.
 * Example:
  {
  "department_id": 1,
  "name": {
    "en": "Service Name in English",
    "ar": "اسم الخدمة بالعربية"
  },
  "details": {
    "en": "Service details in English",
    "ar": "تفاصيل الخدمة بالعربية"
  },
  "image": "image_url_or_name"
}
 */

/* Route: PUT /api/services/{service_id}
                                  update
  * URL: PUT /api/services/{id}
  * name (اختياري): اسم الخدمة بلغتين (إنجليزية وعربية).
  * name.en: الاسم باللغة الإنجليزية.
  * name.ar: الاسم باللغة العربية.
  * details (اختياري): تفاصيل الخدمة بلغتين (إنجليزية وعربية).
  * details.en: التفاصيل باللغة الإنجليزية.
  * details.ar: التفاصيل باللغة العربية.
  * image (اختياري): رابط الصورة أو اسم الملف المرتبط بالخدمة.
  * department_id (اختياري): معرف القسم الذي تنتمي إليه الخدمة.
 */

/* Route: DELETE /api/services/{service_id}

  * URL: DELETE http://your-api-url/api/services/{service_you_want_to_delete}
 */
