<?php

namespace App\Http\Transformers;


/**
*  Class Json is transformers from raw data to json view
*/
class Result
{
    public static function response($data = null, $message = null,  $code = 200, $status = true)
    {
        $result['status'] = $status;
        $result['message'] = $message;
        $result['data'] = $data;
        return response()->json($result, $code);
    }

    public static function exception($status = true, $message = null, $code = 500, $error = null)
    {
        $result['status'] = $status;
        $result['message'] = $message;
        if ($error instanceof \Exception) {
            $result['data']['error']['message'] = $error->getMessage();
            $result['data']['error']['file'] = $error->getFile();
            $result['data']['error']['line'] = $error->getLine();
        } else {
           $result['data']['error'] = $message;
        }
        return response()->json($result, $code);
    }

}

