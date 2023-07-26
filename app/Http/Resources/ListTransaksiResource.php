<?php

namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class ListTransaksiResource extends JsonResource
{
   /**
    * Transform the resource collection into an array.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return array
    */
   public function toArray($request)
   {
        $created_at = $this->created_at;
        if (! empty($created_at)) {
            $start_date_today = date("Y-m-d 00:00:00");
            $end_date_today = date("Y-m-d 23:59:59");
            $start_date_yesterday = date("Y-m-d", strtotime("-1 days")). ' 00:00:00';
            $end_date_yesterday = date("Y-m-d", strtotime("-1 days")). ' 23:59:59';
            $start_date_this_week = date("Y-m-d", strtotime("this week")). ' 00:00:00';
            $end_date_this_week = date("Y-m-d 23:59:59");
            $start_date_last_week = date("Y-m-d", strtotime("monday last week")). ' 00:00:00';
            $end_date_last_week = date("Y-m-d", strtotime("monday last week + 6 days")). ' 23:59:59';
            $start_date_this_month = date("Y-m-d", strtotime("first day of this month")). ' 00:00:00';
            $end_date_this_month = date("Y-m-d 23:59:59");
            $start_date_last_month = date("Y-m-d", strtotime("first day of previous month")). ' 00:00:00';
            $end_date_last_month = date("Y-m-d", strtotime("last day of previous month")). ' 23:59:59';
            // dd($created_at <= $end_date_today);
            if($start_date_today <= $created_at &&  $created_at <= $end_date_today){
                $kategori = 1;
            }elseif($start_date_yesterday <= $created_at &&  $created_at <= $end_date_yesterday){
                $kategori = 2;
            }elseif($start_date_this_week <= $created_at &&  $created_at <= $end_date_this_week){
                $kategori = 3;
            }elseif($start_date_last_week <= $created_at &&  $created_at <= $end_date_last_week){
                $kategori = 4;
            }elseif($start_date_this_month <= $created_at &&  $created_at <= $end_date_this_month){
                $kategori = 5;
            }elseif($start_date_last_month <= $created_at &&  $created_at <= $end_date_last_month){
                $kategori = 6;
            }
        }
        return [
            "id_order" => $this->id_order,
            "no_order" => $this->no_order,
            "nama_order" => $this->nama_order,
            "type_order" => $this->type_order,
            "total" => $this->total,
            "payment_method" => $this->payment_method,
            "created_at" => $this->created_at,
            "payment_status" =>  $this->payment_status,
            "kategori" => $kategori
        ];
   }
}
