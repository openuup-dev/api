<?php
/*
Copyright 2018 UUP dump API authors

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

function uupApiVersion() {
    return '1.15.3';
}

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

function sendWuPostRequest($url, $postData) {
    $req = curl_init($url);

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
    preg_match('/<NewCookie>.*?<\/NewCookie>/', $outDecoded, $cookieData);

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

function uupDevice() {
    return 'dAA9AEUAdwBBAHcAQQBzAE4AMwBCAEEAQQBVADEAYgB5AHMAZQBtAGIAZQBEAFYAQwArADMAZgBtADcAbwBXAHkASAA3AGIAbgBnAEcAWQBtAEEAQQBHADcAVwBtAGUAWQBmAGkAdwAxAGMAdgByAEoAbwBvAGkAUQBzAFoAZABqAHEAawBQAEkARwA5AHUAVQBQAFcAMQB3ADMAQgBVAE8ALwBKAC8AdwBwAGUAcgBhAHQAZAB2AFgAQgB6AFkAbABaAHAAYgBzAHQANAB4AHkAbABHADcAQwBSADQANABBAFoARwB4AG4ARAAvAHIAYwBVAGoAdwBEAFAAVQBXAHkAMABPAEwAaABqAFAAZABWAEgAOQBVAFkATAA4AE0AVgBJAFIAbQA5ADEALwAwAGwATQBjAHUAMwBQAFMAOQB5AFoARwBFADIAZgBOAEcAWAA2AE8AbABrAFoAaABiAG0AbAB1AGsAdwBXAEsAdQBQAHcAcABGADQARQB5AFgAcgBTAHgALwBwAEsATgArAFoANgBOAEoAdQArAFYASwBqAFoANwBoADIAUgBBADIAWQBBAEEAQQBpAEUAWQBjADgAawBnAFoAYQBsAEgAWQBBAEIASwBIADEAZAAvAEoAZABEAFUAeAB5ADIAegBkAEoAMwA0AEIAbABYAGMAYwBsAFoANABJAE4ANQBuAHcAYQBLAE4AWgA3ADEAcQA4AEMAcQBVAFgAYwBQAGMAQgBjAGEAVQBXAFgAVgBGAEMASgBsAEEAegBTAGwAQQBtAHEALwBvAFQAQQBVAFcAYQBOAEgAWQBRADAAWQBNADEAVQAzAHEATAAvAFEAdQBhAEcAMgBuAE4AdQBvADYAVwBqAFEAcQBFAFgAYwBUAGMAWAA3AGMAdgAyAHEANABzADcAWgBpADkAWQB1ACsANwBYADAAeQA1AFgAeQAvAE4AbgBLAGUAeABRAHEAdwA3AG8AKwBjAGIAMwB1AGYAQQBFAEYAdABWAHAAawB3AGwAagBZAHYAZgBvADcAdQBwAFkANQBnAGEAdABlADUAWABwADkALwBoAFoAdQBYAFIANgAwAEoAMQBUAHEATQBGADYAVQBOAEIATQB6AC8ATABCAEMAUABPAEcAWABIAGkAWgBJAEUAZQBIAE8ASwBiAEIAUAB1AFAASwBZAGMAUQBUAFkAZwBIAEkARwA3AFIAegBnAGIAMAAzAGQARABrAFUANgByAHUASQA1AHQAYgBIAHoAaQBmAHoAVgBHAHAAVABGAGcANABrAEoAYgBQAHkARQBXAHcAcwBOAEMARgA4AFQATQBuAHAARABhAHcAeAAvAEUARgBUADcAeQBLAFQAQwBYAFkAbgBhADQASQBJAFAAMABtAFcAMABYADIAdABDAHEANgA1AEUASwBlAFkAcQBBAEYARQA1AEMASABmAFEAMQBvAHIAYgBBAGEASgBWAGkAaQBFAGsARQB3AEEANwBuADMAcgAxAFIAUQB1AHgARgBlAG4ARwBkAHgAdQByAFoAdwByADAAMABEADgATQBoAGwAUQAvAFcAYQBaAGwANgBvAFgAdgBkAHUATgBXAEIAZwBPAFMANgBLAGEARwBpAC8AYgBLADEAZwBWAEgAcABzAEcAcwBFAC8AQgA4AGkAbQBsAGYAcAAzAFEATQBWAGkAUABEAEgANgBhADMANQBCAGYAOABpAHkAMgAyAG4ASABQADAATgBIADkASwBEAHoAWAB5AGkAeABnAGsARQBlAGwAaABKAEcANgAyAHUAMgBjAFQAMgBmAFgAMQA0AEwAdwBSAFkATwArAGkAcgBWAGQAYwBqAEUAQgBoAGQASwA1ADQAYgBPAFcAdgBUAHcAbQBUAFMAVwBHAE8AaAB2ADIAbwBiADIAawBQAEEANgBpADEAVgBRAFUAaQA4AEQASwB1AEsANAA4AGYAWgBIADcASQBKAGEAZQBxAHMAdwBFAD0AJgBwAD0A';
}

function uupEncryptedData() {
    $encData = 'o2vRpZuat2Ot2MKdwG29/fwtUpuU1XOgAr1Lrzo+dWejpj0CZiCSo6v05klhJbSrT0iylYNs8JXPA0owZvOmvvWYSs5+8u/hqUFtMgT/6Z99nhPNYD0Y00jol58NwB1RJcYWy3hzJz/5cAiZ60GRwa8zMDVbsI8qgF1AT/XKjbwsoOQNRTW5gVPDX/Fs/uICWI968NXQjiV7p2AP8poB/CCwA1cpgZPx';

    $cookieInfo = @file_get_contents(dirname(__FILE__).'/cookie.json');
    $cookieInfo = json_decode($cookieInfo, 1);

    if(!empty($cookieInfo)) {
        $encData = $cookieInfo['encryptedData'];
    }

    return $encData;
}
?>
