<?php

return [
    'custom' => [
        'department_id' => [
            'required' => 'حقل معرف القسم مطلوب.',
            'exists' => 'معرف القسم المحدد غير صالح.',
            "unique" => "هذا القسم موجود بالفعل.",
            "id" => "المعرف الفريد غير صالح.",
        ],

        'roleAndPerm' => [
            'dont_have_permission' => 'ليس لديك صلاحية الوصول لهذه الصفحة',
            'role_name_already_in_use' => 'اسم الدور هذا قيد الاستخدام بالفعل',
            'not_allowed_create_Master_role' => 'غير مسموح لك بانشاء دور رئيسي',
            'role_name_already_use' => 'اسم الدور قيد الاستخدام بالفعل',
            'master_permission' => 'هذه (صلاحية لدور رئيسي)، لا يمكنك تعيينها لهذا الدور',
            'role_created_successfully' => 'تم إنشاء الدور بنجاح',
            'not_allowed_assign_permissions_to_role' => 'لا يُسمح لك بتعيين صلاحية لهذا الدور',
            'role_not_found' => 'الدور غير موجود',
            'permission_not_found' => 'الصلاحية غير موجودة',
            'master_role_permission' => 'هذه هي صلاحية (الدور الرئيسي)، ولا يمكنك تعيينها لهذا الدور',
            'master_role_can_not_assign_user' => 'هذا (الدور الرئيسي), لا يمكنك تعيينه لهذا المستخدم',
            'user_not_found' => 'المستخدم غير موجود',
            'role_has_been_assigned_successfully' => 'تم تعيين الدور بنجاح',
            'forbidden_action' => 'غير مصرح لك بتنفيذ هذا الإجراء',
            'permission_not_exist' => 'هذه الصلاحية غير موجودة',
            'master_permission_user' => 'هذه (صلاحية لدور رئيسي), لا يمكنك تعيينها لهذا المستخدم',
            'permission_assigned_successfully' => 'تم تعيين الصلاحية بنجاح',
            'master_role_cannot_remove_from_Master' => 'هذا (الدور الرئيسي), لا يمكنك إزالته من ماستر',
            'role_removed_successfully' => 'تم إزالة الدور بنجاح',
            'role_not_exist' => 'هذا الدور غير موجود',
            'not_allowed_remove_permissions_to_this_role' => 'لا يسمح لك بإزالة الصلاحيات لهذا الدور',
            'cannot_update_Master_role' => 'لا يمكنك تحديث (الدور الرئيسي)',
            'not_allowed_update_role' => 'لا يسمح لك بتحديث هذا الدور',
            'not_allowed_add_Master_permission' => 'لا يسمح لك بإضافة صلاحية رئيسية',
            'master_role_cannot_be_deleted' => 'لا يمكن حذف دور الماستر',
            'master_cannot_be_deleted' => 'لا يمكنك حذف الماستر',
            'role_deleted_successfully' => 'تم حذف الدور بنجاح',
            //
            'failed_to_obtain_token' => 'فشل في الحصول على الرمز',
            'role_assigned_successfully' => 'تم تعيين الدور بنجاح',
            'permission_removed_successfully' => 'تم إزالة الصلاحية بنجاح',
            'master_permission_cannot_be_assigned' => 'لا يمكن تعيين صلاحية الماستر',
            'validation_error' => 'خطأ في التحقق',
        ],

        'service' => [
            'name_exists' => 'اسم الخدمة موجود بالفعل في هذا القسم',
            'not_found' => 'الخدمة غير موجودة.',
            'created' => 'تم إنشاء الخدمة بنجاح.',
            'updated' => 'تم تحديث الخدمة بنجاح.',
            'deleted' => 'تم حذف الخدمة بنجاح.',
            'already_delete' => 'هذه الخدمة محذوفة بالفعل.',
            'try_again' => 'حدث خطأ، يرجى المحاولة مرة أخرى.',
            'id_required' => 'معرف الخدمة مطلوب.',
            'id_exists' => 'لم يتم العثور على معرف الخدمة.',
        ],
        'event' => [
            'already_deleted' => 'هذا الحدث  محذوف بالفعل.',
            'success_deleted' => 'تم حذف الحدث بنجاح.',
            'id_required' => 'يجب إدخال معرف الحدث.',
            'id_exists' => 'الحدث المحدد غير موجود.',
        ],
        'department' => [
            'not_found' => 'القسم غير موجود.',
            'deleted' => 'تم حذف القسم بنجاح.',
            'delete_failed' => 'حدث خطأ أثناء حذف القسم.',
        ],
        'validation' => [
            'name_required' => 'اسم الخدمة مطلوب.',
            'name_max' => 'يجب ألا يتجاوز اسم الخدمة 255 حرفًا.',
            'details_max' => 'يجب ألا تتجاوز التفاصيل 1000 حرف.',
            'department_required' => 'القسم مطلوب.',
            'image_url' => 'يجب أن يكون رابط الصورة صحيحاً.',
        ],

        'userController' => [
            'user_not_found' => 'المستخدم غير موجود',
            'invalid_page' => 'رقم الصفحة غير صالح',
            'permission_denied' => 'ليس لديك إذن للوصول إلى هذه الصفحة',
            'user_activated' => 'تم تفعيل المستخدم بنجاح',
            'user_deactivated' => 'تم إلغاء تفعيل المستخدم بنجاح',
            'image_uploaded' => 'تم تحميل الصورة بنجاح',
            'otp_sent' => 'تم إرسال OTP بنجاح',
            'otp_verified' => 'تم التحقق من OTP بنجاح',
            'invalid_otp' => 'رمز التحقق غير صالح أو منتهي الصلاحية',
            'otp_expired' => 'رمز التحقق غير منتهي',
            'user_restore' => 'تمت استعادة المستخدم بنجاح',
            'results' => 'لم يتم العثور على نتائج',
            'invalid_search' => 'بحث غير صالح',
            'user_not_deleted' => 'لم يتم حذف المستخدم',
            'user_deleted_already' => 'تم حذف المستخدم بالفعل',
            'deleted_successfully' => 'تم حذف المستخدم بنجاح',
            'master_can_not_be_deleted' => 'هذا المستخدم رئيسي ولا يمكن حذفه',
            'can_not_be_activated_or_deactivated' => 'هذا المستخدم هو (حساب رئيسي), ولا يمكن تفعيله أو الغاء تفعيله',
            'user_is_deleted' => 'تم حذف هذا المستخدم',
            'old_password_wrong' => 'كلمة المرور القديمة خاطئة',
            'old_password_required' => 'كلمة المرور القديمة مطلوبة',
            'master_account_can_not_updated' => 'هذا المستخدم هو حساب رئيسي ولا يمكن تحديثه',
            'dont_have_permission_to_access' => 'ليس لديك اذن الوصول لهذه الصفحة',
        ],

        'firebase' => [
            'notification_failed' => 'فشل في إرسال الإشعار. رمز الحالة من FCM: ',
            'failed_to_obtain_token' => 'فشل في الحصول على رمز الوصول',
            'failed_to_send_notification' => 'فشل في إرسال الإشعار: ',
        ],

        'responseTrait' => [
            'duplicate_entry_found' => 'تم العثور على ادخال مكرر',
            'cannot_delete_or_update' => 'لا يمكن حذفه أو تحديثه كما هو مشار إليه في مكان آخر',
            'foreign_key_constraint_violation' => 'انتهاك قيود المفتاح الخارجي',
            'category_cannot_parent' => 'لا يمكن أن تكون الفئة الأصل الخاص بها',
            'database_error' => 'خطأ في قاعدة البيانات: ',
        ],

        'notifyGroup' => [
            'group_created' => 'تم إنشاء المجموعة بنجاح',
            'group_not_found' => 'المجموعة غير موجودة',
            'users_added' => 'تمت إضافة المستخدمين إلى المجموعة بنجاح',
            'users_removed' => 'تمت إزالة المستخدمين من المجموعة بنجاح',
            'notifications_sent' => 'تم إرسال الإشعارات بنجاح',
            'failed_to_send_notifications' => 'فشل في إرسال الإشعارات',
            'group_deleted' => 'تم حذف المجموعة بنجاح',
            'failed_to_retrieve_groups' => 'فشل في استرجاع المجموعات: ',
            'no_users_in_group' => 'المجموعة لا تحتوي على مستخدمين',
            'no_device_tokens' => 'لم يتم العثور على رموز الأجهزة لهذه المجموعة',
            'validation_failed' => 'فشل التحقق من البيانات',
        ],

        'upload_file' => [
            'upload_file_success' => 'تم تحميل الملف بنجاح.'
        ],

        'language' => [
            'lang_success' => 'تم تغيير اللغة بنجاح',
        ],

        'notification' => [
            'user_not_authenticate' => 'لم تتم مصادقة المستخدم.',
            'notification_sent_success' => 'تم إرسال الإشعارات بنجاح!',
            'notification_sent_fail' => 'فشل في إرسال الإشعارات.',
            'device_token_saved' => 'تم حفظ رمز الجهاز بنجاح.',
            'no_device_tokens' => 'لم يتم العثور على رموز أجهزة للمستخدمين أو المجموعة المحددة.',
            'invalid_page_number' => 'رقم الصفحة غير صالح.',
            'user_not_found' => 'المستخدم غير موجود.',
            'validation_error' => 'خطأ في التحقق من صحة البيانات.',
        ],

        'name' => [
            'required' => 'حقل الاسم مطلوب.',
            "array" => 'يجب ارسال الاسم بشكل مصفوفة ',
            'max' => 'الاسم طويل'
        ],
        'name.en' => [
            'required' => 'حقل الاسم الإنجليزي مطلوب.',
            'string' => 'يجب أن يكون الاسم الإنجليزي نصًا.',
            'min' => 'يجب ألا يقل الاسم الإنجليزي عن :min حروف.',
            'max' => 'يجب ألا يتجاوز الاسم الإنجليزي :max حروف.',
            'unique' => 'الاسم باللغة الإنكليزية موجود بالفعل في هذا القسم.',
        ],
        'name.ar' => [
            'required' => 'حقل الاسم العربي مطلوب.',
            'string' => 'يجب أن يكون الاسم العربي نصًا.',
            'min' => 'يجب ألا يقل الاسم العربي عن :min حروف.',
            'max' => 'يجب ألا يتجاوز الاسم العربي :max حروف.',
            'unique' => ' الاسم باللغة العربية موجود بالفعل في هذا القسم.',
        ],
        'chields' => [
            'required' => 'حقل الأبناء مطلوب.',
            'array' => 'يجب أن تكون الأبناء مصفوفة.',
        ],
        'uuid' => [
            'required' => 'حقل المعرف الفريد مطلوب.',
            'exists' => 'المعرف الفريد المحدد غير صالح أو تم حذفه.',
            'unique' => 'هذا المعرف الفريد موجود بالفعل.',
            'uuid' => 'المعرف الفريد غير صالح.',
        ],
        'roleName' => [
            'required' => 'حقل اسم الدور الوظيفي مطلوب.',
            'exists' => 'هذا الدور الوظيفي غير موجود.',
            'unique' => 'اسم الدور مستخدم بالفعل.',
            'regex' => 'يجب أن يكون اسم الدور بدون مسافات.',
        ],
        'first_name' => [
            'required' => 'حقل الاسم الأول مطلوب.',
            'min' => 'يجب أن يحتوي الاسم الأول على الأقل على 3 أحرف.',
            'max' => 'يجب ألا يتجاوز الاسم الأول 255 حرفًا.',
            'string' => 'يجب أن يكون الاسم الأول نصًا.',
            'regex' => 'يجب أن يكون الاسم الأول نصًا.',
        ],
        'last_name' => [
            'required' => 'حقل الاسم الأخير مطلوب.',
            'min' => 'يجب أن يحتوي الاسم الأخير على الأقل على 3 أحرف.',
            'max' => 'يجب ألا يتجاوز الاسم الأخير 255 حرفًا.',
            'string' => 'يجب أن يكون الاسم الأخير نصًا.',
            'regex' => 'يجب أن يكون الاسم الأخير نصًا.',
        ],
        'email' => [
            'required' => 'حقل البريد الإلكتروني مطلوب.',
            'email' => 'البريد الإلكتروني غير صالح.',
            'unique' => 'البريد الإلكتروني مستخدم بالفعل.',
            'max' => 'يجب ألا يتجاوز البريد الإلكتروني 255 حرفًا.',
            'exists' => 'هذا الايميل غير موجود'
        ],
        'password' => [
            'required' => 'حقل كلمة المرور مطلوب.',
            'min' => 'يجب أن تحتوي كلمة المرور على الأقل على 8 أحرف.',
            'string' => 'يجب أن تكون كلمة المرور نصًا.',
            'regex' => 'يجب أن تحتوي على حرف صغير على الأقل، وحرف كبير، ورقم واحد.',
            'confirmed' => 'كلمة المرور غير متطابقة.',
        ],

        'category' => [
            'category_deleted' => 'تم حذف الفئة وجميع الفئات الفرعية المرتبطة بها بنجاح.',
            'category_updated' => 'تم تحديث الفئات بنجاح.',
            'category_created' => 'تم إنشاء الفئات بنجاح.',
        ],

        'user' => [
            'required' => 'حقل البريد الإلكتروني مطلوب.',
        ],
        'phone' => [
            'required' => 'حقل الهاتف مطلوب.',
            'unique' => 'الهاتف مستخدم بالفعل.',
            'numeric' => 'يجب أن يكون الهاتف رقمًا.',
            'regex' => 'يجب ان يتكون رقم الهاتف من 10 ارقام و يبدء ب 05'
        ],
        'gender' => [
            'required' => 'حقل الجنس مطلوب.',
            'in' => 'يجب أن يكون الجنس ذكرًا أو أنثى.',
        ],
        'alt' => [
            'string' => 'يجب أن يكون النص البديل نصًا.',
        ],
        'job' => [
            'string' => 'يجب أن يكون الوظيفة نصًا.',
        ],
        'job_id' => [
            'numeric' => 'يجب أن يكون الوظيفة رقمًا.',
        ],
        'old_password' => [
            'required' => 'حقل كلمة المرور القديمة مطلوب.',
            'min' => 'يجب أن تحتوي كلمة المرور القديمة على الأقل على 8 أحرف.',
            'string' => 'يجب أن تكون كلمة المرور القديمة نصًا.',
        ],
        'active' => [
            'required' => 'حقل النشط مطلوب.',
            'in' => 'يجب أن يكون النشط قيمة منطقية (boolean).',
        ],
        'image' => [
            'required' => 'حقل الصورة مطلوب.',
            'image' => 'يجب أن تكون الصورة صورة.',
            'mimes' => 'يجب أن تكون الصورة من نوع: jpeg, jpg, png.',
            'max' => 'يجب ألا تتجاوز الصورة :max كيلوبايت.',
            'string' => 'يجب ارسال رابط الصورة',

        ],
        'type' => [
            'required' => 'حقل النوع مطلوب.',
        ],
        'displayName' => [
            'required' => 'حقل اسم العرض مطلوب.',
            'unique' => 'اسم العرض مستخدم بالفعل.',
        ],
        'description' => [
            'required' => 'حقل الوصف مطلوب.',
            'string' => 'يجب أن يكون الوصف نصًا.',
            'array' => 'يجب ادخال الوصف بالعربية و الانكليزية'
        ],
        'description.en' => [
            'required' => 'حقل الوصف بالانكليزية مطلوب.',
            'string' => 'يجب أن يكون الوصف نصًا.',
        ],
        'description.ar' => [
            'required' => 'حقل الوصف بالعربية مطلوب.',
            'string' => 'يجب أن يكون الوصف نصًا.',
        ],
        'permission' => [
            'required' => 'حقل الصلاحية مطلوب.',
        ],
        'role' => [
            'required' => 'حقل الدور مطلوب.',
            'exists' => 'هذا الدور غير موجود.',
        ],
        'title' => [
            'required' => 'العنوان مطلوب.',
            'string' => 'يجب ان يكون العنوان نصًا.'
        ],
        'body' => [
            'required' => 'لاشعار مطلوب.',
            'string' => 'الاشعار يجب ان يكون نص.'
        ],
        'group_id' => [
            'exists' => 'المجموعة غير صالحة',
        ],
        'category_id' => [
            "required" => "معرف الفئة مطلوب ",
            "uuid" => "معرف الفئة غير صحيح",
            "exists" => "معرف الفئة غير صحيح"
        ]
    ],
    'forget_password' => [
        'sent_code' => 'تم ارسال الكود',
        'error' => 'عليك المحاولة مرة اخرى',
        'expired' => 'لم تنتهي صلاحية الرمز بعد',
        'done' => 'تم تغير الكلمة بنجاح و تسجيل الخروج من جميع الجلسات النشطة',
        'not_done' => 'الرمز المرسل غير صالح',
    ],
    'auth' => [
        'permission' => 'ليس لديك صلاحيات لهذا الاجراء',
        'failed' => 'اسم المستخدم او كلمة السر خاطئة',
        'deleted' => 'هذا الحساب محذوف',
        'logout' => 'تم تسجيل الخروج',
    ],
    'department' => [
        'notfound' => 'هذا القسم غير موجود',
        'try' => 'الرجاء المحاولة لاحقا',
        'done' => 'تم انشاء القسم بنجاح',
        'deleted' => 'القسم محذوف مسبقا',
        'delete' => 'تم حذف القسم'
    ],
    'attributes' => [
        'department_id' => 'معرف القسم',
        'name' => 'الاسم',
        'name.en' => 'الاسم الإنجليزي',
        'name.ar' => 'الاسم العربي',
        'chields' => 'الأبناء',
        'uuid' => 'المعرف الفريد',
        'roleName' => 'الدور الوظيفي',
        'first_name' => 'الاسم الأول',
        'last_name' => 'الاسم الأخير',
        'email' => 'البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'user' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'gender' => 'الجنس',
        'alt' => 'النص البديل',
        'job' => 'الوظيفة',
        'job_id' => 'الوظيفة',
        'old_password' => 'كلمة المرور القديمة',
        'active' => 'النشط',
        'image' => 'الصورة',
        'type' => 'النوع',
        'displayName' => 'اسم العرض',
        'description' => 'الوصف',
        'permission' => 'الصلاحية',
        'role' => 'الدور',
        "category_id" => "معرف الفئة"
    ]
];
