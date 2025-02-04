<?php

namespace App\Traits;

use Illuminate\Support\Facades\Validator;

trait ApiReturnFormatTrait
{
    protected function setMessage($message = '', $type = 'success')
    {
        session()->flush('message', $message);
        session()->flush('type', $type);
    }

    protected function validateWithJson($data = [], $rules = [])
    {
        $validator = Validator::make($data, $rules);

        if ($validator->passes()) {
            return true;
        }

        return response()->json($validator->errors(), 422);
    }

    protected function responseWithSuccess($message = '', $data = [], $code = 200)
    {
        return response()->json([
            'success'     => true,
            'status_code' => $code,
            'message'     => $message,
            'data'        => $data,
        ], $code);
    }

    protected function responseWithNotFound($message = '', $code = 200)
    {
        return response()->json([
            'success'     => false,
            'status_code' => $code,
            'message'     => $message,
        ], $code);
    }

    protected function responseWithError($message = '', $data = [], $code = null)
    {
        if ($code == null) {
            $code = 400;
        }

        return response()->json([
            'success'     => false,
            'status_code' => $code,
            'message'     => $message,
            'data'        => $data,
        ], $code);
    }
}
