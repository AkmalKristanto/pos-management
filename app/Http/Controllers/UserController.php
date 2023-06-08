<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Transformers\Result;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use DB;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\{Toko, Outlet};
use App\Http\Requests\RegisterRequest;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)
                        ->first();
        if($user == null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Akun Tidak Ditemukan';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $id_user = $user->id;
        $is_toko = $user->is_toko;
        
        $userdata['email'] = $user->email;
        $userdata['password'] = $password;
        try {
            if (! $token = JWTAuth::attempt($userdata)) {
                return response()->json(['error' => 'Username dan password tidak cocok.'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $ret['email'] = $user->email;
        $ret['token_user'] = $token;

        $update_data['last_login'] = date('Y-m-d H:i:s');
        $update_data['token_user'] = $token;
        $update = User::where('email', $email)->update($update_data);

        if(!$update) {
            $result = $this->resultTransformer(false, 'Login Gagal Silakan Coba Lagi');
            return response()->json($result);
        }
        
        // if($is_toko != 0){
        //     $toko = Toko::where('kd_toko', $id_user)->first();
        //     // dd($kurir);
        //     $ret['Id_Kurir'] = $kurir->Id_Kurir;
        // }
        if($is_toko != 0){
            $id_toko = $user->id_toko;
            $toko = Toko::where('id_toko', $id_toko)->first();
            $ret['id_subscription'] = $toko->id_subscription;
            $ret['id_toko'] = $toko->id_toko;
            $ret['id_role'] = 2;
            $ret['nama_role'] = 'Toko';
            $ret['nama_toko'] = $toko->nama_toko;
        }elseif($user->is_superadmin != 0){
            $ret['id_role'] = 1;
            $ret['nama_role'] = 'Superadmin';
        }elseif($user->is_outlet != 0){
            $id_toko = $user->id_toko;
            $toko = Toko::where('id_toko', $id_toko)->first();
            $ret['id_subscription'] = $toko->id_subscription;
            $outlet = Outlet::where('id_toko', $id_toko)
                        ->where('id_user', $id_user)
                        ->first();
            $ret['id_role'] = 3;
            $ret['nama_role'] = 'Outlet';
            $ret['id_outlet'] = $outlet->id_outlet;
            $ret['id_toko'] = $toko->id_toko;
            $ret['nama_outlet'] = $outlet->nama_outlet;
        }
        
        if($ret){
            $result['status'] = true;
            $result['message'] = 'Login Berhasil';
            $result['data'] = $ret;
        } else {
            $result['status'] = false;
            $result['message'] = 'Login Gagal';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function register(RegisterRequest $request)
    {
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
        $data_toko = [
            'nama_toko' => $request->nama_toko,
        ];
        DB::begintransaction();
        try {
            $toko = Toko::create($data_toko);
            $data_user = [
                'username' => $request->username,
                'email' => $request->email,
                'no_telepon' => $request->no_telepon,
                'password' => Hash::make($request->password),
                'is_toko' => '1',
                'id_toko' => $toko->id_toko,
            ];
            $user = User::create($data_user);
        } catch (\Exception $e) {
            DB::rollback();
            return Result::response(array(), $e->getMessage(), 400, false);
        }
        DB::commit();
        // $token = JWTAuth::fromUser($user);
        // return response()->json(compact('user','token'),201);
        if($user){
            $result['status'] = true;
            $result['message'] = 'Register Berhasil Dilakukan';
            $result['data'] = $user;
        } else {
            $result['status'] = false;
            $result['message'] = 'Register Gagal Dilakukan';
            $result['data'] = array();
        }
        return response()->json($result);
    }

    public function me(Request $request){
        try {
        	$user = auth()->user();
            $id_user = $user->id;
            $ret['id_user'] = $user->id;
            $ret['username'] = $user->username;
            $ret['email'] = $user->email;
            $ret['no_telepon'] = $user->no_telepon;
            $ret['url_logo'] = $user->url_logo;
            if($user->is_toko != 0){
                $id_toko = $user->id_toko;
                $toko = Toko::where('id_toko', $id_toko)->first();
                $ret['id_subscription'] = $toko->id_subscription;
                $ret['id_role'] = 2;
                $ret['nama_role'] = 'Toko';
                $ret['nama_toko'] = $toko['nama_toko'];
            }elseif($user->is_superadmin != 0){
                $ret['id_role'] = 1;
                $ret['nama_role'] = 'Superadmin';
            }elseif($user->is_outlet != 0){
                $id_toko = $user->id_toko;
                $toko = Toko::where('id_toko', $id_toko)->first();
                $ret['id_subscription'] = $toko->id_subscription;
                $outlet = Outlet::where('id_toko', $id_toko)
                            ->where('id_user', $id_user)
                            ->first();
                $ret['id_role'] = 3;
                $ret['nama_role'] = 'Outlet';
                $ret['id_outlet'] = $outlet->id_outlet;
                $ret['id_toko'] = $toko->id_toko;
                $ret['nama_outlet'] = $outlet->nama_outlet;
            }
            return Result::response($ret, 'Berhasil');
        } catch(\JWTException $e) {
            return Result::exception(false, 'Server sedang sibuk, coba lagi.', 500, config('app.debug')==true?$e:'');
        }
    }

    public function logout()
    {
        try {
            $user = auth()->user();
            $update = [
                'token_user' => 0,
            ];
            $save = User::where('id', $user->id)->update($update);
            JWTAuth::invalidate(JWTAuth::getToken());
            return Result::response(null, 'Logout Berhasil');
        } catch(\JWTException $e) {
            return Result::exception(false, 'Server sedang sibuk, coba lagi.', 500, config('app.debug')==true?$e:'');
        }
    }

    public function refresh()
    {
        try {
            $data['token'] = auth()->refresh();
            return Result::response($data, 'Refresh Token');
        }catch(\JWTException $e) {
            return Result::exception(false, 'Server sedang sibuk, coba lagi.', 500, config('app.debug')==true?$e:'');
        }
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        return response()->json(compact('user'));
    }

    protected function resultTransformer($status, $message, $data = null)
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    public function generate_token_user(Request $request)
    {
        $id = $request->Id_User;
        $update_data['token_fcm'] = $request->token_fcm;
        // dd($update_data);
        $update = User::where('id', $id)
                    ->update($update_data);
        if($update){
            $result['status'] = true;
            $result['message'] = 'Proses Berhasil';
            $result['data'] = array();
        } else {
            $result['status'] = false;
            $result['message'] = 'Proses Gagal';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function get_cabang(Request $request)
    {
        $get = MasterToko::get();
        if($get){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan';
            $result['data'] = $get;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function get_produk(Request $request)
    {
        $get = MasterProduk::where('status', 1)->get();
        if($get){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan';
            $result['data'] = $get;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function get_pabrik(Request $request)
    {
        $get = MasterPabrik::get();
        if($get){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan';
            $result['data'] = $get;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan';
            $result['data'] = array();
        }

        return response()->json($result);
    }
}