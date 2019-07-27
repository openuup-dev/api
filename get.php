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

require_once dirname(__FILE__).'/shared/main.php';
require_once dirname(__FILE__).'/shared/requests.php';
require_once dirname(__FILE__).'/shared/packs.php';

/*
$updateId       = Update Identifier
$usePack        = Desired language
$desiredEdition = Desired edition

$requestType    = 0 = uncached request,;
                  1 = use cache if available;
                  2 = offline information retrieval
*/

function uupGetFiles(
    $updateId = 'c2a1d787-647b-486d-b264-f90f3782cdc6',
    $usePack = 0,
    $desiredEdition = 0,
    $requestType = 0
) {
    uupApiPrintBrand();

    $info = @file_get_contents('fileinfo/'.$updateId.'.json');
    if(empty($info)) {
        $info = array(
            'ring' => 'WIF',
            'flight' => 'Active',
            'arch' => 'amd64',
            'checkBuild' => '10.0.16251.0',
            'sku' => '48',
            'files' => array(),
        );
    } else {
        $info = json_decode($info, true);
    }

    if(isset($info['build'])) {
        $build = explode('.', $info['build']);
        $build = $build[0];
    } else {
        $build = 9841;
    }

    if(!isset($info['sku'])) {
        $info['sku'] = 48;
    }

    if($usePack) {
        $genPack = uupGetGenPacks($build, $info['arch'], $updateId);
        if(empty($genPack)) return array('error' => 'UNSUPPORTED_COMBINATION');

        if(!isset($genPack[$usePack])) {
            return array('error' => 'UNSUPPORTED_LANG');
        }
    }

    if(!is_array($desiredEdition)) {
        $desiredEdition = strtoupper($desiredEdition);
        $fileListSource = $desiredEdition;

        switch($desiredEdition) {
            case '0':
                if($usePack) {
                    $fileListSource = 'GENERATEDPACKS';

                    $filesList = array();
                    foreach($genPack[$usePack] as $val) {
                        foreach($val as $package) {
                            $filesList[] = $package;
                        }
                    }

                    array_unique($filesList);
                    sort($filesList);
                }
                break;

            case 'WUBFILE': break;

            case 'UPDATEONLY': break;

            default:
                if(!isset($genPack[$usePack][$desiredEdition])) {
                    return array('error' => 'UNSUPPORTED_COMBINATION');
                }

                $filesList = $genPack[$usePack][$desiredEdition];
                $fileListSource = 'GENERATEDPACKS';
                break;
        }
    } else {
        $fileListSource = 'GENERATEDPACKS';
        $filesList = array();
        foreach($desiredEdition as $edition) {
            $edition = strtoupper($edition);

            if(!isset($genPack[$usePack][$edition])) {
                return array('error' => 'UNSUPPORTED_COMBINATION');
            }

            $filesList = array_merge($filesList, $genPack[$usePack][$edition]);
        }
    }

    $rev = 1;
    if(preg_match('/_rev\./', $updateId)) {
        $rev = preg_replace('/.*_rev\./', '', $updateId);
        $updateId = preg_replace('/_rev\..*/', '', $updateId);
    }

    $updateArch = (isset($info['arch'])) ? $info['arch'] : 'UNKNOWN';
    $updateBuild = (isset($info['build'])) ? $info['build'] : 'UNKNOWN';
    $updateName = (isset($info['title'])) ? $info['title'] : 'Unknown update: '.$updateId;

    if($requestType < 2) {
        $files = uupGetOnlineFiles($updateId, $rev, $info, $requestType);
    } else {
        $files = uupGetOfflineFiles($info);
    }

    if(isset($files['error'])) {
        return $files;
    }

    $baseless = preg_grep('/^baseless_|-baseless\....$/i', array_keys($files));
    foreach($baseless as $val) {
        if(isset($files[$val])) unset($files[$val]);
    }

    $psf = array_keys($files);
    $psf = preg_grep('/\.psf$/i', $psf);

    $removeFiles = array();
    foreach($psf as $val) {
        $name = preg_replace('/\.psf$/i', '', $val);
        $removeFiles[] = $name;
        unset($files[$val]);
    }
    unset($index, $name, $psf);

    $temp = preg_grep('/'.$updateArch.'_.*|arm64.arm_.*/i', $removeFiles);
    foreach($temp as $key => $val) {
        if(isset($files[$val.'.cab'])) unset($files[$val.'.cab']);
        unset($removeFiles[$key]);
    }
    unset($temp);

    foreach($removeFiles as $val) {
        if(isset($files[$val.'.esd'])) {
            if(isset($files[$val.'.cab'])) unset($files[$val.'.cab']);
        }
    }
    unset($removeFiles);

    $filesKeys = array_keys($files);

    switch($fileListSource) {
        case 'UPDATEONLY':
            $skipPackBuild = 1;
            $removeFiles = preg_grep('/Windows10\.0-KB.*-EXPRESS/i', $filesKeys);

            foreach($removeFiles as $val) {
                if(isset($files[$val])) unset($files[$val]);
            }

            unset($removeFiles, $temp);
            $filesKeys = array_keys($files);

            $filesKeys = preg_grep('/Windows10\.0-KB/i', $filesKeys);
            if(count($filesKeys) == 0) {
                return array('error' => 'NOT_CUMULATIVE_UPDATE');
            }
            break;

        case 'WUBFILE':
            $skipPackBuild = 1;
            $filesKeys = preg_grep('/WindowsUpdateBox.exe/i', $filesKeys);
            break;
    }

    if($fileListSource == 'GENERATEDPACKS') {
        $temp = preg_grep('/Windows10\.0-KB.*-EXPRESS/i', $filesKeys, PREG_GREP_INVERT);
        $temp = preg_grep('/Windows10\.0-KB/i', $temp);
        $filesList = array_merge($filesList, $temp);

        $newFiles = array();
        foreach($filesList as $val) {
            $name = uupCleanName($val);
            $filesListKeys[] = $name;

            if(isset($files[$name])) {
                $newFiles[$name] = $files[$name];
            }
        }

        $files = $newFiles;
        $filesKeys = array_keys($files);

        $filesListKeys = array_unique($filesListKeys);
        sort($filesListKeys);
        $compare = array_diff($filesListKeys, $filesKeys);

        if(count($compare)) {
            foreach($compare as $val) {
                consoleLogger("Missing file: $val");
            }
            return array('error' => 'MISSING_FILES');
        }
    }

    if(empty($filesKeys)) {
        return array('error' => 'NO_FILES');
    }

    foreach($filesKeys as $val) {
       $filesNew[$val] = $files[$val];
    }

    $files = $filesNew;
    ksort($files);

    consoleLogger('Successfully parsed the information.');

    return array(
        'apiVersion' => uupApiVersion(),
        'updateName' => $updateName,
        'arch' => $updateArch,
        'build' => $updateBuild,
        'files' => $files,
    );
}

