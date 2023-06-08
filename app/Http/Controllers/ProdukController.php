<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{PenawaranRequest, ProdukRequest};
use App\Http\Transformers\Result;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use DB;
use Storage;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\{Produk, Bahan, Toko, Outlet, AddOn, ProdukAddOn, AddOnBahan, ProdukVarian};

class ProdukController extends Controller
{
    private function _handleUpload($get_image) {
        $time = strtotime(date(now())) * 1000;
        $image_ = explode(',', $get_image);
        $image = $image_[1];

        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $file_base64 = base64_decode($image);
        $new_imgname = $time . '.png';
        
        $filePath = '/img-produk/' . $new_imgname;
        Storage::disk('storage')->put($filePath, $file_base64);

        return $filePath;
    }

    public function list_produk(Request $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $cek_outlet = Outlet::Where('id_user', $id_user)
                        ->Where('id_toko', $user->id_toko)->first();
        
        if($cek_outlet == null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Hak Akses Tidak Dibolehkan.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $id_outlet = $cek_outlet->id_outlet;
        $search = $request->get('search');
        $media_url = url('/storage/public');
        $status = $request->get('status') ? $request->get('status') : 1;
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        $get_produk = Produk::where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->where('produk.status_active', $status)
                            ->selectRaw("produk.id_produk, nama_produk, CONCAT('" . $media_url . "', url_logo) as url_logo")
                            ->orderBy('produk.created_at', 'DESC');

        if (!empty($search)) {
            $get_produk->where(function ($q) use ($search) {
                return $q->where('produk.keterangan_produk', 'like', '%' . $search . '%');
            });
        }

        $get_produk = $get_produk->paginate($per_page)->withQueryString();

        if($get_produk){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_produk;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function detail_produk(Request $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $cek_outlet = Outlet::Where('id_user', $id_user)
                        ->Where('id_toko', $user->id_toko)->first();
        
        if($cek_outlet == null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Hak Akses Tidak Dibolehkan.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $id_outlet = $cek_outlet->id_outlet;
        $id_produk = $request->get('id_produk');
        $media_url = url('/storage/public');
        $get_produk = Produk::where('id_produk', $id_produk)
                            ->where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->selectRaw("produk.id_produk, nama_produk, CONCAT('" . $media_url . "', url_logo) as url_logo")
                            ->first();
        $array_add_on = [];
        $get_add_on = ProdukAddOn::where('id_produk', $id_produk)
                                ->get();
        foreach($get_add_on as $val){
            $get = AddOnBahan::Where('id_add_on', $val->id_add_on)
                                    ->get();
            $array_bahan = [];
            foreach($get as $val1){
                $bahan = [
                    'id_bahan' => $val1->id_bahan,
                    'nama' => $val1->nama,
                    'berat' => $val1->berat,
                ];
                array_push($array_bahan, $bahan);
            }
            $add_on = [
                'id_add_on' => $val->id_add_on,
                'nama' => $val->nama,
                'array_bahan' => $array_bahan,
            ];
            array_push($array_add_on, $add_on);
        }
        $get_produk['add_on'] = $array_add_on;

        if($get_produk){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_produk;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function create_produk(ProdukRequest $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $cek_outlet = Outlet::Where('id_user', $id_user)
                        ->Where('id_toko', $user->id_toko)->first();
        
        if($cek_outlet == null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Hak Akses Tidak Dibolehkan.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $id_outlet = $cek_outlet->id_outlet;
        // DB::begintransaction();
        // try {
            $url_logo = $this->_handleUpload($request->url_logo);
            $req["id_toko"] = $user->id_toko;
            $req["id_outlet"] = $id_outlet;
            $req["id_kategori"] = $request->id_kategori;
            $req["nama_produk"] = $request->nama_produk;
            $req["url_logo"] = $url_logo;

            $create_produk = Produk::create($req);
            if (empty($create_produk)) {
               throw new \Exception('Gagal Create Produk');
            }
            $id_produk = $create_produk->id_produk;
            if($request->add_on != null){
                $nama_add_on = explode(",", $request->add_on);
                foreach($nama_add_on as $val){
                    $save_add_on = [
                        'id_produk' => $id_produk,
                        'nama' => $val,
                    ];
                    $create_add_on = ProdukAddOn::create($save_add_on);
                    if (empty($create_add_on)) {
                       throw new \Exception('Gagal Create Produk Add On');
                    }
                }
            }
            if($request->id_produk_add_on != null ){
                $id_produk_add_on = explode(",", $request->id_produk_add_on);
                foreach($id_produk_add_on as $val){
                    $add_on = AddOn::where('id_add_on', $val)->first();
                    $save_add_on = [
                        'id_produk' => $id_produk,
                        'id_add_on' => $add_on->id_add_on,
                        'nama' => $add_on->nama,
                    ];
                    $create_add_on = ProdukAddOn::create($save_add_on);
                    if (empty($create_add_on)) {
                       throw new \Exception('Gagal Create Produk Add On');
                    }
                }
            }

            $varian = explode(",", $request->varian);
            foreach($varian as $val){
                $save_varian = [
                    'id_produk' => $id_produk,
                    'nama_varian' => $val,
                ];
                $create_varian = ProdukVarian::create($save_varian);
                if (empty($create_varian)) {
                   throw new \Exception('Gagal Create Produk Varian');
                }
            }

        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return Result::response(array(), $e->getMessage(), 400, false);
        // }
        $result['status'] = true;
        $result['message'] = 'Data Berhasil Dibuat.';
        $result['data'] = array();

        return response()->json($result);
    }

    protected function resultTransformer($status, $message, $data = null)
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }
}