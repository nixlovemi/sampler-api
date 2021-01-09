<?php
if (!function_exists('lpApiResponse')) {
    /**
     * Returns an array for Api Response
     *
     * @param boolean $error    [true | false]
     * @param string $message   [the message]
     * @param array $data       [array of data]
     * @return array
     */
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

if (!function_exists('lpValidateIsbn')) {
    /**
     * Validate the 10-digits ISBN code
     *
     * @param string $isbn
     * @return bool
     */
    function lpValidateIsbn(string $isbn) {
        if( strlen($isbn) != 10 ){
            return false;
        }

        $checkSum = 0;
        for($i=0; $i<strlen($isbn); $i++){
            // result: (10 - 0) = 10 * first digit
            //         (10 - 1) = 9 * second digit ...
            $checkSum += (10 - $i) * $isbn[$i];
        }

        return $checkSum % 11 === 0;
    }
}