<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProdukRequest extends FormRequest
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
            "nama_produk" => "required|max:100",
            "url_logo" => "required",
            // "id_produk_add_on" => "required|max:11",
            "id_kategori" => "required",
        ];
    }
}
