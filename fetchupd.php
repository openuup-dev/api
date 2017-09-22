<?php
/*
Copyright 2017 UUP dump API authors

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

function uupFetchUpd($arch = 'amd64', $ring = 'WIF', $flight = 'Active', $build = '16251') {
    require_once 'shared/main.php';
    require_once 'shared/requests.php';
    brand();

    $arch = strtolower($arch);
    $ring = strtoupper($ring);
    $flight = ucwords(strtolower($flight));
    if($flight == 'Current') $flight = 'Active';

    if(!($arch == 'amd64' || $arch == 'x86' || $arch == 'arm64')) {
        return array('error' => 'UNKNOWN_ARCH');
    }

    if(!($ring == 'WIF' || $ring == 'WIS' || $ring == 'RP')) {
        return array('error' => 'UNKNOWN_RING');
    }

    if(!($flight == 'Skip' || $flight == 'Active')) {
        return array('error' => 'UNKNOWN_FLIGHT');
    }

    if($flight == 'Skip' && $ring != 'WIF') {
        return array('error' => 'UNKNOWN_COMBINATION');
    }

    if($build < 15063 || $build > 65536) {
        return array('error' => 'ILLEGAL_BUILD');
    }

    if($flight == 'Active' && $ring == 'RP') $flight = 'Current';

    $build = '10.0.'.$build.'.0';

    consoleLogger('Fetching information from the server...');
    $postData = composeFetchUpdRequest($device, $encData, $arch, $flight, $ring, $build);
    $out = sendWuPostRequest('https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx', $postData);

    $out = html_entity_decode($out);
    consoleLogger('Information was successfully fetched.');

    consoleLogger('Checking build information...');
    preg_match('/<Files>.*<\/Files>/', $out, $fileList);
    if(empty($fileList[0])) {
        consoleLogger('An error has occured');
        return array('error' => 'ERROR');
    }

    preg_match('/<FlightMetadata>.*?<Relationships>/', $out, $out2);

    preg_match('/"BuildFlightVersion":".*"}/', $out2[0], $foundBuild);
    $foundBuild = preg_replace('/"BuildFlightVersion":"10\.0\.|"}/', '', $foundBuild[0]);
    preg_match_all('/<Title>.*?<\/Title>/i', $out, $updateTitle);
    $updateTitle = preg_replace('/<Title>|<\/Title>/i', '', $updateTitle[0]);
    $updateTitle = preg_grep('/'.$foundBuild.'/', $updateTitle);
    sort($updateTitle);

    if(isset($updateTitle[0])) {
        $updateTitle = $updateTitle[0];
    } else {
        $updateTitle = 'Windows 10 build '.$foundBuild;
    }

    $out = preg_replace('/>/', ">\n", $out2[0]);

    preg_match('/UpdateID=".*?"/', $out, $updateId);
    $updateId = preg_replace('/UpdateID="|"$/', '', $updateId[0]);
    consoleLogger('Successfully checked build information.');
    consoleLogger('BUILD: '.$updateTitle.' '.$arch);

    $fileWrite = 'NO_SAVE';
    if(!file_exists('fileinfo/'.$updateId.'.json')) {
        consoleLogger('WARNING: This build is NOT in the database. It will be saved now.');
        consoleLogger('Parsing information to write');
        if(!file_exists('fileinfo')) mkdir('fileinfo');

        $fileList = preg_replace('/<Files>|<\/Files>/', '', $fileList[0]);
        preg_match_all('/<File .*?>/', $fileList, $fileList);

        $shaArray = array();

        foreach($fileList[0] as $val) {
            preg_match('/Digest=".*?"/', $val, $sha1);
            $sha1 = preg_replace('/Digest="|"$/', '', $sha1[0]);
            $sha1 = bin2hex(base64_decode($sha1));

            preg_match('/FileName=".*?"/', $val, $name);
            $name = preg_replace('/FileName="|"$/', '', $name[0]);

            preg_match('/Size=".*?"/', $val, $size);
            $size = preg_replace('/Size="|"$/', '', $size[0]);

            $temp = array(
                'name' => $name,
                'size' => $size,
            );

            $shaArray = array_merge($shaArray, array($sha1 => $temp));
        }

        unset($temp, $sha1, $name, $size);

        ksort($shaArray);

        $temp = array(
            'title' => $updateTitle,
            'ring' => $ring,
            'flight' => $flight,
            'arch' => $arch,
            'build' => $foundBuild,
            'checkBuild' => $build,
            'files' => $shaArray,
        );

        consoleLogger('Successfully parsed the information.');
        consoleLogger('Writing new build information to the disk...');

        $success = file_put_contents('fileinfo/'.$updateId.'.json', json_encode($temp)."\n");
        if($success) {
            consoleLogger('Successfully written build information to the disk.');
            $fileWrite = 'INFO_WRITTEN';
        } else {
            consoleLogger('An error has occured while writing the information to the disk.');
        }
    } else {
        consoleLogger('This build already exists in the database.');
    }

    return array(
        'apiVersion' => $apiVersion,
        'updateId' => $updateId,
        'updateTitle' => $updateTitle,
        'foundBuild' => $foundBuild,
        'arch' => $arch,
        'fileWrite' => $fileWrite,
    );
}
?>
