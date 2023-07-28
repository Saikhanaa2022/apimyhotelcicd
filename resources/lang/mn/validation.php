<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Зөвшөөрнө үү',
    'active_url'           => 'Зөв URL хаяг оруулна уу',
    'after'                => ':date - с хойших огноо оруулна уу',
    'after_or_equal'       => ':date эсвэл түүнээс хойших огноо оруулна уу',
    'alpha'                => 'Зөвхөн латин үсэг оруулах боломжтой',
    'alpha_dash'           => 'Латин үсэг, тоо болон зураас оруулах боломжтой',
    'alpha_num'            => 'Латин үсэг болон тоо оруулах боломжтой',
    'array'                => 'Массив байх шаардлагатай',
    'before'               => ':date - с өмнөх огноо оруулна уу',
    'before_or_equal'      => ':date эсвэл түүнээс өмнөх огноо оруулна уу',
    'between'              => [
        'numeric' => ':min-:max хооронд тоо оруулах боломжтой',
        'file'    => ':min-:max килобайт хэмжээтэй файл оруулах боломжтой',
        'string'  => ':min-:max урттай текст оруулах боломжтой',
        'array'   => 'Массивын урт :min-:max байх боломжтой',
    ],
    'boolean'              => 'Үнэн эсвэл худал байх шаардлагатай',
    'confirmed'            => 'Баталгаажуулалт ижил байх шаардлагатай',
    'date'                 => 'Зөвхөн огноо оруулах боломжтой',
    'date_format'          => ':format хэлбэртэй огноо оруулах шаардлагатай',
    'different'            => '(:other) өөр утга оруулах шаардлагатай',
    'digits'               => ':digits урттай тоо оруулах боломжтой',
    'digits_between'       => ':min-:max урттай тоо оруулах боломжтой',
    'dimensions'           => 'Зургийн хэмжээс буруу байна',
    'distinct'             => 'Ялгаатай утга оруулна уу',
    'email'                => 'Зөв и-мэйл хаяг оруулна уу',
    'exists'               => 'Сонгогдсон утга буруу байна',
    'file'                 => 'Зөвхөн файл оруулах боломжтой',
    'filled'               => 'Заавал бөглөх шаардлагатай',
    'gt' => [
        'numeric' => 'Оруулсан утга :value -с их байх ёстой.',
    ],
    'image'                => 'Зөвхөн зураг оруулах боломжтой',
    'in'                   => 'Сонгогдсон утга буруу байна',
    'in_array'             => 'Оруулсан утга :other -д байхгүй байна',
    'integer'              => 'Зөвхөн бүхэл тоо оруулах боломжтой',
    'ip'                   => 'Зөв IP хаяг оруулах шаардлагатай',
    'ipv4'                 => 'Зөв IPv4 хаяг оруулах шаардлагатай',
    'ipv6'                 => 'Зөв IPv6 хаяг оруулах шаардлагатай',
    'json'                 => 'JSON төрлийн утга оруулах шаардлагатай',
    'max'                  => [
        'numeric' => ':max буюу түүнээс бага утга оруулах боломжтой',
        'file'    => ':max килобайтаас бага хэмжээтэй файл оруулах боломжтой',
        'string'  => ':max - с бага урттай текст оруулах боломжтой',
        'array'   => 'Массивын урт хамгийн ихдээ :max байх боломжтой',
    ],
    'mimes'                => ':values төрлийн файл оруулах боломжтой',
    'mimetypes'            => ':values төрлийн файл оруулах боломжтой',
    'min'                  => [
        'numeric' => ':min буюу түүнээс их утга оруулах боломжтой',
        'file'    => ':min килобайтаас их хэмжээтэй файл оруулах боломжтой',
        'string'  => ':min - с их урттай текст оруулах боломжтой',
        'array'   => 'Массивын урт хамгийн багадаа :min байх боломжтой',
    ],
    'not_in'               => 'Сонгогдсон утга буруу байна',
    'not_regex'            => 'Сонгогдсон утга буруу байна',
    'numeric'              => 'Зөвхөн тоо оруулах боломжтой',
    'present'              => 'Заавал бөглөх шаардлагатай',
    'regex'                => 'Шаардлага хангахгүй байна',
    'required'             => 'Талбарт утга оруулна уу', // Return no message
    'required_if'          => '', // Return no message
    'required_unless'      => '', // Return no message
    'required_with'        => '', // Return no message
    'required_with_all'    => '', // Return no message
    'required_without'     => '', // Return no message
    'required_without_all' => '', // Return no message
    'same'                 => 'Нууц үг ижил байх шаардлагатай',
    'size'                 => [
        'numeric' => '::size хэмжээтэй байх шаардлагатай',
        'file'    => ':size килобайт хэмжээтэй байх шаардлагатай',
        'string'  => ':size урттай текст байх шаардлагатай',
        'array'   => 'Массивын урт :size байх шаардлагатай',
    ],
    'string'               => 'Зөвхөн текст оруулах боломжтой',
    'timezone'             => 'Зөвхөн цагийн бүс оруулах боломжтой',
    'unique'               => 'Аль хэдийн бүртгэгдсэн байна',
    'uploaded'             => 'Файл хуулахад алдаа гарлаа',
    'url'                  => 'Зөв URL хаяг оруулна уу',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [],

];
