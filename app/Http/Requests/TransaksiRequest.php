<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class TransaksiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Override failed validation response
     *
     * @param Validation $validator
     *
     * @return json
     */
    protected function failedValidation(Validator $validator)
    {
        if ($validator->fails()) {
            throw new HttpResponseException(
                response()->json(
                    [   
                        'status' => false,
                        'message' => "Pastikan Semua Field Telah Diisi Dengan Benar.",
                        'data' => $validator->errors()
                    ],
                    400
                )
            );
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "nama_order" => "required|max:100",
            "jumlah" => "required",
            "tax" => "required",
            "service" => "required",
            "total" => "required",
            "payment_method" => "required",
            "array_produk" => "required",
            "type_order" => "required",
        ];
    }
}
