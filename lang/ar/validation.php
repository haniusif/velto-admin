<?php

return [

    /*
    |--------------------------------------------------------------------------
    | رسائل التحقق من المدخلات
    |--------------------------------------------------------------------------
    |
    | صيغة عربية طبيعية ومختصرة لرسائل التحقق. تُحفَظ العناصر النائبة كما هي
    | (‎:attribute‎، ‎:value‎، ‎:min‎ …) ولا تُترجم.
    |
    */

    'accepted' => 'يجب قبول :attribute.',
    'accepted_if' => 'يجب قبول :attribute عندما يكون :other هو :value.',
    'active_url' => 'أدخل رابطًا صحيحًا في :attribute.',
    'after' => 'يجب أن يكون :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون :attribute تاريخًا في :date أو بعده.',
    'alpha' => 'يجب أن يحتوي :attribute على حروف فقط.',
    'alpha_dash' => 'يجب أن يحتوي :attribute على حروف وأرقام وشرطات فقط.',
    'alpha_num' => 'يجب أن يحتوي :attribute على حروف وأرقام فقط.',
    'any_of' => 'قيمة :attribute غير صحيحة.',
    'array' => 'يجب أن يكون :attribute قائمة.',
    'ascii' => 'يجب أن يحتوي :attribute على حروف وأرقام ورموز إنجليزية فقط.',
    'before' => 'يجب أن يكون :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون :attribute تاريخًا في :date أو قبله.',
    'between' => [
        'array' => 'يجب أن يحتوي :attribute على عدد بين :min و :max عنصرًا.',
        'file' => 'يجب أن يكون حجم :attribute بين :min و :max كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'string' => 'يجب أن يكون طول :attribute بين :min و :max حرفًا.',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute نعم أو لا.',
    'can' => 'يحتوي :attribute على قيمة غير مصرّح بها.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'contains' => 'ينقص :attribute قيمة مطلوبة.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => 'أدخل تاريخًا صحيحًا في :attribute.',
    'date_equals' => 'يجب أن يكون :attribute تاريخًا مساويًا لـ :date.',
    'date_format' => 'يجب أن يطابق :attribute الصيغة :format.',
    'decimal' => 'يجب أن يحتوي :attribute على :decimal منزلة عشرية.',
    'declined' => 'يجب رفض :attribute.',
    'declined_if' => 'يجب رفض :attribute عندما يكون :other هو :value.',
    'different' => 'يجب أن يختلف :attribute عن :other.',
    'digits' => 'يجب أن يتكوّن :attribute من :digits رقمًا.',
    'digits_between' => 'يجب أن يتكوّن :attribute من عدد أرقام بين :min و :max.',
    'dimensions' => 'أبعاد صورة :attribute غير صحيحة.',
    'distinct' => 'قيمة :attribute مكرّرة.',
    'doesnt_contain' => 'يجب ألا يحتوي :attribute على أيٍّ مما يلي: :values.',
    'doesnt_end_with' => 'يجب ألا ينتهي :attribute بأيٍّ مما يلي: :values.',
    'doesnt_start_with' => 'يجب ألا يبدأ :attribute بأيٍّ مما يلي: :values.',
    'email' => 'أدخل بريدًا إلكترونيًا صحيحًا في :attribute.',
    'encoding' => 'يجب أن يكون ترميز :attribute بصيغة :encoding.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد ما يلي: :values.',
    'enum' => 'القيمة المختارة في :attribute غير صحيحة.',
    'exists' => 'القيمة المختارة في :attribute غير صحيحة.',
    'extensions' => 'يجب أن يكون امتداد :attribute أحد التالي: :values.',
    'file' => 'يجب أن يكون :attribute ملفًا.',
    'filled' => 'يجب إدخال قيمة في :attribute.',
    'gt' => [
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عنصرًا.',
        'file' => 'يجب أن يكون حجم :attribute أكبر من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
        'string' => 'يجب أن يكون طول :attribute أكبر من :value حرفًا.',
    ],
    'gte' => [
        'array' => 'يجب أن يحتوي :attribute على :value عنصرًا أو أكثر.',
        'file' => 'يجب أن يكون حجم :attribute :value كيلوبايت أو أكثر.',
        'numeric' => 'يجب أن تكون قيمة :attribute :value أو أكثر.',
        'string' => 'يجب أن يكون طول :attribute :value حرفًا أو أكثر.',
    ],
    'hex_color' => 'أدخل كود لون سداسي صحيح في :attribute.',
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => 'القيمة المختارة في :attribute غير صحيحة.',
    'in_array' => 'يجب أن يكون :attribute موجودًا ضمن :other.',
    'in_array_keys' => 'يجب أن يحتوي :attribute على أحد المفاتيح التالية على الأقل: :values.',
    'integer' => 'يجب أن يكون :attribute رقمًا صحيحًا.',
    'ip' => 'أدخل عنوان IP صحيحًا في :attribute.',
    'ipv4' => 'أدخل عنوان IPv4 صحيحًا في :attribute.',
    'ipv6' => 'أدخل عنوان IPv6 صحيحًا في :attribute.',
    'json' => 'يجب أن يكون :attribute نصًا بصيغة JSON صحيحة.',
    'list' => 'يجب أن يكون :attribute قائمة.',
    'lowercase' => 'يجب أن يكون :attribute بأحرف صغيرة.',
    'lt' => [
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عنصرًا.',
        'file' => 'يجب أن يكون حجم :attribute أقل من :value كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute أقل من :value.',
        'string' => 'يجب أن يكون طول :attribute أقل من :value حرفًا.',
    ],
    'lte' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :value عنصرًا.',
        'file' => 'يجب أن يكون حجم :attribute :value كيلوبايت أو أقل.',
        'numeric' => 'يجب أن تكون قيمة :attribute :value أو أقل.',
        'string' => 'يجب أن يكون طول :attribute :value حرفًا أو أقل.',
    ],
    'mac_address' => 'أدخل عنوان MAC صحيحًا في :attribute.',
    'max' => [
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عنصرًا.',
        'file' => 'يجب ألا يزيد حجم :attribute عن :max كيلوبايت.',
        'numeric' => 'يجب ألا تزيد قيمة :attribute عن :max.',
        'string' => 'يجب ألا يزيد طول :attribute عن :max حرفًا.',
    ],
    'max_digits' => 'يجب ألا يزيد عدد أرقام :attribute عن :max.',
    'mimes' => 'يجب أن يكون :attribute ملفًا من نوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفًا من نوع: :values.',
    'min' => [
        'array' => 'يجب أن يحتوي :attribute على :min عنصرًا على الأقل.',
        'file' => 'يجب ألا يقل حجم :attribute عن :min كيلوبايت.',
        'numeric' => 'يجب ألا تقل قيمة :attribute عن :min.',
        'string' => 'يجب ألا يقل طول :attribute عن :min حرفًا.',
    ],
    'min_digits' => 'يجب ألا يقل عدد أرقام :attribute عن :min.',
    'missing' => 'يجب ألا يكون :attribute موجودًا.',
    'missing_if' => 'يجب ألا يكون :attribute موجودًا عندما يكون :other هو :value.',
    'missing_unless' => 'يجب ألا يكون :attribute موجودًا إلا إذا كان :other هو :value.',
    'missing_with' => 'يجب ألا يكون :attribute موجودًا عند وجود :values.',
    'missing_with_all' => 'يجب ألا يكون :attribute موجودًا عند وجود :values.',
    'multiple_of' => 'يجب أن تكون قيمة :attribute من مضاعفات :value.',
    'not_in' => 'القيمة المختارة في :attribute غير صحيحة.',
    'not_regex' => 'صيغة :attribute غير صحيحة.',
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'password' => [
        'letters' => 'يجب أن تحتوي :attribute على حرف واحد على الأقل.',
        'mixed' => 'يجب أن تحتوي :attribute على حرف كبير وحرف صغير على الأقل.',
        'numbers' => 'يجب أن تحتوي :attribute على رقم واحد على الأقل.',
        'symbols' => 'يجب أن تحتوي :attribute على رمز واحد على الأقل.',
        'uncompromised' => 'ظهرت :attribute في تسريب بيانات. يرجى اختيار كلمة مرور أخرى.',
    ],
    'present' => 'يجب أن يكون :attribute موجودًا.',
    'present_if' => 'يجب أن يكون :attribute موجودًا عندما يكون :other هو :value.',
    'present_unless' => 'يجب أن يكون :attribute موجودًا إلا إذا كان :other هو :value.',
    'present_with' => 'يجب أن يكون :attribute موجودًا عند وجود :values.',
    'present_with_all' => 'يجب أن يكون :attribute موجودًا عند وجود :values.',
    'prohibited' => ':attribute غير مسموح به.',
    'prohibited_if' => ':attribute غير مسموح به عندما يكون :other هو :value.',
    'prohibited_if_accepted' => ':attribute غير مسموح به عند قبول :other.',
    'prohibited_if_declined' => ':attribute غير مسموح به عند رفض :other.',
    'prohibited_unless' => ':attribute غير مسموح به إلا إذا كان :other ضمن :values.',
    'prohibits' => ':attribute يمنع وجود :other.',
    'regex' => 'صيغة :attribute غير صحيحة.',
    'required' => 'يرجى إدخال :attribute.',
    'required_array_keys' => 'يجب أن يحتوي :attribute على قيم لـ: :values.',
    'required_if' => 'يرجى إدخال :attribute عندما يكون :other هو :value.',
    'required_if_accepted' => 'يرجى إدخال :attribute عند قبول :other.',
    'required_if_declined' => 'يرجى إدخال :attribute عند رفض :other.',
    'required_unless' => 'يرجى إدخال :attribute إلا إذا كان :other ضمن :values.',
    'required_with' => 'يرجى إدخال :attribute عند وجود :values.',
    'required_with_all' => 'يرجى إدخال :attribute عند وجود :values.',
    'required_without' => 'يرجى إدخال :attribute عند عدم وجود :values.',
    'required_without_all' => 'يرجى إدخال :attribute عند عدم وجود أيٍّ من :values.',
    'same' => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'array' => 'يجب أن يحتوي :attribute على :size عنصرًا.',
        'file' => 'يجب أن يكون حجم :attribute :size كيلوبايت.',
        'numeric' => 'يجب أن تكون قيمة :attribute :size.',
        'string' => 'يجب أن يكون طول :attribute :size حرفًا.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بأحد ما يلي: :values.',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'timezone' => 'أدخل نطاقًا زمنيًا صحيحًا في :attribute.',
    'unique' => ':attribute مستخدم من قبل.',
    'uploaded' => 'تعذّر رفع :attribute.',
    'uppercase' => 'يجب أن يكون :attribute بأحرف كبيرة.',
    'url' => 'أدخل رابطًا صحيحًا في :attribute.',
    'ulid' => 'أدخل معرّف ULID صحيحًا في :attribute.',
    'uuid' => 'أدخل معرّف UUID صحيحًا في :attribute.',

    /*
    |--------------------------------------------------------------------------
    | رسائل مخصّصة
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | أسماء الحقول
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الجوال',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'city' => 'المدينة',
        'area' => 'الحي',
        'address' => 'العنوان',
        'amount' => 'المبلغ',
        'price' => 'السعر',
        'note' => 'الملاحظة',
        'notes' => 'الملاحظات',
    ],

];
