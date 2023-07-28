<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UpdatedTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->insert([
            'id' => 1,
            'name' => 'Монгол',
            'code' => 'MN',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Initial languages
        DB::table('languages')->insert([
            [
                'id' => 1,
                'locale' => 'mn',
                'name' => 'Mongolian',
                'name_national' => 'Монгол хэл',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'locale' => 'en',
                'name' => 'English',
                'name_national' => 'English',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        // Initial provinces
        $provinces = [
            [
                'country_id' => 1,
                "name" => "Улаанбаатар",
                "international" => "Улаанбаатар",
                "location" => [
                    "lat" => "47.913138",
                    "lng" => "106.920123",
                ],
                "districts" => [
                    [
                        "province_id" => "1",
                        "name" => "Багануур",
                        "international" => "Багануур",
                        "location" => [
                            "lat" => "47.8239867497",
                            "lng" => "108.34324421",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Багахангай",
                        "international" => "Багахангай",
                        "location" => [
                            "lat" => "47.3746138358",
                            "lng" => "107.445745149",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Баянгол",
                        "international" => "Баянгол",
                        "location" => [
                            "lat" => "47.9098272236",
                            "lng" => "106.845025304",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Баянзүрх",
                        "international" => "Баянзүрх",
                        "location" => [
                            "lat" => "47.945760064",
                            "lng" => "107.140014877",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Налайх",
                        "international" => "Налайх",
                        "location" => [
                            "lat" => "47.8136287423",
                            "lng" => "107.420770286",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Сонгинохайрхан",
                        "international" => "Сонгинохайрхан",
                        "location" => [
                            "lat" => "48.0690761482",
                            "lng" => "106.643363188",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Сүхбаатар",
                        "international" => "Сүхбаатар",
                        "location" => [
                            "lat" => "48.0789054465",
                            "lng" => "106.950886461",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Хан-Уул",
                        "international" => "Хан-Уул",
                        "location" => [
                            "lat" => "47.8053718371",
                            "lng" => "106.736991025",
                        ],
                    ],
                    [
                        "province_id" => "1",
                        "name" => "Чингэлтэй",
                        "international" => "Чингэлтэй",
                        "location" => [
                            "lat" => "48.0280923509",
                            "lng" => "106.882365333",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Архангай",
                "international" => "Архангай",
                "location" => [
                    "lat" => "47.3120331833",
                    "lng" => "101.538757324",
                ],
                "districts" => [
                    [
                        "province_id" => "2",
                        "name" => "Батцэнгэл",
                        "international" => "Батцэнгэл",
                        "location" => [
                            "lat" => "47.7951245907961",
                            "lng" => "101.974153518677",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Булган",
                        "international" => "Булган",
                        "location" => [
                            "lat" => "47.3172102888087",
                            "lng" => "101.113529205322",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Жаргалант",
                        "international" => "Жаргалант",
                        "location" => [
                            "lat" => "48.725906593837",
                            "lng" => "100.777759552002",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Их тамир",
                        "international" => "Их тамир",
                        "location" => [
                            "lat" => "47.5944143136177",
                            "lng" => "101.198415752256",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Өлгийнуур",
                        "international" => "Өлгийнуур",
                        "location" => [
                            "lat" => "47.6671214315393",
                            "lng" => "102.550420761108",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Өлзийт",
                        "international" => "Өлзийт",
                        "location" => [
                            "lat" => "48.1001520183643",
                            "lng" => "102.55651473999",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Өндөр-Улаан",
                        "international" => "Өндөр-Улаан",
                        "location" => [
                            "lat" => "48.0440908314395",
                            "lng" => "100.506019592285",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Тариат",
                        "international" => "Тариат",
                        "location" => [
                            "lat" => "48.1581847515391",
                            "lng" => "99.8826313018799",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Төвшрүүлэх",
                        "international" => "Төвшрүүлэх",
                        "location" => [
                            "lat" => "47.3778364204214",
                            "lng" => "101.907076835632",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Хайрхан",
                        "international" => "Хайрхан",
                        "location" => [
                            "lat" => "48.6094052531396",
                            "lng" => "101.942331790924",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Хангай",
                        "international" => "Хангай",
                        "location" => [
                            "lat" => "47.8540625375354",
                            "lng" => "99.4255828857422",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Хашаат",
                        "international" => "Хашаат",
                        "location" => [
                            "lat" => "47.4509892162818",
                            "lng" => "103.147716522217",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Хотонт",
                        "international" => "Хотонт",
                        "location" => [
                            "lat" => "47.3646412651095",
                            "lng" => "102.471628189087",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Цахир",
                        "international" => "Цахир",
                        "location" => [
                            "lat" => "48.0730538454847",
                            "lng" => "98.8545620441437",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Цэнхэр",
                        "international" => "Цэнхэр",
                        "location" => [
                            "lat" => "47.4443068813173",
                            "lng" => "101.750907897949",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Цэцэрлэг",
                        "international" => "Цэцэрлэг",
                        "location" => [
                            "lat" => "48.8825820446373",
                            "lng" => "101.240515708923",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Чулуут",
                        "international" => "Чулуут",
                        "location" => [
                            "lat" => "47.5366451471895",
                            "lng" => "100.225138664246",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Эрдэнэбулган",
                        "international" => "Эрдэнэбулган",
                        "location" => [
                            "lat" => "47.4777680777976",
                            "lng" => "101.451787948608",
                        ],
                    ],
                    [
                        "province_id" => "2",
                        "name" => "Эрдэнэмандал",
                        "international" => "Эрдэнэмандал",
                        "location" => [
                            "lat" => "48.5292813166367",
                            "lng" => "101.376214027405",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Баян-Өлгий",
                "international" => "Баян-Өлгий",
                "location" => [
                    "lat" => "48.6787756294",
                    "lng" => "89.9005297852",
                ],
                "districts" => [
                    [
                        "province_id" => "3",
                        "name" => "Алтай",
                        "international" => "Алтай",
                        "location" => [
                            "lat" => "48.3032081219393",
                            "lng" => "89.5139408111572",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Алтанцөгц",
                        "international" => "Алтанцөгц",
                        "location" => [
                            "lat" => "49.058569955302",
                            "lng" => "90.4513835906982",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Баяннуур",
                        "international" => "Баяннуур",
                        "location" => [
                            "lat" => "48.9500418767633",
                            "lng" => "91.1614179611206",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Бугат",
                        "international" => "Бугат",
                        "location" => [
                            "lat" => "48.9483226701701",
                            "lng" => "90.0229597091675",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Булган",
                        "international" => "Булган",
                        "location" => [
                            "lat" => "46.9262052904524",
                            "lng" => "91.0890626907348",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Буянт",
                        "international" => "Буянт",
                        "location" => [
                            "lat" => "48.577515717867",
                            "lng" => "89.5484018325806",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Дэлүүн",
                        "international" => "Дэлүүн",
                        "location" => [
                            "lat" => "47.8661559130778",
                            "lng" => "90.6963872909546",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Ногооннуур",
                        "international" => "Ногооннуур",
                        "location" => [
                            "lat" => "49.6178283121112",
                            "lng" => "90.2393817901611",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Өлгий",
                        "international" => "Өлгий",
                        "location" => [
                            "lat" => "48.9705832207603",
                            "lng" => "89.9660110473633",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Сагсай",
                        "international" => "Сагсай",
                        "location" => [
                            "lat" => "48.9103159035553",
                            "lng" => "89.6539306640625",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Толбо",
                        "international" => "Толбо",
                        "location" => [
                            "lat" => "48.4134080146792",
                            "lng" => "90.2912020683289",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Улаанхус",
                        "international" => "Улаанхус",
                        "location" => [
                            "lat" => "49.0411313343146",
                            "lng" => "89.4385170936585",
                        ],
                    ],
                    [
                        "province_id" => "3",
                        "name" => "Цэнгэл",
                        "international" => "Цэнгэл",
                        "location" => [
                            "lat" => "48.9362442788337",
                            "lng" => "89.1361355781555",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Баянхонгор",
                "international" => "Баянхонгор",
                "location" => [
                    "lat" => "44.6662763442",
                    "lng" => "100.118408203",
                ],
                "districts" => [
                    [
                        "province_id" => "4",
                        "name" => "Баацагаан",
                        "international" => "Баацагаан",
                        "location" => [
                            "lat" => "45.5556505400855",
                            "lng" => "99.4358825683594",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баян-Овоо",
                        "international" => "Баян-Овоо",
                        "location" => [
                            "lat" => "46.109956890439",
                            "lng" => "100.14587402344",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баян-Өндөр",
                        "international" => "Баян-Өндөр",
                        "location" => [
                            "lat" => "43.692296608267",
                            "lng" => "98.805541992188",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баянбулаг",
                        "international" => "Баянбулаг",
                        "location" => [
                            "lat" => "45.0036511568719",
                            "lng" => "98.9373779296875",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баянговь",
                        "international" => "Баянговь",
                        "location" => [
                            "lat" => "44.7350736281132",
                            "lng" => "100.391628742218",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баянлиг",
                        "international" => "Баянлиг",
                        "location" => [
                            "lat" => "44.551151345991",
                            "lng" => "100.831446647644",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баянхонгор",
                        "international" => "Баянхонгор",
                        "location" => [
                            "lat" => "46.143685745982",
                            "lng" => "100.62103271484",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Баянцагаан",
                        "international" => "Баянцагаан",
                        "location" => [
                            "lat" => "45.5556505400855",
                            "lng" => "99.4354104995727",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Богд",
                        "international" => "Богд",
                        "location" => [
                            "lat" => "45.1986714470399",
                            "lng" => "100.773296356201",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Бөмбөгөр",
                        "international" => "Бөмбөгөр",
                        "location" => [
                            "lat" => "46.2093289538182",
                            "lng" => "99.6001195907593",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Бууцагаан",
                        "international" => "Бууцагаан",
                        "location" => [
                            "lat" => "46.1748381509269",
                            "lng" => "98.6929321289062",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Галуут",
                        "international" => "Галуут",
                        "location" => [
                            "lat" => "46.6992589783986",
                            "lng" => "100.143642425537",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Гурванбулаг",
                        "international" => "Гурванбулаг",
                        "location" => [
                            "lat" => "47.2340816807629",
                            "lng" => "98.5638427734375",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Жаргалант",
                        "international" => "Жаргалант",
                        "location" => [
                            "lat" => "47.0191207429378",
                            "lng" => "99.4801712036133",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Жинст",
                        "international" => "Жинст",
                        "location" => [
                            "lat" => "45.4111952064446",
                            "lng" => "100.57502746582",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Заг",
                        "international" => "Заг",
                        "location" => [
                            "lat" => "46.9421174931845",
                            "lng" => "99.1657304763794",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Өлзийт",
                        "international" => "Өлзийт",
                        "location" => [
                            "lat" => "45.667653105286",
                            "lng" => "100.84075927734",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Хүрээмарал",
                        "international" => "Хүрээмарал",
                        "location" => [
                            "lat" => "46.4083037402104",
                            "lng" => "98.284592628479",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Шинэжинст",
                        "international" => "Шинэжинст",
                        "location" => [
                            "lat" => "44.5394370612712",
                            "lng" => "99.2670965194702",
                        ],
                    ],
                    [
                        "province_id" => "4",
                        "name" => "Эрдэнэцогт",
                        "international" => "Эрдэнэцогт",
                        "location" => [
                            "lat" => "46.4186299363495",
                            "lng" => "100.822606086731",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Булган",
                "international" => "Булган",
                "location" => [
                    "lat" => "48.7280990171",
                    "lng" => "102.999055176",
                ],
                "districts" => [
                    [
                        "province_id" => "5",
                        "name" => "Баян-Агт",
                        "international" => "Баян-Агт",
                        "location" => [
                            "lat" => "49.0391620734755",
                            "lng" => "102.084274291992",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Баяннуур",
                        "international" => "Баяннуур",
                        "location" => [
                            "lat" => "47.8307028221974",
                            "lng" => "104.444489479065",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Бугат",
                        "international" => "Бугат",
                        "location" => [
                            "lat" => "48.0670339190479",
                            "lng" => "103.675875663757",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Булган",
                        "international" => "Булган",
                        "location" => [
                            "lat" => "48.8241580560601",
                            "lng" => "103.519835472107",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Бүрэгхангай",
                        "international" => "Бүрэгхангай",
                        "location" => [
                            "lat" => "48.2512265104176",
                            "lng" => "103.876118659973",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Гурванбулаг",
                        "international" => "Гурванбулаг",
                        "location" => [
                            "lat" => "47.7432482784032",
                            "lng" => "103.482542037964",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Дашинчилэн",
                        "international" => "Дашинчилэн",
                        "location" => [
                            "lat" => "47.8515859269655",
                            "lng" => "104.04670715332",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Могод",
                        "international" => "Могод",
                        "location" => [
                            "lat" => "48.2761671473887",
                            "lng" => "102.989315986633",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Орхон",
                        "international" => "Орхон",
                        "location" => [
                            "lat" => "48.6318875460055",
                            "lng" => "103.535370826721",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Рашаант",
                        "international" => "Рашаант",
                        "location" => [
                            "lat" => "47.3755987080566",
                            "lng" => "103.948602676392",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Сайхан",
                        "international" => "Сайхан",
                        "location" => [
                            "lat" => "48.6633601065224",
                            "lng" => "102.628569602966",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Сэлэнгэ",
                        "international" => "Сэлэнгэ",
                        "location" => [
                            "lat" => "49.4537867991656",
                            "lng" => "103.981046676636",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Тэшиг",
                        "international" => "Тэшиг",
                        "location" => [
                            "lat" => "49.9461801939323",
                            "lng" => "102.658717632294",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Хангал",
                        "international" => "Хангал",
                        "location" => [
                            "lat" => "49.3194995474697",
                            "lng" => "104.384794235229",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Хишиг-Өндөр",
                        "international" => "Хишиг-Өндөр",
                        "location" => [
                            "lat" => "48.2964706335923",
                            "lng" => "103.432674407959",
                        ],
                    ],
                    [
                        "province_id" => "5",
                        "name" => "Хутаг-Өндөр",
                        "international" => "Хутаг-Өндөр",
                        "location" => [
                            "lat" => "49.3915356696856",
                            "lng" => "102.706117630005",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Говь-Алтай",
                "international" => "Говь-Алтай",
                "location" => [
                    "lat" => "45.6388142029",
                    "lng" => "96.0392456055",
                ],
                "districts" => [
                    [
                        "province_id" => "6",
                        "name" => "Алтай",
                        "international" => "Алтай",
                        "location" => [
                            "lat" => "44.617492856605",
                            "lng" => "94.918656349182",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Баян-Уул",
                        "international" => "Баян-Уул",
                        "location" => [
                            "lat" => "46.992957935681",
                            "lng" => "95.196876525879",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Бигэр",
                        "international" => "Бигэр",
                        "location" => [
                            "lat" => "45.707378049266",
                            "lng" => "97.177720069885",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Бугат",
                        "international" => "Бугат",
                        "location" => [
                            "lat" => "45.557964343814",
                            "lng" => "94.365606307983",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Дарви",
                        "international" => "Дарви",
                        "location" => [
                            "lat" => "46.464881483934",
                            "lng" => "94.102492332458",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Дэлгэр",
                        "international" => "Дэлгэр",
                        "location" => [
                            "lat" => "46.352955769189",
                            "lng" => "97.370109558105",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Есөнбулаг",
                        "international" => "Есөнбулаг",
                        "location" => [
                            "lat" => "46.37257600464",
                            "lng" => "96.258945465088",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Жаргалан",
                        "international" => "Жаргалан",
                        "location" => [
                            "lat" => "46.978378465723",
                            "lng" => "95.92583656311",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Тайшир",
                        "international" => "Тайшир",
                        "location" => [
                            "lat" => "46.71173693095",
                            "lng" => "96.527509689331",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Тонхил",
                        "international" => "Тонхил",
                        "location" => [
                            "lat" => "46.30668336774",
                            "lng" => "93.902742862701",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Төгрөг",
                        "international" => "Төгрөг",
                        "location" => [
                            "lat" => "45.825778905159",
                            "lng" => "94.817891120911",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Халиун",
                        "international" => "Халиун",
                        "location" => [
                            "lat" => "45.929527975634",
                            "lng" => "96.16348028183",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Хөхморьт",
                        "international" => "Хөхморьт",
                        "location" => [
                            "lat" => "47.416937456635",
                            "lng" => "94.304237365723",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Цогт",
                        "international" => "Цогт",
                        "location" => [
                            "lat" => "45.350094420411",
                            "lng" => "96.643295288086",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Цээл",
                        "international" => "Цээл",
                        "location" => [
                            "lat" => "45.549880640496",
                            "lng" => "95.854682922363",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Чандмань",
                        "international" => "Чандмань",
                        "location" => [
                            "lat" => "45.334016994038",
                            "lng" => "97.987532615662",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Шарга",
                        "international" => "Шарга",
                        "location" => [
                            "lat" => "46.269079538837",
                            "lng" => "95.271892547607",
                        ],
                    ],
                    [
                        "province_id" => "6",
                        "name" => "Эрдэнэ",
                        "international" => "Эрдэнэ",
                        "location" => [
                            "lat" => "45.161736029055",
                            "lng" => "97.70450592041",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Говьсүмбэр",
                "international" => "Говьсүмбэр",
                "location" => [
                    "lat" => "46.4278887414",
                    "lng" => "108.811340332",
                ],
                "districts" => [
                    [
                        "province_id" => "7",
                        "name" => "Баянтал",
                        "international" => "Баянтал",
                        "location" => [
                            "lat" => "46.5599816327046",
                            "lng" => "108.308544158936",
                        ],
                    ],
                    [
                        "province_id" => "7",
                        "name" => "Сүмбэр",
                        "international" => "Сүмбэр",
                        "location" => [
                            "lat" => "46.360227183849",
                            "lng" => "108.363733291626",
                        ],
                    ],
                    [
                        "province_id" => "7",
                        "name" => "Шивээговь",
                        "international" => "Шивээговь",
                        "location" => [
                            "lat" => "46.10388728915",
                            "lng" => "108.61307144165",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Дархан-Уул",
                "international" => "Дархан-Уул",
                "location" => [
                    "lat" => "49.4594667404",
                    "lng" => "106.33392334",
                ],
                "districts" => [
                    [
                        "province_id" => "8",
                        "name" => "Дархан",
                        "international" => "Дархан",
                        "location" => [
                            "lat" => "49.465502390077",
                            "lng" => "105.97017288208",
                        ],
                    ],
                    [
                        "province_id" => "8",
                        "name" => "Орхон",
                        "international" => "Орхон",
                        "location" => [
                            "lat" => "49.837899418104",
                            "lng" => "106.13913059235",
                        ],
                    ],
                    [
                        "province_id" => "8",
                        "name" => "Хонгор",
                        "international" => "Хонгор",
                        "location" => [
                            "lat" => "49.312029935636",
                            "lng" => "105.93180656433",
                        ],
                    ],
                    [
                        "province_id" => "8",
                        "name" => "Шарын гол",
                        "international" => "Шарын гол",
                        "location" => [
                            "lat" => "49.257386221284",
                            "lng" => "106.43159866333",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Дорноговь",
                "international" => "Дорноговь",
                "location" => [
                    "lat" => "43.995226343",
                    "lng" => "109.99855957",
                ],
                "districts" => [
                    [
                        "province_id" => "9",
                        "name" => "Айраг",
                        "international" => "Айраг",
                        "location" => [
                            "lat" => "45.8021188385604",
                            "lng" => "109.309844970703",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Алтанширээ",
                        "international" => "Алтанширээ",
                        "location" => [
                            "lat" => "45.5415553144803",
                            "lng" => "110.467829704285",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Даланжаргалан",
                        "international" => "Даланжаргалан",
                        "location" => [
                            "lat" => "45.9310055037561",
                            "lng" => "109.090461730957",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Дэлгэрэх",
                        "international" => "Дэлгэрэх",
                        "location" => [
                            "lat" => "45.8019393306157",
                            "lng" => "111.215415000916",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Замын-Үүд",
                        "international" => "Замын-Үүд",
                        "location" => [
                            "lat" => "43.7164342514261",
                            "lng" => "111.899914741516",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Иххэт",
                        "international" => "Иххэт",
                        "location" => [
                            "lat" => "46.3754185033282",
                            "lng" => "110.096333026886",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Мандах",
                        "international" => "Мандах",
                        "location" => [
                            "lat" => "44.4064235574205",
                            "lng" => "108.252239227295",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Өргөн",
                        "international" => "Өргөн",
                        "location" => [
                            "lat" => "44.7282139343594",
                            "lng" => "110.777614116669",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Сайншанд",
                        "international" => "Сайншанд",
                        "location" => [
                            "lat" => "44.9122739124316",
                            "lng" => "110.137038230896",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Сайхандулаан",
                        "international" => "Сайхандулаан",
                        "location" => [
                            "lat" => "44.6956726645258",
                            "lng" => "109.022612571716",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Улаанбадрах",
                        "international" => "Улаанбадрах",
                        "location" => [
                            "lat" => "43.8696522741395",
                            "lng" => "110.421760082245",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Хатанбулаг",
                        "international" => "Хатанбулаг",
                        "location" => [
                            "lat" => "43.1521309965163",
                            "lng" => "109.140629768372",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Хөвсгөл",
                        "international" => "Хөвсгөл",
                        "location" => [
                            "lat" => "43.6082394496433",
                            "lng" => "109.643683433533",
                        ],
                    ],
                    [
                        "province_id" => "9",
                        "name" => "Эрдэнэ",
                        "international" => "Эрдэнэ",
                        "location" => [
                            "lat" => "44.4448872885262",
                            "lng" => "111.092076301575",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Дорнод",
                "international" => "Дорнод",
                "location" => [
                    "lat" => "48.0842367",
                    "lng" => "114.4629458",
                ],
                "districts" => [
                    [
                        "province_id" => "10",
                        "name" => "Баян-Уул",
                        "international" => "Баян-Уул",
                        "location" => [
                            "lat" => "49.1217617752884",
                            "lng" => "112.670373916626",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Баяндун",
                        "international" => "Баяндун",
                        "location" => [
                            "lat" => "49.2431274701762",
                            "lng" => "113.374271392822",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Баянтүмэн",
                        "international" => "Баянтүмэн",
                        "location" => [
                            "lat" => "47.75699365863",
                            "lng" => "114.72747802734",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Булган",
                        "international" => "Булган",
                        "location" => [
                            "lat" => "47.319952821211",
                            "lng" => "114.78515625",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Гурванзагал",
                        "international" => "Гурванзагал",
                        "location" => [
                            "lat" => "49.14755215358",
                            "lng" => "114.874291419983",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Дашбалбар",
                        "international" => "Дашбалбар",
                        "location" => [
                            "lat" => "49.5472103611187",
                            "lng" => "114.405570030212",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Матад",
                        "international" => "Матад",
                        "location" => [
                            "lat" => "46.9517562882325",
                            "lng" => "115.29153585434",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Сэргэлэн",
                        "international" => "Сэргэлэн",
                        "location" => [
                            "lat" => "48.5096534090658",
                            "lng" => "114.033622741699",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Халхгол",
                        "international" => "Халхгол",
                        "location" => [
                            "lat" => "47.6306792932148",
                            "lng" => "118.620736598969",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Хөлөнбуйр",
                        "international" => "Хөлөнбуйр",
                        "location" => [
                            "lat" => "47.923992301074",
                            "lng" => "112.957370281219",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Хэрлэн",
                        "international" => "Хэрлэн",
                        "location" => [
                            "lat" => "48.02",
                            "lng" => "114.6",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Цагаан-Овоо",
                        "international" => "Цагаан-Овоо",
                        "location" => [
                            "lat" => "48.5642828218701",
                            "lng" => "113.240203857422",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Чойбалсан",
                        "international" => "Чойбалсан",
                        "location" => [
                            "lat" => "48.4394794466477",
                            "lng" => "114.873197078705",
                        ],
                    ],
                    [
                        "province_id" => "10",
                        "name" => "Чулуунхороот",
                        "international" => "Чулуунхороот",
                        "location" => [
                            "lat" => "49.8691935002872",
                            "lng" => "115.700883865356",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Дундговь",
                "international" => "Дундговь",
                "location" => [
                    "lat" => "45.1277969158",
                    "lng" => "106.837792969",
                ],
                "districts" => [
                    [
                        "province_id" => "11",
                        "name" => "Адаацаг",
                        "international" => "Адаацаг",
                        "location" => [
                            "lat" => "46.3913456018713",
                            "lng" => "105.734868049622",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Баянжаргалан",
                        "international" => "Баянжаргалан",
                        "location" => [
                            "lat" => "45.7572388214047",
                            "lng" => "107.996163368225",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Говьугтаал",
                        "international" => "Говьугтаал",
                        "location" => [
                            "lat" => "46.04994399304",
                            "lng" => "107.490363121033",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Гурвансайхан",
                        "international" => "Гурвансайхан",
                        "location" => [
                            "lat" => "45.5293205590078",
                            "lng" => "107.039666175842",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Дэлгэрхангай",
                        "international" => "Дэлгэрхангай",
                        "location" => [
                            "lat" => "45.2422915045381",
                            "lng" => "104.805407524109",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Дэлгэрцогт",
                        "international" => "Дэлгэрцогт",
                        "location" => [
                            "lat" => "46.1250393761681",
                            "lng" => "106.37246131897",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Дэрэн",
                        "international" => "Дэрэн",
                        "location" => [
                            "lat" => "46.2105762774475",
                            "lng" => "106.707844734192",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Луус",
                        "international" => "Луус",
                        "location" => [
                            "lat" => "45.5070687293699",
                            "lng" => "105.759372711182",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Өлзийт",
                        "international" => "Өлзийт",
                        "location" => [
                            "lat" => "45.117514876883",
                            "lng" => "107.10845947266",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Өндөршил",
                        "international" => "Өндөршил",
                        "location" => [
                            "lat" => "45.231654077557",
                            "lng" => "108.280069828033",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Сайнцагаан",
                        "international" => "Сайнцагаан",
                        "location" => [
                            "lat" => "45.7645891065326",
                            "lng" => "106.271266937256",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Сайхан-Овоо",
                        "international" => "Сайхан-Овоо",
                        "location" => [
                            "lat" => "45.4589867862411",
                            "lng" => "103.901867866516",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Хулд",
                        "international" => "Хулд",
                        "location" => [
                            "lat" => "45.2224656062722",
                            "lng" => "105.558786392212",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Цагаандэлгэр",
                        "international" => "Цагаандэлгэр",
                        "location" => [
                            "lat" => "46.4074012146941",
                            "lng" => "107.641060352325",
                        ],
                    ],
                    [
                        "province_id" => "11",
                        "name" => "Эрдэнэдалай",
                        "international" => "Эрдэнэдалай",
                        "location" => [
                            "lat" => "46.0080062841811",
                            "lng" => "104.944903850555",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Завхан",
                "international" => "Завхан",
                "location" => [
                    "lat" => "48.1564897419",
                    "lng" => "96.6912963867",
                ],
                "districts" => [
                    [
                        "province_id" => "12",
                        "name" => "Алдархаан",
                        "international" => "Алдархаан",
                        "location" => [
                            "lat" => "47.6384295319465",
                            "lng" => "96.5325736999512",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Асгат",
                        "international" => "Асгат",
                        "location" => [
                            "lat" => "49.4106102128929",
                            "lng" => "96.6055727005005",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Баянтэс",
                        "international" => "Баянтэс",
                        "location" => [
                            "lat" => "49.7005586428626",
                            "lng" => "96.3614058494568",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Баянхайрхан",
                        "international" => "Баянхайрхан",
                        "location" => [
                            "lat" => "49.3729846474515",
                            "lng" => "96.4144706726074",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Дөрвөлжин",
                        "international" => "Дөрвөлжин",
                        "location" => [
                            "lat" => "94.9989938735962",
                            "lng" => "94.9989938735962",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Завханмандал",
                        "international" => "Завханмандал",
                        "location" => [
                            "lat" => "48.3261545855473",
                            "lng" => "95.1051235198975",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Идэр",
                        "international" => "Идэр",
                        "location" => [
                            "lat" => "48.2204982384311",
                            "lng" => "97.378134727478",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Их-Уул",
                        "international" => "Их-Уул",
                        "location" => [
                            "lat" => "48.7197630315027",
                            "lng" => "98.7963581085205",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Нөмрөг",
                        "international" => "Нөмрөг",
                        "location" => [
                            "lat" => "48.8729858771179",
                            "lng" => "96.9613409042358",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Отгон",
                        "international" => "Отгон",
                        "location" => [
                            "lat" => "47.2098318861809",
                            "lng" => "97.6074314117432",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Сантмаргац",
                        "international" => "Сантмаргац",
                        "location" => [
                            "lat" => "48.5879348957964",
                            "lng" => "95.4335975646973",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Сонгино",
                        "international" => "Сонгино",
                        "location" => [
                            "lat" => "49.0238554459337",
                            "lng" => "95.9315872192383",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Тосонцэнгэл",
                        "international" => "Тосонцэнгэл",
                        "location" => [
                            "lat" => "48.7522276472236",
                            "lng" => "98.2710742950439",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Түдэвтэй",
                        "international" => "Түдэвтэй",
                        "location" => [
                            "lat" => "48.9863568345013",
                            "lng" => "96.5463066101074",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Тэлмэн",
                        "international" => "Тэлмэн",
                        "location" => [
                            "lat" => "48.6437416807731",
                            "lng" => "97.6136112213135",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Тэс",
                        "international" => "Тэс",
                        "location" => [
                            "lat" => "49.6558495026979",
                            "lng" => "95.7900524139404",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Улиастай",
                        "international" => "Улиастай",
                        "location" => [
                            "lat" => "47.7265653373467",
                            "lng" => "96.8543529510498",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Ургамал",
                        "international" => "Ургамал",
                        "location" => [
                            "lat" => "48.51521139439",
                            "lng" => "94.2935299873352",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Цагаанхайрхан",
                        "international" => "Цагаанхайрхан",
                        "location" => [
                            "lat" => "47.4957628841138",
                            "lng" => "96.7951726913452",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Цагаанчулуут",
                        "international" => "Цагаанчулуут",
                        "location" => [
                            "lat" => "47.1117287500827",
                            "lng" => "96.6722202301026",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Цэцэн-Уул",
                        "international" => "Цэцэн-Уул",
                        "location" => [
                            "lat" => "48.750954365174",
                            "lng" => "96.0002517700195",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Шилүүстэй",
                        "international" => "Шилүүстэй",
                        "location" => [
                            "lat" => "46.7987668194016",
                            "lng" => "97.1981906890869",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Эрдэнэхайрхан",
                        "international" => "Эрдэнэхайрхан",
                        "location" => [
                            "lat" => "48.1233901693396",
                            "lng" => "95.717396736145",
                        ],
                    ],
                    [
                        "province_id" => "12",
                        "name" => "Яруу",
                        "international" => "Яруу",
                        "location" => [
                            "lat" => "48.1157121401887",
                            "lng" => "96.7660760879517",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Орхон",
                "international" => "Орхон",
                "location" => [
                    "lat" => "48.9620453587",
                    "lng" => "104.501953125",
                ],
                "districts" => [
                    [
                        "province_id" => "13",
                        "name" => "Баян-Өндөр",
                        "international" => "Баян-Өндөр",
                        "location" => [
                            "lat" => "49.0389370101292",
                            "lng" => "104.141807556152",
                        ],
                    ],
                    [
                        "province_id" => "13",
                        "name" => "Жаргалант",
                        "international" => "Жаргалант",
                        "location" => [
                            "lat" => "49.0560248604141",
                            "lng" => "104.402089118958",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Өвөрхангай",
                "international" => "Өвөрхангай",
                "location" => [
                    "lat" => "45.6234197376",
                    "lng" => "103.186340332",
                ],
                "districts" => [
                    [
                        "province_id" => "14",
                        "name" => "Арвайхээр",
                        "international" => "Арвайхээр",
                        "location" => [
                            "lat" => "46.238752301106",
                            "lng" => "102.90618896484",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Баруунбаян-Улаан",
                        "international" => "Баруунбаян-Улаан",
                        "location" => [
                            "lat" => "45.1755782739452",
                            "lng" => "101.418786048889",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Бат-Өлзий",
                        "international" => "Бат-Өлзий",
                        "location" => [
                            "lat" => "46.8182704400559",
                            "lng" => "102.246065139771",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Баян-Өндөр",
                        "international" => "Баян-Өндөр",
                        "location" => [
                            "lat" => "46.4985695093912",
                            "lng" => "104.117131233215",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Баянгол",
                        "international" => "Баянгол",
                        "location" => [
                            "lat" => "45.6733756283968",
                            "lng" => "103.627971410751",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Богд",
                        "international" => "Богд",
                        "location" => [
                            "lat" => "45.1980666373102",
                            "lng" => "100.77308177948",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Бүрд",
                        "international" => "Бүрд",
                        "location" => [
                            "lat" => "46.9823896802489",
                            "lng" => "103.785653114319",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Гучин-Ус",
                        "international" => "Гучин-Ус",
                        "location" => [
                            "lat" => "45.4623580719666",
                            "lng" => "102.422919273376",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Есөнзүйл",
                        "international" => "Есөнзүйл",
                        "location" => [
                            "lat" => "46.7599147583275",
                            "lng" => "103.51794719696",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Зүүнбаян-Улаан",
                        "international" => "Зүүнбаян-Улаан",
                        "location" => [
                            "lat" => "46.5218434259599",
                            "lng" => "102.593636512756",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Нарийнтээл",
                        "international" => "Нарийнтээл",
                        "location" => [
                            "lat" => "45.7017436352425",
                            "lng" => "101.577658653259",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Өлзийт",
                        "international" => "Өлзийт",
                        "location" => [
                            "lat" => "46.6103585182182",
                            "lng" => "103.546528816223",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Сант",
                        "international" => "Сант",
                        "location" => [
                            "lat" => "46.091373689876",
                            "lng" => "103.838245868683",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Тарагт",
                        "international" => "Тарагт",
                        "location" => [
                            "lat" => "46.3021918391544",
                            "lng" => "102.445986270905",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Төгрөг",
                        "international" => "Төгрөг",
                        "location" => [
                            "lat" => "45.5399021246968",
                            "lng" => "102.99485206604",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Уянга",
                        "international" => "Уянга",
                        "location" => [
                            "lat" => "46.4630782911145",
                            "lng" => "102.266492843628",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Хайрхандулаан",
                        "international" => "Хайрхандулаан",
                        "location" => [
                            "lat" => "45.9629940324409",
                            "lng" => "102.061743736267",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Хархорин",
                        "international" => "Хархорин",
                        "location" => [
                            "lat" => "47.1886917107924",
                            "lng" => "102.822461128235",
                        ],
                    ],
                    [
                        "province_id" => "14",
                        "name" => "Хужирт",
                        "international" => "Хужирт",
                        "location" => [
                            "lat" => "46.9183793241516",
                            "lng" => "102.762594223022",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Өмнөговь",
                "international" => "Өмнөговь",
                "location" => [
                    "lat" => "42.9999070893",
                    "lng" => "104.899475098",
                ],
                "districts" => [
                    [
                        "province_id" => "15",
                        "name" => "Баян-Овоо",
                        "international" => "Баян-Овоо",
                        "location" => [
                            "lat" => "44.4222105408702",
                            "lng" => "105.319833755493",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Баяндалай",
                        "international" => "Баяндалай",
                        "location" => [
                            "lat" => "43.465379191547",
                            "lng" => "103.509836196899",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Булган",
                        "international" => "Булган",
                        "location" => [
                            "lat" => "44.0968626579966",
                            "lng" => "103.542430400848",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Гурвантэс",
                        "international" => "Гурвантэс",
                        "location" => [
                            "lat" => "43.230508396395",
                            "lng" => "101.044735908508",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Даланзадгад",
                        "international" => "Даланзадгад",
                        "location" => [
                            "lat" => "43.5755408992572",
                            "lng" => "104.426422119141",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Мандал-Овоо",
                        "international" => "Мандал-Овоо",
                        "location" => [
                            "lat" => "44.6536042068318",
                            "lng" => "104.059066772461",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Манлай",
                        "international" => "Манлай",
                        "location" => [
                            "lat" => "44.0824061834346",
                            "lng" => "106.868991851807",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Ноён",
                        "international" => "Ноён",
                        "location" => [
                            "lat" => "43.1487965532064",
                            "lng" => "102.125816345215",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Номгон",
                        "international" => "Номгон",
                        "location" => [
                            "lat" => "42.8417060027591",
                            "lng" => "105.137529373169",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Сэврэй",
                        "international" => "Сэврэй",
                        "location" => [
                            "lat" => "43.5876805998889",
                            "lng" => "102.187185287476",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Ханбогд",
                        "international" => "Ханбогд",
                        "location" => [
                            "lat" => "43.1963694997907",
                            "lng" => "107.198131084442",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Ханхонгор",
                        "international" => "Ханхонгор",
                        "location" => [
                            "lat" => "43.781722295832",
                            "lng" => "104.479336738586",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Хүрмэн",
                        "international" => "Хүрмэн",
                        "location" => [
                            "lat" => "42.3045379651565",
                            "lng" => "104.072499275208",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Цогт-Овоо",
                        "international" => "Цогт-Овоо",
                        "location" => [
                            "lat" => "44.4222565161507",
                            "lng" => "105.319833755493",
                        ],
                    ],
                    [
                        "province_id" => "15",
                        "name" => "Цогтцэций",
                        "international" => "Цогтцэций",
                        "location" => [
                            "lat" => "43.7279407792729",
                            "lng" => "105.573635101318",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Сүхбаатар",
                "international" => "Сүхбаатар",
                "location" => [
                    "lat" => "46.0500059362",
                    "lng" => "113.727478027",
                ],
                "districts" => [
                    [
                        "province_id" => "16",
                        "name" => "Асгат",
                        "international" => "Асгат",
                        "location" => [
                            "lat" => "46.3628482101115",
                            "lng" => "113.577110767364",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Баруун-Урт",
                        "international" => "Баруун-Урт",
                        "location" => [
                            "lat" => "46.6819498744688",
                            "lng" => "113.277626037598",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Баяндэлгэр",
                        "international" => "Баяндэлгэр",
                        "location" => [
                            "lat" => "45.7265547760285",
                            "lng" => "112.356019020081",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Дарьганга",
                        "international" => "Дарьганга",
                        "location" => [
                            "lat" => "45.3034482503115",
                            "lng" => "113.849773406982",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Мөнххаан",
                        "international" => "Мөнххаан",
                        "location" => [
                            "lat" => "46.969798726793",
                            "lng" => "112.053809165955",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Наран",
                        "international" => "Наран",
                        "location" => [
                            "lat" => "45.1298936688683",
                            "lng" => "113.677382469177",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Онгон",
                        "international" => "Онгон",
                        "location" => [
                            "lat" => "45.3532158678442",
                            "lng" => "113.136262893677",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Сүхбаатар",
                        "international" => "Сүхбаатар",
                        "location" => [
                            "lat" => "47",
                            "lng" => "113",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Түвшинширээ",
                        "international" => "Түвшинширээ",
                        "location" => [
                            "lat" => "46.2047403410019",
                            "lng" => "111.808140277863",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Түмэнцогт",
                        "international" => "Түмэнцогт",
                        "location" => [
                            "lat" => "47.5729646318862",
                            "lng" => "112.350225448608",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Уулбаян",
                        "international" => "Уулбаян",
                        "location" => [
                            "lat" => "46.4954084501794",
                            "lng" => "112.351083755493",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Халзан",
                        "international" => "Халзан",
                        "location" => [
                            "lat" => "46.1669922604376",
                            "lng" => "112.953486442566",
                        ],
                    ],
                    [
                        "province_id" => "16",
                        "name" => "Эрдэнэцагаан",
                        "international" => "Эрдэнэцагаан",
                        "location" => [
                            "lat" => "45.9034782156919",
                            "lng" => "115.367088317871",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Сэлэнгэ",
                "international" => "Сэлэнгэ",
                "location" => [
                    "lat" => "48.8738291424",
                    "lng" => "106.559143066",
                ],
                "districts" => [
                    [
                        "province_id" => "17",
                        "name" => "Алтанбулаг",
                        "international" => "Алтанбулаг",
                        "location" => [
                            "lat" => "50.3131604722245",
                            "lng" => "106.501035690308",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Баруунбүрэн",
                        "international" => "Баруунбүрэн",
                        "location" => [
                            "lat" => "49.1617262313203",
                            "lng" => "104.805793762207",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Баянгол",
                        "international" => "Баянгол",
                        "location" => [
                            "lat" => "48.8551151667417",
                            "lng" => "106.459493637085",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Ерөө",
                        "international" => "Ерөө",
                        "location" => [
                            "lat" => "49.7466132303615",
                            "lng" => "106.670379638672",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Жавхлант",
                        "international" => "Жавхлант",
                        "location" => [
                            "lat" => "49.7388757846116",
                            "lng" => "106.261117458344",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Зүүнбүрэн",
                        "international" => "Зүүнбүрэн",
                        "location" => [
                            "lat" => "50.0660374575102",
                            "lng" => "105.880222320557",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Мандал",
                        "international" => "Мандал",
                        "location" => [
                            "lat" => "48.8019876811902",
                            "lng" => "106.707630157471",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Орхон",
                        "international" => "Орхон",
                        "location" => [
                            "lat" => "48.6275195310058",
                            "lng" => "103.540048599243",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Орхонтуул",
                        "international" => "Орхонтуул",
                        "location" => [
                            "lat" => "48.833297478156",
                            "lng" => "104.810450077057",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Сайхан",
                        "international" => "Сайхан",
                        "location" => [
                            "lat" => "49.1986159390537",
                            "lng" => "105.663371086121",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Сант",
                        "international" => "Сант",
                        "location" => [
                            "lat" => "49.2488146563621",
                            "lng" => "105.377855300903",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Сүхбаатар",
                        "international" => "Сүхбаатар",
                        "location" => [
                            "lat" => "50.2276612271521",
                            "lng" => "106.21470451355",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Түшиг",
                        "international" => "Түшиг",
                        "location" => [
                            "lat" => "50.3215183710207",
                            "lng" => "105.044617652893",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Хушаат",
                        "international" => "Хушаат",
                        "location" => [
                            "lat" => "49.6757515347544",
                            "lng" => "105.82526922226",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Хүдэр",
                        "international" => "Хүдэр",
                        "location" => [
                            "lat" => "49.7766638542997",
                            "lng" => "107.516541481018",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Цагааннуур",
                        "international" => "Цагааннуур",
                        "location" => [
                            "lat" => "50.1126802956122",
                            "lng" => "105.442872047424",
                        ],
                    ],
                    [
                        "province_id" => "17",
                        "name" => "Шаамар",
                        "international" => "Шаамар",
                        "location" => [
                            "lat" => "50.0801672649565",
                            "lng" => "106.183333396912",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Төв",
                "international" => "Төв",
                "location" => [
                    "lat" => "46.7131118077",
                    "lng" => "106.155773926",
                ],
                "districts" => [
                    [
                        "province_id" => "18",
                        "name" => "Алтанбулаг",
                        "international" => "Алтанбулаг",
                        "location" => [
                            "lat" => "47.697920564098",
                            "lng" => "106.407823562622",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Аргалант",
                        "international" => "Аргалант",
                        "location" => [
                            "lat" => "47.9402094128201",
                            "lng" => "105.895671844482",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Архуст",
                        "international" => "Архуст",
                        "location" => [
                            "lat" => "47.5171137467086",
                            "lng" => "107.94011592865",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Батсүмбэр",
                        "international" => "Батсүмбэр",
                        "location" => [
                            "lat" => "48.3658869040205",
                            "lng" => "106.73951625824",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баян",
                        "international" => "Баян",
                        "location" => [
                            "lat" => "47.2496108765528",
                            "lng" => "107.538299560547",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баян-Өнжүүл",
                        "international" => "Баян-Өнжүүл",
                        "location" => [
                            "lat" => "46.8438582101201",
                            "lng" => "106.227900981903",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баяндэлгэр",
                        "international" => "Баяндэлгэр",
                        "location" => [
                            "lat" => "47.727604569347",
                            "lng" => "108.111820220947",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баянжаргалан",
                        "international" => "Баянжаргалан",
                        "location" => [
                            "lat" => "47.1804082977782",
                            "lng" => "108.261165618896",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баянхангай",
                        "international" => "Баянхангай",
                        "location" => [
                            "lat" => "47.950500614687",
                            "lng" => "105.542607307434",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баянцагаан",
                        "international" => "Баянцагаан",
                        "location" => [
                            "lat" => "46.7706444905939",
                            "lng" => "107.142877578735",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баянцогт",
                        "international" => "Баянцогт",
                        "location" => [
                            "lat" => "48.125882417156",
                            "lng" => "105.81018447876",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Баянчандмань",
                        "international" => "Баянчандмань",
                        "location" => [
                            "lat" => "48.2236433743318",
                            "lng" => "106.302123069763",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Борнуур",
                        "international" => "Борнуур",
                        "location" => [
                            "lat" => "48.4512079393738",
                            "lng" => "106.25946521759",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Бүрэн",
                        "international" => "Бүрэн",
                        "location" => [
                            "lat" => "46.9179982554084",
                            "lng" => "105.051591396332",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Дэлгэрхаан",
                        "international" => "Дэлгэрхаан",
                        "location" => [
                            "lat" => "46.6202779525288",
                            "lng" => "104.565618038177",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Жаргалант",
                        "international" => "Жаргалант",
                        "location" => [
                            "lat" => "48.5260128953443",
                            "lng" => "105.86678981781",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Заамар",
                        "international" => "Заамар",
                        "location" => [
                            "lat" => "48.212134096021",
                            "lng" => "104.773714542389",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Зуунмод",
                        "international" => "Зуунмод",
                        "location" => [
                            "lat" => "47.7019352460004",
                            "lng" => "106.94962978363",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Лүн",
                        "international" => "Лүн",
                        "location" => [
                            "lat" => "47.8676817814455",
                            "lng" => "105.25408744812",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Мөнгөнморьт",
                        "international" => "Мөнгөнморьт",
                        "location" => [
                            "lat" => "48.2059137197121",
                            "lng" => "108.482823371887",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Өндөрширээт",
                        "international" => "Өндөрширээт",
                        "location" => [
                            "lat" => "47.4548197485853",
                            "lng" => "105.053822994232",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Сүмбэр",
                        "international" => "Сүмбэр",
                        "location" => [
                            "lat" => "48.8016626123425",
                            "lng" => "105.925626754761",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Сэргэлэн",
                        "international" => "Сэргэлэн",
                        "location" => [
                            "lat" => "47.6089406830802",
                            "lng" => "107.01979637146",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Угтаалцайдам",
                        "international" => "Угтаалцайдам",
                        "location" => [
                            "lat" => "48.2536554004508",
                            "lng" => "105.406694412231",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Цээл",
                        "international" => "Цээл",
                        "location" => [
                            "lat" => "48.4539119405766",
                            "lng" => "105.315113067627",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Эрдэнэ",
                        "international" => "Эрдэнэ",
                        "location" => [
                            "lat" => "47.717528918587",
                            "lng" => "107.79643535614",
                        ],
                    ],
                    [
                        "province_id" => "18",
                        "name" => "Эрдэнэсант",
                        "international" => "Эрдэнэсант",
                        "location" => [
                            "lat" => "47.3307954310064",
                            "lng" => "104.493799209595",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Увс",
                "international" => "Увс",
                "location" => [
                    "lat" => "49.3527193852",
                    "lng" => "93.1263671875",
                ],
                "districts" => [
                    [
                        "province_id" => "19",
                        "name" => "Баруунтуруун",
                        "international" => "Баруунтуруун",
                        "location" => [
                            "lat" => "49.6592944006878",
                            "lng" => "94.4032859802246",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Бөхмөрөн",
                        "international" => "Бөхмөрөн",
                        "location" => [
                            "lat" => "49.7754998780654",
                            "lng" => "90.6131744384766",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Давст",
                        "international" => "Давст",
                        "location" => [
                            "lat" => "50.6132013936592",
                            "lng" => "92.4010705947876",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Завхан",
                        "international" => "Завхан",
                        "location" => [
                            "lat" => "48.8223214950108",
                            "lng" => "93.1011915206909",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Зүүнговь",
                        "international" => "Зүүнговь",
                        "location" => [
                            "lat" => "49.9061475232778",
                            "lng" => "93.7892317771911",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Зүүнхангай",
                        "international" => "Зүүнхангай",
                        "location" => [
                            "lat" => "49.3057064576119",
                            "lng" => "95.4500341415405",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Малчин",
                        "international" => "Малчин",
                        "location" => [
                            "lat" => "49.7285850116092",
                            "lng" => "93.2662010192871",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Наранбулаг",
                        "international" => "Наранбулаг",
                        "location" => [
                            "lat" => "49.3737391177871",
                            "lng" => "92.5556945800781",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Өлгий",
                        "international" => "Өлгий",
                        "location" => [
                            "lat" => "49.0325785498292",
                            "lng" => "92.0362901687622",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Өмнөговь",
                        "international" => "Өмнөговь",
                        "location" => [
                            "lat" => "49.1043874768524",
                            "lng" => "91.7189311981201",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Өндөрхангай",
                        "international" => "Өндөрхангай",
                        "location" => [
                            "lat" => "49.2716688554334",
                            "lng" => "94.859733581543",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Сагил",
                        "international" => "Сагил",
                        "location" => [
                            "lat" => "50.3375997655833",
                            "lng" => "91.6062355041504",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Тариалан",
                        "international" => "Тариалан",
                        "location" => [
                            "lat" => "49.803316799231",
                            "lng" => "92.0680904388428",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Түргэн",
                        "international" => "Түргэн",
                        "location" => [
                            "lat" => "50.0987803026081",
                            "lng" => "91.6761875152588",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Тэс",
                        "international" => "Тэс",
                        "location" => [
                            "lat" => "50.476680224579",
                            "lng" => "93.5955548286438",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Улаангом",
                        "international" => "Улаангом",
                        "location" => [
                            "lat" => "49.9970363910722",
                            "lng" => "92.0489501953125",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Ховд",
                        "international" => "Ховд",
                        "location" => [
                            "lat" => "49.2797465148149",
                            "lng" => "90.9112000465393",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Хяргас",
                        "international" => "Хяргас",
                        "location" => [
                            "lat" => "49.6714607174095",
                            "lng" => "93.7772798538208",
                        ],
                    ],
                    [
                        "province_id" => "19",
                        "name" => "Цагаанхайрхан",
                        "international" => "Цагаанхайрхан",
                        "location" => [
                            "lat" => "49.4022328486935",
                            "lng" => "94.2536401748657",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Ховд",
                "international" => "Ховд",
                "location" => [
                    "lat" => "46.9910582574",
                    "lng" => "92.7355957031",
                ],
                "districts" => [
                    [
                        "province_id" => "20",
                        "name" => "Алтай",
                        "international" => "Алтай",
                        "location" => [
                            "lat" => "45.8060977828316",
                            "lng" => "92.2872376441956",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Булган",
                        "international" => "Булган",
                        "location" => [
                            "lat" => "46.1069148261149",
                            "lng" => "91.1185616254807",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Буянт",
                        "international" => "Буянт",
                        "location" => [
                            "lat" => "48.1707222005826",
                            "lng" => "91.7675971984863",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Дарви",
                        "international" => "Дарви",
                        "location" => [
                            "lat" => "46.9393046301276",
                            "lng" => "93.6165618896484",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Дөргөн",
                        "international" => "Дөргөн",
                        "location" => [
                            "lat" => "48.3328596253339",
                            "lng" => "92.6321697235107",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Дуут",
                        "international" => "Дуут",
                        "location" => [
                            "lat" => "47.5201279684921",
                            "lng" => "91.6318559646606",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Жаргалант",
                        "international" => "Жаргалант",
                        "location" => [
                            "lat" => "47.9790637682866",
                            "lng" => "91.6318988800049",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Зэрэг",
                        "international" => "Зэрэг",
                        "location" => [
                            "lat" => "47.1094213522466",
                            "lng" => "92.8442144393921",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Манхан",
                        "international" => "Манхан",
                        "location" => [
                            "lat" => "47.4212060203601",
                            "lng" => "92.2224140167236",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Мөнххайрхан",
                        "international" => "Мөнххайрхан",
                        "location" => [
                            "lat" => "47.0475516720459",
                            "lng" => "91.8371200561523",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Мөст",
                        "international" => "Мөст",
                        "location" => [
                            "lat" => "46.7008188802009",
                            "lng" => "92.7918148040771",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Мянгад",
                        "international" => "Мянгад",
                        "location" => [
                            "lat" => "48.2328489341095",
                            "lng" => "91.9296026229858",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Үенч",
                        "international" => "Үенч",
                        "location" => [
                            "lat" => "46.064008",
                            "lng" => "92.0176605",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Ховд",
                        "international" => "Ховд",
                        "location" => [
                            "lat" => "47.88620177919",
                            "lng" => "91.499633789062",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Цэцэг",
                        "international" => "Цэцэг",
                        "location" => [
                            "lat" => "46.5921067301321",
                            "lng" => "93.2671236991882",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Чандмань",
                        "international" => "Чандмань",
                        "location" => [
                            "lat" => "47.665502963133",
                            "lng" => "92.8173065185547",
                        ],
                    ],
                    [
                        "province_id" => "20",
                        "name" => "Эрдэнэбүрэн",
                        "international" => "Эрдэнэбүрэн",
                        "location" => [
                            "lat" => "48.5031849092492",
                            "lng" => "91.449658870697",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Хөвсгөл",
                "international" => "Хөвсгөл",
                "location" => [
                    "lat" => "49.7958044359",
                    "lng" => "100.003051758",
                ],
                "districts" => [
                    [
                        "province_id" => "21",
                        "name" => "Алаг-Эрдэнэ",
                        "international" => "Алаг-Эрдэнэ",
                        "location" => [
                            "lat" => "50.1167256742",
                            "lng" => "100.045580863953",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Арбулаг",
                        "international" => "Арбулаг",
                        "location" => [
                            "lat" => "49.9126975843151",
                            "lng" => "99.4424057006836",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Баянзүрх",
                        "international" => "Баянзүрх",
                        "location" => [
                            "lat" => "50.1761285688443",
                            "lng" => "98.9730834960938",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Бүрэнтогтох",
                        "international" => "Бүрэнтогтох",
                        "location" => [
                            "lat" => "49.6203306184828",
                            "lng" => "99.5860862731934",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Галт",
                        "international" => "Галт",
                        "location" => [
                            "lat" => "48.7705027620624",
                            "lng" => "99.8734045028686",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Жаргалант",
                        "international" => "Жаргалант",
                        "location" => [
                            "lat" => "48.5828249406697",
                            "lng" => "99.3471336364746",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Их-Уул",
                        "international" => "Их-Уул",
                        "location" => [
                            "lat" => "49.4425148735769",
                            "lng" => "101.472730636597",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Мөрөн",
                        "international" => "Мөрөн",
                        "location" => [
                            "lat" => "49.6365646845072",
                            "lng" => "100.159091949463",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Рашаант",
                        "international" => "Рашаант",
                        "location" => [
                            "lat" => "49.1229554203885",
                            "lng" => "101.437497138977",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Рэнчинлхүмбэ",
                        "international" => "Рэнчинлхүмбэ",
                        "location" => [
                            "lat" => "51.106432148148",
                            "lng" => "99.6717023849487",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Тариалан",
                        "international" => "Тариалан",
                        "location" => [
                            "lat" => "49.6159375954104",
                            "lng" => "101.994023323059",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Тосонцэнгэл",
                        "international" => "Тосонцэнгэл",
                        "location" => [
                            "lat" => "49.4760161423193",
                            "lng" => "100.890111923218",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Төмөрбулаг",
                        "international" => "Төмөрбулаг",
                        "location" => [
                            "lat" => "49.2968074458863",
                            "lng" => "100.258634090424",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Түнэл",
                        "international" => "Түнэл",
                        "location" => [
                            "lat" => "49.8598987004543",
                            "lng" => "100.623950958252",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Улаан-Уул",
                        "international" => "Улаан-Уул",
                        "location" => [
                            "lat" => "50.6786216639417",
                            "lng" => "99.2255973815918",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Ханх",
                        "international" => "Ханх",
                        "location" => [
                            "lat" => "51.5083551577358",
                            "lng" => "100.671501159668",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Цагаан-Уул",
                        "international" => "Цагаан-Уул",
                        "location" => [
                            "lat" => "49.6031733389555",
                            "lng" => "98.6982536315918",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Цагаан-Үүр",
                        "international" => "Цагаан-Үүр",
                        "location" => [
                            "lat" => "50.5383082228019",
                            "lng" => "101.518650054932",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Цагааннуур",
                        "international" => "Цагааннуур",
                        "location" => [
                            "lat" => "51.3550064366185",
                            "lng" => "99.3514251708984",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Цэцэрлэг",
                        "international" => "Цэцэрлэг",
                        "location" => [
                            "lat" => "49.5248462005909",
                            "lng" => "97.738881111145",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Чадмань-Өндөр",
                        "international" => "Чадмань-Өндөр",
                        "location" => [
                            "lat" => "50.4749049768062",
                            "lng" => "100.931739807129",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Шинэ-Идэр",
                        "international" => "Шинэ-Идэр",
                        "location" => [
                            "lat" => "48.9482381174979",
                            "lng" => "99.5343732833862",
                        ],
                    ],
                    [
                        "province_id" => "21",
                        "name" => "Эрдэнэбулган",
                        "international" => "Эрдэнэбулган",
                        "location" => [
                            "lat" => "50.1202203859529",
                            "lng" => "101.58890247345",
                        ],
                    ]
                ]
            ],
            [
                'country_id' => 1,
                "name" => "Хэнтий",
                "international" => "Хэнтий",
                "location" => [
                    "lat" => "47.7380529727",
                    "lng" => "110.882263184",
                ],
                "districts" => [
                    [
                        "province_id" => "22",
                        "name" => "Батноров",
                        "international" => "Батноров",
                        "location" => [
                            "lat" => "47.9473674768828",
                            "lng" => "111.50050163269",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Батширээт",
                        "international" => "Батширээт",
                        "location" => [
                            "lat" => "48.6924618206487",
                            "lng" => "110.191969871521",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Баян-Адарга",
                        "international" => "Баян-Адарга",
                        "location" => [
                            "lat" => "48.5552506602222",
                            "lng" => "111.087656021118",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Баян-Овоо",
                        "international" => "Баян-Овоо",
                        "location" => [
                            "lat" => "47.7874408742845",
                            "lng" => "112.112474441528",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Баян-хутаг",
                        "international" => "Баян-хутаг",
                        "location" => [
                            "lat" => "47.1712191975064",
                            "lng" => "110.817246437073",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Баянмөнх",
                        "international" => "Баянмөнх",
                        "location" => [
                            "lat" => "46.9013751587606",
                            "lng" => "109.761185646057",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Биндэр",
                        "international" => "Биндэр",
                        "location" => [
                            "lat" => "48.6161722709505",
                            "lng" => "110.604515075684",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Галшир",
                        "international" => "Галшир",
                        "location" => [
                            "lat" => "46.2403106157895",
                            "lng" => "110.839776992798",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Дадал",
                        "international" => "Дадал",
                        "location" => [
                            "lat" => "49.0214915028294",
                            "lng" => "111.615386009216",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Дархан",
                        "international" => "Дархан",
                        "location" => [
                            "lat" => "46.6282505043558",
                            "lng" => "109.405117034912",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Дэлгэрхаан",
                        "international" => "Дэлгэрхаан",
                        "location" => [
                            "lat" => "47.1813125359862",
                            "lng" => "109.149856567383",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Жаргалтхаан",
                        "international" => "Жаргалтхаан",
                        "location" => [
                            "lat" => "47.4945885521752",
                            "lng" => "109.47172164917",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Мөрөн",
                        "international" => "Мөрөн",
                        "location" => [
                            "lat" => "47.3830670658725",
                            "lng" => "110.318570137024",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Норовлин",
                        "international" => "Норовлин",
                        "location" => [
                            "lat" => "48.6912436829794",
                            "lng" => "111.993083953857",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Өмнөдэлгэр",
                        "international" => "Өмнөдэлгэр",
                        "location" => [
                            "lat" => "47.8871974177745",
                            "lng" => "109.807169437408",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Хэрлэн",
                        "international" => "Хэрлэн",
                        "location" => [
                            "lat" => "47.3204978063591",
                            "lng" => "110.65146446228",
                        ],
                    ],
                    [
                        "province_id" => "22",
                        "name" => "Цэнхэрмандал",
                        "international" => "Цэнхэрмандал",
                        "location" => [
                            "lat" => "47.7431761318628",
                            "lng" => "109.046795368195",
                        ],
                    ]
                ]
            ]
        ];

        foreach ($provinces as $province) {
            $p = new \App\Models\Province;
            $p->country_id = $province['country_id'];
            $p->name = $province['name'];
            $p->international = $province['international'];
            $p->location = $province['location'];
            $p->save();

            foreach ($province['districts'] as $district) {
                $d = new \App\Models\District;
                $d->province_id = $p->id;
                $d->name = $district['name'];
                $d->international = $district['international'];
                $d->location = $district['location'];
                $d->save();
            }
        }
    }
}
