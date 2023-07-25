<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Transformers\Result;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use DB;
use Storage;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\{Toko, Outlet};
use App\Http\Requests\{RegisterOutletRequest, ProdukRequest};

class OutletController extends Controller
{
    private function _handleUpload($get_image) {
        $time = strtotime(date(now())) * 1000;
        $image_ = explode(',', $get_image);
        $image = $image_[1];

        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $file_base64 = base64_decode($image);
        $new_imgname = $time . '.png';
        
        $filePath = '/img-profile/' . $new_imgname;
        Storage::disk('storage')->put($filePath, $file_base64);

        return $filePath;
    }

    public function list_outlet(Request $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $is_toko = $user->is_toko;
        if($is_toko == '0'){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Hak Akses Tidak Dibolehkan.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $id_toko = $user->id_toko;
        $search = $request->get('search');
        $media_url = url('/storage/public');
        $status = $request->get('status') ? $request->get('status') : 1;
        if($request->get('status') == '0'){
            $status = 0;
        }
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        $get_outlet = Outlet::where('outlet.id_toko', $id_toko)
                            ->where('outlet.status_active', $status)
                            ->join('users', 'outlet.id_user', 'users.id')
                            ->selectRaw("id_outlet, outlet.id_toko, nama_outlet, alamat_outlet, email, username, CONCAT('" . $media_url . "', url_logo) as url_logo, outlet.created_at");

        if (!empty($search)) {
            $get_outlet->where(function ($q) use ($search) {
                return $q->where('outlet.nama_outlet', 'like', '%' . $search . '%');
            });
        }

        $get_outlet = $get_outlet->paginate($per_page)->withQueryString();

        if($get_outlet){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_outlet;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function create_outlet(RegisterOutletRequest $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $is_toko = $user->is_toko;
        if($is_toko == '0'){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Hak Akses Tidak Dibolehkan.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $id_toko = $user->id_toko;
        $cek_email = User::Where('email', $request->email)->first();
        if($cek_email != null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Email Sudah Terdaftar';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $cek_telepon = User::Where('no_telepon', $request->no_telepon)->first();
        if($cek_telepon != null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Nomor Telepon Sudah Terdaftar';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        DB::begintransaction();
        try {
            if($request->url_logo != null){
                $url_logo = $this->_handleUpload($request->url_logo);
            }else{
                $url_logo = null;
            }
            $data_user = [
                'username' => $request->username,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'password' => Hash::make($request->password),
                'url_logo' => $url_logo,
                'is_outlet' => '1',
                'id_toko' => $id_toko,
            ];
            $user = User::create($data_user);

            $data_outlet = [
                'id_user' => $user->id,
                'id_toko' => $id_toko,
                'nama_outlet' => $request->nama_outlet,
                'alamat_outlet' => $request->alamat_outlet,
            ];
            $toko = Outlet::create($data_outlet);
        } catch (\Exception $e) {
            DB::rollback();
            return Result::response(array(), $e->getMessage(), 400, false);
        }
        DB::commit();
        if($user){
            $result['status'] = true;
            $result['message'] = 'Register Outlet Berhasil Dilakukan';
            $result['data'] = array();
        } else {
            $result['status'] = false;
            $result['message'] = 'Register Outlet Gagal Dilakukan';
            $result['data'] = array();
        }
        return response()->json($result);
    }

    public function detail_outlet(Request $request){
        try {
            $user = auth()->user();
            $id_user = $user->id;
            $is_toko = $user->is_toko;
            if($is_toko == '0'){
                $code = 400;
                $result['status'] = false;
                $result['message'] = 'Hak Akses Tidak Dibolehkan.';
                $result['data'] = array();

                return response()->json($result, $code);
            }
            $id_toko = $user->id_toko;
            $id_outlet = $request->id_outlet;
            $media_url = url('/storage/public');
            $get_outlet = Outlet::where('outlet.id_toko', $id_toko)
                                ->where('outlet.id_outlet', $id_outlet)
                                ->join('users', 'outlet.id_user', 'users.id')
                                ->selectRaw("id_outlet, outlet.id_toko, nama_outlet, alamat_outlet, email, username, CONCAT('" . $media_url . "', url_logo) as url_logo, outlet.created_at")
                                ->first();
            return Result::response($get_outlet, 'Data Berhasil Didapatkan');
        } catch(\JWTException $e) {
            return Result::exception(false, 'Server sedang sibuk, coba lagi.', 500, config('app.debug')==true?$e:'');
        }
    }
}