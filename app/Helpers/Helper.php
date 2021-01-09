<?php

if (!function_exists('lpApiResponse')) {
    function lpApiResponse(bool $error, string $message, $data=[]) {
        $arrRet = array(
            "error"   => $error,
            "message" => $message,
        );

        if(is_array($data) && count($data) > 0){
            $arrRet['data'] = $data;
        }

        return $arrRet;
    }
}
