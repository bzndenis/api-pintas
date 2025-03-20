<?php

namespace App\Http\Controllers;

use App\Models\News;

use App\Models\UserAuth;

use App\Models\CCTV;

use App\Models\Polantas;
use App\Models\PolantasCategory;

use App\Models\Fasum;
use App\Models\FasumCategory;

use App\Models\Trayek;
use App\Models\TrayekCategory;

use Illuminate\Http\Request;
use App\Http\Helper\ResponseBuilder;

class PublicController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware("login");
    // }

    public function get_trayek()
    {
        $trayek = Trayek::all();
        $category_trayek = TrayekCategory::all();

        $data = [];

        foreach ($trayek as $trayekItem) {
            $category = $category_trayek->where('id', $trayekItem->category_trayek)->first();

            if ($category) {
                $data[] = [
                    "id" => $trayekItem->id,
                    "name_trayek" => $trayekItem->name_trayek,
                    "category_trayek" => [
                        "id" => $category->id,
                        "name_category" => $category->name_category,
                        "created_at" => $category->created_at,
                        "updated_at" => $category->updated_at,
                        "deleted_at" => $category->deleted_at,
                    ],
                    "image_trayek" => $trayekItem->image_trayek,
                    "option" => $trayekItem->option,
                    "region" => $trayekItem->region,
                    "route" => $trayekItem->route,
                    "created_at" => $trayekItem->created_at,
                    "updated_at" => $trayekItem->updated_at,
                    "deleted_at" => $trayekItem->deleted_at,
                ];
            }
        }

        $response = [
            "status" => 200,
            "success" => true,
            "message" => "Berhasil Mendapatkan Data",
            "total_data" => count($data),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function get_trayekbyid($id)
    {
        $trayekItem = Trayek::find($id);

        if (!$trayekItem) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $category = TrayekCategory::find($trayekItem->category_trayek);

        $response = [
            "status" => 200,
            "success" => true,
            "message" => "Berhasil Mendapatkan Data",
            "data" => [
                "id" => $trayekItem->id,
                "name_trayek" => $trayekItem->name_trayek,
                "category_trayek" => [
                    "id" => $category->id,
                    "name_category" => $category->name_category,
                    "created_at" => $category->created_at,
                    "updated_at" => $category->updated_at,
                    "deleted_at" => $category->deleted_at,
                ],
                "image_trayek" => $trayekItem->image_trayek,
                "option" => $trayekItem->option,
                "region" => $trayekItem->region,
                "route" => $trayekItem->route,
                "created_at" => $trayekItem->created_at,
                "updated_at" => $trayekItem->updated_at,
                "deleted_at" => $trayekItem->deleted_at,
            ],
        ];

        return response()->json($response);
    }

    public function get_trayekcate()
    {
        $categories = TrayekCategory::all();

        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $categories, true, false);
    }

    public function get_trayekcatebyid($id)
    {
        $category = TrayekCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori polantas tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }


    public function get_fasum()
    {
        $fasum = Fasum::all();
        $category_fasum = FasumCategory::all();

        $data = [];

        foreach ($fasum as $fasumItem) {
            $category = $category_fasum->where('id', $fasumItem->category_fasum)->first();

            if ($category) {
                $data[] = [
                    "id" => $fasumItem->id,
                    "name_fasum" => $fasumItem->name_fasum,
                    "category_fasum" => [
                        "id" => $category->id,
                        "name_category" => $category->name_category,
                        "created_at" => $category->created_at,
                        "updated_at" => $category->updated_at,
                        "deleted_at" => $category->deleted_at,
                    ],
                    "address" => $fasumItem->address,
                    "latitude" => $fasumItem->latitude,
                    "longitude" => $fasumItem->longitude,
                    "contact_fasum" => $fasumItem->contact_fasum,
                    "image_fasum" => $fasumItem->image_fasum,
                    "open_time" => $fasumItem->open_time,
                    "close_time" => $fasumItem->close_time,
                    "created_at" => $fasumItem->created_at,
                    "updated_at" => $fasumItem->updated_at,
                    "deleted_at" => $fasumItem->deleted_at,
                ];
            }
        }

        $response = [
            "status" => 200,
            "success" => true,
            "message" => "Berhasil Mendapatkan Data",
            "total_data" => count($data),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function get_fasumbyid($id)
    {
        $fasumItem = Fasum::find($id);

        if (!$fasumItem) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $category = FasumCategory::find($fasumItem->category_fasum);

        $response = [
            "status" => 200,
            "success" => true,
            "message" => "Berhasil Mendapatkan Data",
            "data" => [
                "id" => $fasumItem->id,
                "name_fasum" => $fasumItem->name_fasum,
                "category_fasum" => [
                    "id" => $category->id,
                    "name_category" => $category->name_category,
                    "created_at" => $category->created_at,
                    "updated_at" => $category->updated_at,
                    "deleted_at" => $category->deleted_at,
                ],
                "address" => $fasumItem->address,
                "latitude" => $fasumItem->latitude,
                "longitude" => $fasumItem->longitude,
                "contact_fasum" => $fasumItem->contact_fasum,
                "image_fasum" => $fasumItem->image_fasum,
                "open_time" => $fasumItem->open_time,
                "close_time" => $fasumItem->close_time,
                "created_at" => $fasumItem->created_at,
                "updated_at" => $fasumItem->updated_at,
                "deleted_at" => $fasumItem->deleted_at,
            ],
        ];

        return response()->json($response);
    }

    public function get_fasumcate()
    {
        $categories = FasumCategory::all();

        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $categories, true, false);
    }

    public function get_fasumcatebyid($id)
    {
        $category = FasumCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori polantas tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }


    public function get_polantas()
    {
        $polantas = Polantas::all();
        $category_polantas = PolantasCategory::all();

        $data = [];

        foreach ($polantas as $polantasItem) {
            $category = $category_polantas->where('id', $polantasItem->category_polantas)->first();

            if ($category) {
                $data[] = [
                    "id" => $polantasItem->id,
                    "name_polantas" => $polantasItem->name_polantas,
                    "category_polantas" => [
                        "id" => $category->id,
                        "name_category" => $category->name_category,
                        "created_at" => $category->created_at,
                        "updated_at" => $category->updated_at,
                        "deleted_at" => $category->deleted_at,
                    ],
                    "address" => $polantasItem->address,
                    "latitude" => $polantasItem->latitude,
                    "longitude" => $polantasItem->longitude,
                    "contact_polantas" => $polantasItem->contact_polantas,
                    "image_polantas" => $polantasItem->image_polantas,
                    "open_time" => $polantasItem->open_time,
                    "close_time" => $polantasItem->close_time,
                    "created_at" => $polantasItem->created_at,
                    "updated_at" => $polantasItem->updated_at,
                    "deleted_at" => $polantasItem->deleted_at,
                ];
            }
        }

        $response = [
            "status" => 200,
            "success" => true,
            "message" => "Berhasil Mendapatkan Data",
            "total_data" => count($data),
            "data" => $data,
        ];

        return response()->json($response);
    }

    public function get_polantasbyid($id)
    {
        $polantasItem = Polantas::find($id);

        if (!$polantasItem) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $category = PolantasCategory::find($polantasItem->category_polantas);

        $response = [
            "status" => 200,
            "success" => true,
            "message" => "Berhasil Mendapatkan Data",
            "data" => [
                "id" => $polantasItem->id,
                "name_polantas" => $polantasItem->name_polantas,
                "category_polantas" => [
                    "id" => $category->id,
                    "name_category" => $category->name_category,
                    "created_at" => $category->created_at,
                    "updated_at" => $category->updated_at,
                    "deleted_at" => $category->deleted_at,
                ],
                "address" => $polantasItem->address,
                "latitude" => $polantasItem->latitude,
                "longitude" => $polantasItem->longitude,
                "contact_polantas" => $polantasItem->contact_polantas,
                "image_polantas" => $polantasItem->image_polantas,
                "open_time" => $polantasItem->open_time,
                "close_time" => $polantasItem->close_time,
                "created_at" => $polantasItem->created_at,
                "updated_at" => $polantasItem->updated_at,
                "deleted_at" => $polantasItem->deleted_at,
            ],
        ];

        return response()->json($response);
    }

    public function get_polantascate()
    {
        $categories = PolantasCategory::all();

        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $categories, true, false);
    }

    public function get_polantascatebyid($id)
    {
        $category = PolantasCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori polantas tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ], 200);
    }

    public function showcctvbyid($id)
    {
        $cctv[] = CCTV::find($id);
        if ($cctv[0] != null) {
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $cctv, true);
        }
        return ResponseBuilder::error(404, "Data Tidak ada", $cctv);
    }

    public function tokenview()
    {
        $tokenget = UserAuth::all();
        $data = $tokenget->pluck('token');
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }

    public function getcctv()
    {
        $cctv = CCTV::orderBy('created_at', 'desc')->get();

        return ResponseBuilder::success(200, 'Berhasil Mendapatkan Data', $cctv, true, false);
    }

    public function index()
    {
        $news = News::all();
        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $news, true, false);
    }

    public function show($id)
    {
        $news[] = News::find($id);
        if ($news[0] != null) {
            return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $news, true);
        }
        return ResponseBuilder::error(404, "Data Tidak ada", $news);
    }

    public function pagenationNews(Request $request)
    {

        $news = News::orderBy('created_at', 'desc')->paginate($request->input('limit', 10));

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $offset = ($page - 1) * $limit;
        $news = News::orderBy('created_at', 'desc')->paginate($limit);
        $total_news = $news->total();
        $total_pages = $news->lastPage();

        $data = $news->items();

        return response()->json([
            "isSuccess" => true,
            "statusCode" => 200,
            "responseMessage" => "Success",
            "query" => [
                "page" => $page,
                "limit" => $limit,
                "offset" => $offset,
                "count" => $total_news,
                "total_pages" => $total_pages
            ],
            "data" => $data,
            "recordsFiltered" => $total_news,
            "recordsTotal" => $total_news
        ]);

    }

    public function count_total_data()
    {
        $countCCTV = CCTV::count();
        $countTrayek = Trayek::count();
        $countFasum = Fasum::where('category_fasum', 8)->count();
        $countPos = Polantas::where('category_polantas', 3)->count();

        $data = [
            'cctv' => $countCCTV,
            'trayek' => $countTrayek,
            'terminal' => $countFasum,
            'pos' => $countPos,
        ];

        return ResponseBuilder::success(200, "Berhasil Mendapatkan Data", $data, true, false);
    }


}