function uupGetOnlineFiles($updateId, $rev, $info, $cacheRequests) {
    $cacheHash = hash('sha256', strtolower("api-get-${updateId}_rev.$rev"));
    $cached = 0;

    if(file_exists('cache/'.$cacheHash.'.json.gz') && $cacheRequests == 1) {
        $cache = @gzdecode(@file_get_contents('cache/'.$cacheHash.'.json.gz'));
        $cache = json_decode($cache, 1);

        if(!empty($cache['content']) && ($cache['expires'] > time())) {
            consoleLogger('Using cached response...');
            $out = $cache['content'];
            $fetchTime = $cache['fetchTime'];
            $cached = 1;
        } else {
            $cached = 0;
        }

        unset($cache);
    }

    if(!$cached) {
        $fetchTime = time();
        consoleLogger('Fetching information from the server...');
        $postData = composeFileGetRequest($updateId, uupDevice(), $info, $rev);
        $out = sendWuPostRequest('https://fe3cr.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured', $postData);
        consoleLogger('Information has been successfully fetched.');

        if($cacheRequests == 1) {
            $cache = array(
                'expires' => time()+90,
                'content' => $out,
                'fetchTime' => $fetchTime,
            );

            if(!file_exists('cache')) mkdir('cache');
            @file_put_contents('cache/'.$cacheHash.'.json.gz', gzencode(json_encode($cache)."\n"));

            unset($cache);
        }
    }

    consoleLogger('Parsing information...');
    $xmlOut = @simplexml_load_string($out);
    if($xmlOut === false) {
        @unlink('cache/'.$cacheHash.'.json.gz');
        return array('error' => 'XML_PARSE_ERROR');
    }

    $xmlBody = $xmlOut->children('s', true)->Body->children();

    if(!isset($xmlBody->GetExtendedUpdateInfo2Response)) {
        consoleLogger('An error has occurred');
        return array('error' => 'EMPTY_FILELIST');
    }

    $getResponse = $xmlBody->GetExtendedUpdateInfo2Response;
    $getResult = $getResponse->GetExtendedUpdateInfo2Result;

    if(!isset($getResult->FileLocations)) {
        consoleLogger('An error has occurred');
        return array('error' => 'EMPTY_FILELIST');
    }

    $fileLocations = $getResult->FileLocations;
    $info = $info['files'];

    $files = array();
    foreach($fileLocations->FileLocation as $val) {
        $sha1 = bin2hex(base64_decode((string)$val->FileDigest));
        $url = (string)$val->Url;

        preg_match('/files\/(.{8}-.{4}-.{4}-.{4}-.{12})/', $url, $guid);
        $guid = $guid[1];

        if(empty($info[$sha1]['name'])) {
            $name = $guid;
        } else {
            $name = $info[$sha1]['name'];
        }

        if(empty($info[$sha1]['name'])) {
            $size = -1;
        } else {
            $size = $info[$sha1]['size'];
        }

        if(!isset($fileSizes[$name])) $fileSizes[$name] = -2;

        if($size > $fileSizes[$name]) {
            preg_match('/P1=(.*?)&/', $url, $expire);
            if(isset($expire[0])) {
                $expire = $expire[1];
            }

            $expire = intval($expire);

            if($size < 0) {
                $temp = ($expire - $fetchTime) / 600;
                $size = ($temp - 1) * 31457280;
                if($size < 0) $size = 0;
                unset($temp);
            }

            $fileSizes[$name] = $size;

            $temp = array();
            $temp['sha1'] = $sha1;
            $temp['size'] = $size;
            $temp['url'] = $url;
            $temp['uuid'] = $guid;
            $temp['expire'] = $expire;


            $newName = uupCleanName($name);
            $files[$newName] = $temp;
        }
    }

    return $files;
}

function uupGetOfflineFiles($info) {
    if(empty($info['files'])) return array();

    consoleLogger('Parsing information...');
    foreach($info['files'] as $sha1 => $val) {
        $name = $val['name'];
        $size = $val['size'];
        if(!isset($fileSizes[$name])) $fileSizes[$name] = 0;

        if($size > $fileSizes[$name]) {
            $fileSizes[$name] = $size;

            $temp = array();
            $temp['sha1'] = $sha1;
            $temp['size'] = $size;
            $temp['url'] = null;
            $temp['uuid'] = null;
            $temp['expire'] = 0;

            $newName = uupCleanName($name);
            $files[$newName] = $temp;
        }
    }

    return $files;
}

function uupCleanName($name) {
    $replace = array(
        'cabs_' => null,
        'metadataesd_' => null,
        'prss_signed_appx_' => null,
        '~31bf3856ad364e35' => null,
        '~~.' => '.',
        '~.' => '.',
        '~' => '-',
    );

    $name = strtr($name, 'QWERTYUIOPASDFGHJKLZXCVBNM', 'qwertyuiopasdfghjklzxcvbnm');
    return strtr($name, $replace);
}
