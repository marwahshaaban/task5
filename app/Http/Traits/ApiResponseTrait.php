<?php

namespace App\Http\Traits;

trait ApiResponseTrait
{
    public function apiResponse($data,$token,$message,$status){

        $array = [
            'data' =>$data,
            'message' =>$message,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];

        return response()->json($array,$status);
    }
}