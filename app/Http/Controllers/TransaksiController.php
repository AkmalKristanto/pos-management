<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{OrderRequest, TransaksiRequest};
use App\Http\Transformers\Result;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use DB;
use Storage;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\{MasterToko, Order, Outlet, OrderProduk, Penawaran};

class TransaksiController extends Controller
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

    public function list_transaksi(Request $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $id_toko = $user->id_toko;
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
        $kategori = $request->get('kategori');
        $search = $request->get('search');
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        
        $media_url = url('/storage/public');
        $get_pesanan = Order::where('id_toko', $id_toko)
                            ->where('id_outlet', $id_outlet)
                            ->selectRaw("id_order, no_order, nama_order, type_order , total, payment_method, created_at, payment_status")
                            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $get_pesanan->where(function ($q) use ($search) {
                return $q->where('order.nama_order', 'like', '%' . $search . '%')
                            ->orwhere('order.no_order', 'like', '%' . $search . '%')
                            ->orwhere('order.total', 'like', '%' . $search . '%');
            });
        }
        if (!empty($kategori)) {
            if($kategori == '1'){
                $start_date = date("Y-m-d 00:00:00");
                $end_date = date("Y-m-d 23:59:59");
            }
            if($kategori == '2'){
                $start_date = date("Y-m-d", strtotime("-1 days")). ' 00:00:00';
                $end_date = date("Y-m-d", strtotime("-1 days")). ' 23:59:59';
            }
            if($kategori == '3'){
                $start_date = date("Y-m-d", strtotime("this week")). ' 00:00:00';
                $end_date = date("Y-m-d 23:59:59");
            }
            if($kategori == '4'){
                $start_date = date("Y-m-d", strtotime("-1 week")). ' 00:00:00';
                $end_date = date("Y-m-d", strtotime("-1 week + 6 days")). ' 23:59:59';
            }
            if($kategori == '5'){
                $start_date = date("Y-m-d", strtotime("first day of this month")). ' 00:00:00';
                $end_date = date("Y-m-d 23:59:59");
            }
            if($kategori == '6'){
                $start_date = date("Y-m-d", strtotime("first day of previous month")). ' 00:00:00';
                $end_date = date("Y-m-d", strtotime("last day of previous month")). ' 23:59:59';
            }
            $get_pesanan->where(function ($q) use ($start_date, $end_date) {
                return $q->where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date);
            });
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

    public function detail_transaksi(Request $request)
    {
        $user = auth()->user();
        $id_order = $request->get('id_order');
        $media_url = url('/storage/public');
        $id_user = $user->id;
        $id_toko = $user->id_toko;
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
        $get_pesanan = Order::where('id_toko', $id_toko)
                            ->where('id_order', $id_order)
                            ->where('id_outlet', $id_outlet)
                            ->selectRaw("id_order, no_order, nama_order, type_order, payment_method, jumlah, tax, service, total ,created_at")
                            ->with([
                                'item' => function ($q) {
                                   $q->join('produk', 'order_produk.id_produk', 'produk.id_produk')
                                        ->join('produk_add_on', 'order_produk.id_produk_add_on', 'produk_add_on.id_produk_add_on')
                                        ->join('produk_varian', 'order_produk.id_produk_varian', 'produk_varian.id_produk_varian')
                                        ->select('order_produk.id_order_produk', 'order_produk.id_order','nama_produk', 'produk_add_on.nama as nama_add_on', 'produk_varian.nama_varian', 'qty', 'keterangan', 'order_produk.harga', 'order_produk.harga_total');
                                }
                             ])
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

    public function create_transaksi(TransaksiRequest $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $id_toko = $user->id_toko;
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
        $tahun = date("y");
        $bulan = date("m");
        $tanggal = date("d");
        $kode = sprintf("%03d", $cek_outlet->id_outlet);
        $cek_np = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-';
        $no_order = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-001';
        $cek = Order::Where('no_order', 'like', '%' . $cek_np . '%')
                            ->where('id_toko', $id_toko)
                            ->where('id_outlet', $id_outlet)
                            ->orderBy('created_at', 'DESC')
                            ->orderBy('no_order', 'DESC')
                            ->first();
        if($cek == null){
            $no_order = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-001';
        }else{
            $nmr = explode('-', $cek->no_order);
            $nmr_next = $nmr[3] + 1;
            $kode_next = sprintf("%03d", $nmr_next);
            $no_order = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-'.$kode_next;
        }
        if($request->payment_method == 1){

            if($request->id_order == null){
                
                $req["id_toko"] = $id_toko;
                $req["id_user"] = $id_user;
                $req["id_outlet"] = $id_outlet;
                $req["no_order"] = $no_order;
                $req["nama_order"] = $request->nama_order;
                $req["jumlah"] = $request->jumlah;
                $req["tax"] = $request->tax;
                $req["service"] = $request->service;
                $req["total"] = $request->total;
                $req["payment_method"] = $request->payment_method;
                $req["payment_status"] = '1';
                $req["type_order"] = $request->type_order;
                $req["status_active"] = 1;
                DB::begintransaction();
                try {
                    $create_order = Order::create($req);
                    $id_order = $create_order->id_order;
                    $array_produk = json_decode($request->array_produk);
                    foreach($array_produk as $val){
                        $arr = [
                            'id_order' => $id_order,
                            'id_produk' => $val->id_produk,
                            'id_produk_add_on' => $val->id_produk_add_on,
                            'id_produk_varian' => $val->id_produk_varian,
                            'qty' => $val->qty,
                            'keterangan' => $val->keterangan,
                            'harga' => $val->harga,
                            'harga_total' => $val->harga_total,
                        ];
                        $create_order_produk = OrderProduk::create($arr);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    return Result::response(array(), $e->getMessage(), 400, false);
                }
                $result['status'] = true;
                $result['message'] = 'Data Berhasil Dibuat.';
                $result['data'] = $create_order;
        
                return response()->json($result);
            }else{
                $id_order = $request->id_order;
                $order = Order::findOrFail($id_order);
                $order->no_order = $no_order;
                $order->nama_order = $request->nama_order;
                $order->jumlah = $request->jumlah;
                $order->tax = $request->tax;
                $order->service = $request->service;
                $order->total = $request->total;
                $order->payment_method = $request->payment_method;
                $order->payment_status = '1';
                $order->type_order = $request->type_order;
                $order->is_draft = '0';
                DB::begintransaction();
                try {
                    $order->save();

                    $delete_row = OrderProduk::where('id_order', $id_order)
                                            ->delete();
                                            
                    $array_produk = json_decode($request->array_produk);
                    foreach($array_produk as $val){
                        $arr = [
                            'id_order' => $id_order,
                            'id_produk' => $val->id_produk,
                            'id_produk_add_on' => $val->id_produk_add_on,
                            'id_produk_varian' => $val->id_produk_varian,
                            'qty' => $val->qty,
                            'keterangan' => $val->keterangan,
                            'harga' => $val->harga,
                            'harga_total' => $val->harga_total,
                        ];
                        $update_order_produk = OrderProduk::create($arr);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();
                    return Result::response(array(), $e->getMessage(), 400, false);
                }
                $result['status'] = true;
                $result['message'] = 'Data Berhasil Dibuat.';
                $result['data'] = $order;
        
                return response()->json($result);
            }
        }else{
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Payment Method Tidak Tersedia.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
    }

    public function draft_transaksi(TransaksiRequest $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $id_toko = $user->id_toko;
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
        $tahun = date("y");
        $bulan = date("m");
        $tanggal = date("d");
        $kode = sprintf("%03d", $cek_outlet->id_outlet);
        $cek_np = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-';
        $no_order = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-001';
        $cek = Order::Where('no_order', 'like', '%' . $cek_np . '%')
                            ->where('id_toko', $id_toko)
                            ->where('id_outlet', $id_outlet)
                            ->orderBy('created_at', 'DESC')
                            ->orderBy('no_order', 'DESC')
                            ->first();
        if($cek == null){
            $no_order = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-001';
        }else{
            $nmr = explode('-', $cek->no_order);
            $nmr_next = $nmr[3] + 1;
            $kode_next = sprintf("%03d", $nmr_next);
            $no_order = 'Order-'.$kode.'-'.$tahun.''.$bulan.'-'.$kode_next;
        }
        if($request->payment_method == 1){

            $req["id_toko"] = $id_toko;
            $req["id_user"] = $id_user;
            $req["id_outlet"] = $id_outlet;
            $req["no_order"] = $no_order;
            $req["nama_order"] = $request->nama_order;
            $req["jumlah"] = $request->jumlah;
            $req["tax"] = $request->tax;
            $req["service"] = $request->service;
            $req["total"] = $request->total;
            $req["payment_method"] = $request->payment_method;
            $req["payment_status"] = '1';
            $req["type_order"] = $request->type_order;
            $req["status_active"] = 1;
            $req["is_draft"] = '1';
            DB::begintransaction();
            try {
                $create_order = Order::create($req);
                $id_order = $create_order->id_order;
                $array_produk = json_decode($request->array_produk);
                foreach($array_produk as $val){
                    $arr = [
                        'id_order' => $id_order,
                        'id_produk' => $val->id_produk,
                        'id_produk_add_on' => $val->id_produk_add_on,
                        'id_produk_varian' => $val->id_produk_varian,
                        'qty' => $val->qty,
                        'keterangan' => $val->keterangan,
                        'harga' => $val->harga,
                        'harga_total' => $val->harga_total,
                    ];
                    $create_order_produk = OrderProduk::create($arr);
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return Result::response(array(), $e->getMessage(), 400, false);
            }
            $result['status'] = true;
            $result['message'] = 'Data Berhasil Dibuat.';
            $result['data'] = $create_order;
    
            return response()->json($result);
        }else{
            $code = 400;
            $result['status'] = false;
            $result['message'] = 'Payment Method Tidak Tersedia.';
            $result['data'] = array();

            return response()->json($result, $code);
        }
    }

    public function list_draft_transaksi(Request $request)
    {
        $user = auth()->user();
        $id_user = $user->id;
        $id_toko = $user->id_toko;
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
        $kategori = $request->get('kategori');
        $search = $request->get('search');
        $page = $request->get('page') ? $request->get('page') : 1;
        $per_page = $request->get('per_page') ? $request->get('per_page') : 20;
        
        $media_url = url('/storage/public');
        $get_pesanan = Order::where('id_toko', $id_toko)
                            ->where('id_outlet', $id_outlet)
                            ->where('is_draft', 1)
                            ->selectRaw("id_order, no_order, nama_order, type_order , total, payment_method, created_at, payment_status")
                            ->orderBy('created_at', 'DESC');

        if (!empty($search)) {
            $get_pesanan->where(function ($q) use ($search) {
                return $q->where('order.nama_order', 'like', '%' . $search . '%')
                            ->orwhere('order.no_order', 'like', '%' . $search . '%')
                            ->orwhere('order.total', 'like', '%' . $search . '%');
            });
        }
        if (!empty($kategori)) {
            if($kategori == '1'){
                $start_date = date("Y-m-d 00:00:00");
                $end_date = date("Y-m-d 23:59:59");
            }
            if($kategori == '2'){
                $start_date = date("Y-m-d", strtotime("-1 days")). ' 00:00:00';
                $end_date = date("Y-m-d", strtotime("-1 days")). ' 23:59:59';
            }
            if($kategori == '3'){
                $start_date = date("Y-m-d", strtotime("this week")). ' 00:00:00';
                $end_date = date("Y-m-d 23:59:59");
            }
            if($kategori == '4'){
                $start_date = date("Y-m-d", strtotime("-1 week")). ' 00:00:00';
                $end_date = date("Y-m-d", strtotime("-1 week + 6 days")). ' 23:59:59';
            }
            if($kategori == '5'){
                $start_date = date("Y-m-d", strtotime("first day of this month")). ' 00:00:00';
                $end_date = date("Y-m-d 23:59:59");
            }
            if($kategori == '6'){
                $start_date = date("Y-m-d", strtotime("first day of previous month")). ' 00:00:00';
                $end_date = date("Y-m-d", strtotime("last day of previous month")). ' 23:59:59';
            }
            $get_pesanan->where(function ($q) use ($start_date, $end_date) {
                return $q->where('created_at', '>=', $start_date)
                        ->where('created_at', '<=', $end_date);
            });
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

    protected function resultTransformer($status, $message, $data = null)
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }
}