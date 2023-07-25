<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\{PenawaranRequest, ProdukRequest};
use App\Http\Transformers\Result;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\DB;
use Storage;
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
        if($request->get('status') == '0'){
            $status = 0;
        }
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        $get_produk = Produk::where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->where('produk.status_active', $status)
                            ->selectRaw("produk.id_produk, nama_produk, harga, CONCAT('" . $media_url . "', url_logo) as url_logo")
                            ->orderBy('produk.created_at', 'DESC');

        if (!empty($search)) {
            $get_produk->where(function ($q) use ($search) {
                return $q->where('produk.produk', 'like', '%' . $search . '%');
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
                            ->selectRaw("produk.id_produk, nama_produk, harga, CONCAT('" . $media_url . "', url_logo) as url_logo")
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
                'id_produk_add_on' => $val->id_produk_add_on,
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

    public function detail_menu(Request $request)
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
        $get = Produk::where('id_produk', $id_produk)
                            ->where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->selectRaw("produk.id_produk, nama_produk, harga, CONCAT('" . $media_url . "', url_logo) as url_logo")
                            ->first();
        $array_add_on = [];
        $get_add_on = ProdukAddOn::where('id_produk', $id_produk)
                                ->get();
        foreach($get_add_on as $val){
            $add_on = [
                'id_produk_add_on' => $val->id_produk_add_on,
                'id_add_on' => $val->id_add_on,
                'nama' => $val->nama,
            ];
            array_push($array_add_on, $add_on);
        }
        $array_varian = [];
        $get_varian = ProdukVarian::where('id_produk', $id_produk)
                                ->get();
        foreach($get_varian as $val){
            $varian = [
                'id_produk_varian' => $val->id_produk_varian,
                'nama_varian' => $val->nama_varian,
            ];
            array_push($array_varian, $varian);
        }
        $get_produk['id_produk'] = $get->id_produk;
        $get_produk['nama_produk'] = $get->nama_produk;
        $get_produk['harga'] = $get->harga;
        $get_produk['url_logo'] = $get->url_logo;
        $get_produk['add_on'] = $array_add_on;
        $get_produk['varian'] = $array_varian;

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
        DB::begintransaction();
        try {
            $url_logo = $this->_handleUpload($request->url_logo);
            $req["id_toko"] = $user->id_toko;
            $req["id_outlet"] = $id_outlet;
            $req["id_kategori"] = $request->id_kategori;
            $req["nama_produk"] = $request->nama_produk;
            $req["harga"] = $request->harga;
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

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return Result::response(array(), $e->getMessage(), 400, false);
        }
        $result['status'] = true;
        $result['message'] = 'Data Berhasil Dibuat.';
        $result['data'] = array();

        return response()->json($result);
    }

    public function update_produk(Request $request)
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
        $id_produk = $request->id_produk;
        DB::begintransaction();
        try {
            
            $produk = Produk::findOrFail($id_produk);
            $produk->id_kategori = $request->id_kategori;
            $produk->nama_produk = $request->nama_produk;
            $produk->harga = $request->harga;

            if (!empty($request->url_logo)) {
                $url_logo = $this->_handleUpload($request->url_logo);
                $produk->url_logo = $url_logo;
            }

            $produk->save();

            if (empty($produk)) {
               throw new \Exception('Gagal Update Produk');
            }
            if($request->add_on != null){
                $nama_add_on = explode(",", $request->add_on);
                foreach($nama_add_on as $val){
                    $cek = ProdukAddOn::where('id_produk', $id_produk)
                                    ->where('nama', $val)
                                    ->first();
                    if($cek == null){
                        $save_add_on = [
                            'id_produk' => $id_produk,
                            'nama' => $val,
                        ];
                        $create_add_on = ProdukAddOn::create($save_add_on);
                        if (empty($create_add_on)) {
                        throw new \Exception('Gagal Create Produk Add On');
                        }
                    }else{
                        if($cek->status_active == '0'){
                            $active_produk_add_on = ProdukAddOn::where('id_produk_add_on', $cek->id_produk_add_on)
                                                        ->where('id_produk', $id_produk)
                                                        ->update(
                                                            ['status_active' => '1']
                                                        );
                        }
                    }
                }
                $disable_produk_add_on = ProdukAddOn::whereNotIn('nama', $nama_add_on)
                                                    // where('id_produk_add_on', 23)
                                                    ->where('id_produk', $id_produk)
                                                    ->update(
                                                        ['status_active' => '0']
                                                    );
            }
            if($request->id_produk_add_on != null ){
                $id_produk_add_on = explode(",", $request->id_produk_add_on);
                foreach($id_produk_add_on as $val){
                    $add_on = AddOn::where('id_add_on', $val)->first();
                    $cek_ = ProdukAddOn::where('id_produk', $id_produk)
                                    ->where('id_add_on', $add_on->id_add_on)
                                    ->first();
                    if($cek_ == null){
                        $save_add_on = [
                            'id_produk' => $id_produk,
                            'id_add_on' => $add_on->id_add_on,
                            'nama' => $add_on->nama,
                        ];
                        $create_add_on = ProdukAddOn::create($save_add_on);
                        if (empty($create_add_on)) {
                        throw new \Exception('Gagal Create Produk Add On');
                        }
                    }else{
                        if($cek_->status_active == '0'){
                            $active_produk_add_on = ProdukAddOn::where('id_produk_add_on', $cek_->id_produk_add_on)
                                                        ->where('id_produk', $id_produk)
                                                        ->update(
                                                            ['status_active' => '1']
                                                        );
                        }
                    }
                    $disable_produk_add_on = ProdukAddOn::whereNotIn('id_add_on', $id_produk_add_on)
                                                        // where('id_produk_add_on', 23)
                                                        ->where('id_produk', $id_produk)
                                                        ->update(
                                                            ['status_active' => '0']
                                                        );
                }
            }

            if($request->varian != null ){
                $varian = explode(",", $request->varian);
                foreach($varian as $val){
                    $cek__ = ProdukVarian::where('id_produk', $id_produk)
                                    ->where('nama_varian', $val)
                                    ->first();
                                    // dd($cek__);
                    if($cek__ == null){
                        $save_varian = [
                            'id_produk' => $id_produk,
                            'nama_varian' => $val,
                        ];
                        $create_varian = ProdukVarian::create($save_varian);
                        if (empty($create_varian)) {
                        throw new \Exception('Gagal Create Produk Varian');
                        }
                    }else{
                        if($cek__->status_active == '0'){
                            $active_produk_add_on = ProdukVarian::where('id_produk_varian', $cek__->id_produk_varian)
                                                        ->where('id_produk', $id_produk)
                                                        ->update(
                                                            ['status_active' => '1']
                                                        );
                        }
                    }
                    $disable_produk_add_on = ProdukVarian::whereNotIn('nama_varian', $varian)
                                                        // where('id_produk_varian', 23)
                                                        ->where('id_produk', $id_produk)
                                                        ->update(
                                                            ['status_active' => '0']
                                                        );
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return Result::response(array(), $e->getMessage(), 400, false);
        }
        $result['status'] = true;
        $result['message'] = 'Data Berhasil Diupdate.';
        $result['data'] = array();

        return response()->json($result);
    }

    public function delete_produk(Request $request)
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
        $get_produk = Produk::where('id_produk', $id_produk)
                            ->where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->update(['status_active', 0]);

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

    public function create_bahan(Request $request)
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
        DB::beginTransaction();
 
        try{
            $ret = [
             'id_toko' => $user->id_toko,
             'id_outlet' => $id_outlet,
             'nama_bahan' => $request->nama_bahan,
             'berat_awal' => $request->berat_awal,
             'berat_akhir' => $request->berat_awal,
             'qty' => $request->qty,
             'harga_total' => $request->harga_total,
            ];

            $create_bahan = Bahan::create($ret);

            if (empty($create_bahan)) {
               throw new \Exception('Gagal Create Bahan');
            }
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return Result::response(array(), $e->getMessage(), 400, false);
        }
        $result['status'] = true;
        $result['message'] = 'Data Berhasil Dibuat.';
        $result['data'] = array();

        return response()->json($result);
    }

    public function list_bahan(Request $request)
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
        if($request->get('status') == '0'){
            $status = 0;
        }
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        $get_bahan = Bahan::where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->where('status_active', $status)
                            ->selectRaw("id_bahan, nama_bahan, berat_awal, berat_akhir, qty, harga_total, created_at")
                            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $get_bahan->where(function ($q) use ($search) {
                return $q->where('bahan.nama_bahan', 'like', '%' . $search . '%');
            });
        }

        $get_bahan = $get_bahan->paginate($per_page)->withQueryString();

        if($get_bahan){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_bahan;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function detail_bahan(Request $request)
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
        $id_bahan = $request->get('id_bahan');
        $get_bahan = Bahan::where('id_bahan', $id_bahan)
                            ->where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->selectRaw("id_bahan, nama_bahan, berat_awal, berat_akhir, qty, harga_total, created_at")
                            ->first();
        if($get_bahan){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_bahan;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function create_add_on(Request $request)
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
        DB::beginTransaction();
 
        try{
            $ret = [
             'id_toko' => $user->id_toko,
             'id_outlet' => $id_outlet,
             'nama' => $request->nama_add_on,
            ];

            $create_add_on = AddOn::create($ret);
            if (empty($create_add_on)) {
               throw new \Exception('Gagal Create Add On');
            }

            $array_bahan = json_decode($request->array_bahan);
            if (empty($array_bahan)) {
               throw new \Exception('Gagal Muat Array Bahan');
            }
            foreach($array_bahan as $val){
                $get_bahan = Bahan::where('id_bahan', $val->id_bahan)->first();

                $save_add_on_bahan = [
                    'id_add_on' => $create_add_on->id_add_on,
                    'id_bahan' => $val->id_bahan,
                    'nama' => $get_bahan->nama_bahan,
                    'berat' => $val->berat_komposisi,
                ];

                $create_add_on_bahan = AddOnBahan::create($save_add_on_bahan);
                if (empty($create_add_on_bahan)) {
                    throw new \Exception('Gagal Create Produk Add On');
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            return Result::response(array(), $e->getMessage(), 400, false);
        }
        $result['status'] = true;
        $result['message'] = 'Data Berhasil Dibuat.';
        $result['data'] = array();

        return response()->json($result);
    }

    public function list_add_on(Request $request)
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
        if($request->get('status') == '0'){
            $status = 0;
        }
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        $get_bahan = AddOn::where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->where('status_active', $status)
                            ->selectRaw("id_add_on, nama, created_at")
                            ->with([
                                'add_on_bahan' => function ($q) {
                                   $q->select('*');
                                }
                             ])
                            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $get_bahan->where(function ($q) use ($search) {
                return $q->where('bahan.nama_bahan', 'like', '%' . $search . '%');
            });
        }

        $get_bahan = $get_bahan->paginate($per_page)->withQueryString();

        if($get_bahan){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_bahan;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function detail_add_on(Request $request)
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
        $id_add_on = $request->get('id_add_on');
        $get_add_on = AddOn::where('id_add_on', $id_add_on)
                            ->where('id_outlet', $id_outlet)
                            ->where('id_toko', $user->id_toko)
                            ->selectRaw("id_add_on, nama, created_at")
                            ->with([
                                'add_on_bahan' => function ($q) {
                                   $q->select('*');
                                }
                             ])
                            ->first();
        if($get_add_on){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_add_on;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

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