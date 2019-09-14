<?php
/*
Copyright 2019 whatever127

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

function uupApiPrintBrand() {
    global $uupApiBrandPrinted;

    if(!isset($uupApiBrandPrinted)) {
        consoleLogger('UUP dump API v'.uupApiVersion());
        $uupApiBrandPrinted = 1;
    }
}

function randStr($length = 4) {
    $characters = '0123456789abcdef';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function genUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        rand(0, 0xffff),
        rand(0, 0xffff),

        rand(0, 0xffff),

        rand(0, 0x0fff) | 0x4000,

        rand(0, 0x3fff) | 0x8000,

        rand(0, 0xffff),
        rand(0, 0xffff),
        rand(0, 0xffff)
    );
}

function sendWuPostRequest($url, $postData) {
    $req = curl_init($url);

    $proxy = uupDumpApiGetDebug();
    if(isset($proxy['proxy'])) {
        curl_setopt($req, CURLOPT_PROXY, $proxy['proxy']);
    }

    curl_setopt($req, CURLOPT_HEADER, 0);
    curl_setopt($req, CURLOPT_POST, 1);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($req, CURLOPT_ENCODING, '');
    curl_setopt($req, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($req, CURLOPT_HTTPHEADER, array(
        'User-Agent: Windows-Update-Agent/10.0.10011.16384 Client-Protocol/1.70',
        'Content-Type: application/soap+xml; charset=utf-8',
    ));

    $out = curl_exec($req);
    curl_close($req);

    $outDecoded = html_entity_decode($out);
    preg_match('/<NewCookie>.*?<\/NewCookie>|<GetCookieResult>.*?<\/GetCookieResult>/', $outDecoded, $cookieData);

    if(!empty($cookieData)) {
        preg_match('/<Expiration>.*<\/Expiration>/', $cookieData[0], $expirationDate);
        preg_match('/<EncryptedData>.*<\/EncryptedData>/', $cookieData[0], $encryptedData);

        $expirationDate = preg_replace('/<Expiration>|<\/Expiration>/', '', $expirationDate[0]);
        $encryptedData = preg_replace('/<EncryptedData>|<\/EncryptedData>/', '', $encryptedData[0]);

        $fileData = array(
            'expirationDate' => $expirationDate,
            'encryptedData' => $encryptedData,
        );

        @file_put_contents(dirname(__FILE__).'/cookie.json', json_encode($fileData));
    }

    return $out;
}

function consoleLogger($message, $showTime = 1) {
    if(php_sapi_name() != 'cli') return
    $currTime = '';
    if($showTime) {
        $currTime = '['.date('Y-m-d H:i:s T', time()).'] ';
    }

    $msg = $currTime.$message;
    fwrite(STDERR, $msg."\n");
}

function uupDumpApiGetDebug() {
    if(!file_exists('debug.ini')) {
        return null;
    }

    $data = parse_ini_file('debug.ini');
    return $data;
}

function uupApiCheckUpdateId($updateId) {
    return preg_match(
        '/^[\da-fA-F]{8}-([\da-fA-F]{4}-){3}[\da-fA-F]{12}(_rev\.\d+)?$/',
        $updateId
    );
}
