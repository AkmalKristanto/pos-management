<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\OrderRequest;
use App\Http\Transformers\Result;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Storage;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\{MasterToko, Order, Penawaran};

class OrderController extends Controller
{

    private function _handleUpload($get_image) {
        $time = strtotime(date(now())) * 1000;
        $image_ = explode(',', $get_image);
        $image = $image_[1];

        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $file_base64 = base64_decode($image);
        $new_imgname = $time . '.png';
        
        $filePath = '/img-order/' . $new_imgname;
        Storage::disk('storage')->put($filePath, $file_base64);

        return $filePath;
    }

    public function list_order(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $kadar = $request->get('kadar');
        $berat = $request->get('berat');
        $qty = $request->get('qty');
        $nama_barang = $request->get('nama_barang');
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        
        $media_url = url('/storage/public');
        $get_pesanan = Order::where('kd_toko', $user->kd_toko)
                            ->where('id_penawaran', null)
                            ->selectRaw("id_order, CONCAT('" . $media_url . "', url_foto) as url_foto, nama_barang ,qty, kadar, berat, created_at, status")
                            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $get_pesanan->where(function ($q) use ($search) {
                return $q->where('order.nama_barang', 'like', '%' . $search . '%')
                            ->orwhere('order.kadar', 'like', '%' . $search . '%')
                            ->orwhere('order.berat', 'like', '%' . $search . '%')
                            ->orwhere('order.qty', 'like', '%' . $search . '%');
            });
        }
        if (!empty($kadar)) {
            $get_pesanan->where('kadar', '=', $kadar);
        }
        if (!empty($berat)) {
            $get_pesanan->where('berat', '=', $berat);
        }
        if (!empty($qty)) {
            $get_pesanan->where('qty', '=', $qty);
        }
        if (!empty($nama_barang)) {
            $get_pesanan->where('nama_barang', '=', $nama_barang);
        }

        $get_pesanan = $get_pesanan->paginate($per_page)->withQueryString();

        if($get_pesanan){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_pesanan;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function detail_order(Request $request)
    {
        $user = auth()->user();
        $id_order = $request->get('id_order');
        $media_url = url('/storage/public');
        $get_pesanan = Order::where('kd_toko', $user->kd_toko)
                            ->where('id_order', $id_order)
                            ->selectRaw("id_order, CONCAT('" . $media_url . "', url_foto) as url_foto, nama_barang ,qty, kadar, berat, created_at, status")
                            ->orderBy('created_at', 'DESC')
                            ->first();

        if($get_pesanan){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Didapatkan.';
            $result['data'] = $get_pesanan;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Didapatkan.';
            $result['data'] = array();
        }

        return response()->json($result);
    }

    public function create_order(OrderRequest $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $kd_toko = $user->kd_toko;
        $cek_toko = MasterToko::Where('kd_toko', $kd_toko)->first();
        
        if($cek_toko == null){
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Akun Tidak Ditemukan';
            $result['data'] = array();

            return response()->json($result, $code);
        }
        $tahun = date("y");
        $bulan = date("m");
        $tanggal = date("d");
        $cek_np = 'Order-'.$cek_toko->initial.'-'.$tahun.''.$bulan.'-';
        $no_order = 'Order-'.$cek_toko->initial.'-'.$tahun.''.$bulan.'-001';
        $cek = Order::Where('no_order', 'like', '%' . $cek_np . '%')
                            ->where('kd_toko', $kd_toko)
                            ->orderBy('created_at', 'DESC')
                            ->orderBy('no_order', 'DESC')
                            ->first();
        // dd($cek);
        if($cek == null){
            $no_order = 'Order-'.$cek_toko->initial.'-'.$tahun.''.$bulan.'-001';
        }else{
            // dd($cek);
            $nmr = explode('-', $cek->no_order);
            $nmr_next = $nmr[3] + 1;
            $no_order = 'Order-'.$cek_toko->initial.'-'.$tahun.''.$bulan.'-'.$nmr_next;
        }

        $url_foto = $this->_handleUpload($request->url_foto);
        $req = $request->all();
        // dd($req);
        $req["url_foto"] = $url_foto;
        $req["id_user"] = $id_user;
        $req["kd_toko"] = $kd_toko;
        $req["no_order"] = $no_order;
        $req["status"] = 1;
        $create_order = Order::create($req);

        if($create_order){
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Dibuat.';
            $result['data'] = $create_order;
        } else {
            $result['status'] = false;
            $result['message'] = 'Data Gagal Dibuat.';
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