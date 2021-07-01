<?php
/*
Copyright 2021 whatever127

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

    if(!$updateId) {
        return array('error' => 'UNSPECIFIED_UPDATE');
    }

    if(!uupApiCheckUpdateId($updateId)) {
            return array('error' => 'INCORRECT_ID');
    }

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

                    $filesPacksList = array();
                    foreach($genPack[$usePack] as $val) {
                        foreach($val as $package) {
                            $filesPacksList[] = $package;
                        }
                    }

                    array_unique($filesPacksList);
                    sort($filesPacksList);
                }
                break;

            case 'WUBFILE': break;

            case 'UPDATEONLY': break;

            default:
                if(!isset($genPack[$usePack][$desiredEdition])) {
                    return array('error' => 'UNSUPPORTED_COMBINATION');
                }

                $filesPacksList = $genPack[$usePack][$desiredEdition];
                $fileListSource = 'GENERATEDPACKS';
                break;
        }
    } else {
        $fileListSource = 'GENERATEDPACKS';
        $filesPacksList = array();
        foreach($desiredEdition as $edition) {
            $edition = strtoupper($edition);

            if(!isset($genPack[$usePack][$edition])) {
                return array('error' => 'UNSUPPORTED_COMBINATION');
            }

            $filesPacksList = array_merge($filesPacksList, $genPack[$usePack][$edition]);
        }
    }

    $rev = 1;
    if(preg_match('/_rev\./', $updateId)) {
        $rev = preg_replace('/.*_rev\./', '', $updateId);
        $updateId = preg_replace('/_rev\..*/', '', $updateId);
    }

    $updateSku = $info['sku'];
    $updateArch = (isset($info['arch'])) ? $info['arch'] : 'UNKNOWN';
    $updateBuild = (isset($info['build'])) ? $info['build'] : 'UNKNOWN';
    $updateName = (isset($info['title'])) ? $info['title'] : 'Unknown update: '.$updateId;

    if(isset($info['releasetype'])) {
        $type = $info['releasetype'];
    }
    if(!isset($type)) {
        $type = 'Production';
        if($updateSku == 189 || $updateSku == 135) foreach($info['files'] as $val) {
            if(preg_match('/NonProductionFM/i', $val['name'])) $type = 'Test';
        }
    }

    if($requestType < 2) {
        $filesInfoList = uupGetOnlineFiles($updateId, $rev, $info, $requestType, $type);
    } else {
        $filesInfoList = uupGetOfflineFiles($info);
    }

    if(isset($filesInfoList['error'])) {
        return $filesInfoList;
    }

    $baseless = preg_grep('/^baseless_|-baseless\....$/i', array_keys($filesInfoList));
    foreach($baseless as $val) {
        if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
    }

    $diffs = preg_grep('/.*_Diffs_.*|.*_Forward_CompDB_.*|\.cbsu\.cab$/i', array_keys($filesInfoList));
    foreach($diffs as $val) {
        if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
    }

    $psf = array_keys($filesInfoList);
    $psf = preg_grep('/\.psf$/i', $psf);

    $psfk = preg_grep('/Windows10\.0-KB.*/i', $psf);
    $psfk = preg_grep('/.*-EXPRESS|.*-baseless/i', $psfk, PREG_GREP_INVERT);
    if($build > 21380) foreach($psfk as $key => $val) {
        if(isset($psf[$key])) unset($psf[$key]);
    }
    unset($psfk);

    $removeFiles = array();
    foreach($psf as $val) {
        $name = preg_replace('/\.psf$/i', '', $val);
        $removeFiles[] = $name;
        unset($filesInfoList[$val]);
    }
    unset($index, $name, $psf);

    $temp = preg_grep('/'.$updateArch.'_.*|arm64\.arm_.*|arm64\.x86_.*/i', $removeFiles);
    foreach($temp as $key => $val) {
        if(isset($filesInfoList[$val.'.cab'])) unset($filesInfoList[$val.'.cab']);
        unset($removeFiles[$key]);
    }
    unset($temp);

    foreach($removeFiles as $val) {
        if(isset($filesInfoList[$val.'.esd'])) {
            if(isset($filesInfoList[$val.'.cab'])) unset($filesInfoList[$val.'.cab']);
        }
    }
    unset($removeFiles);

    $filesInfoKeys = array_keys($filesInfoList);

    switch($fileListSource) {
        case 'UPDATEONLY':
            $skipPackBuild = 1;
            $removeFiles = preg_grep('/Windows10\.0-KB.*-EXPRESS|SSU-\d*?\.\d*?-.{3,5}-EXPRESS/i', $filesInfoKeys);

            foreach($removeFiles as $val) {
                if(isset($filesInfoList[$val])) unset($filesInfoList[$val]);
            }
            unset($removeFiles);

            $filesInfoKeys = array_keys($filesInfoList);

            $filesInfoKeys = preg_grep('/Windows10\.0-KB|SSU-\d*?\.\d*?-.{3,5}/i', $filesInfoKeys);
            if(count($filesInfoKeys) == 0) {
                return array('error' => 'NOT_CUMULATIVE_UPDATE');
            }
            break;

        case 'WUBFILE':
            $skipPackBuild = 1;
            $filesInfoKeys = preg_grep('/WindowsUpdateBox.exe/i', $filesInfoKeys);
            break;
    }

    $uupCleanFunc = 'uupCleanName';
    if($updateSku == 189) $uupCleanFunc = 'uupCleanWCOS';
    if($updateSku == 135) $uupCleanFunc = 'uupCleanHolo';

    if($fileListSource == 'GENERATEDPACKS') {
        $temp = preg_grep('/Windows10\.0-KB.*-EXPRESS|SSU-\d*?\.\d*?-.{3,5}-EXPRESS/i', $filesInfoKeys, PREG_GREP_INVERT);
        $temp = preg_grep('/Windows10\.0-KB|SSU-\d*?\.\d*?-.{3,5}/i', $temp);
        $filesPacksList = array_merge($filesPacksList, $temp);

        $newFiles = array();
        foreach($filesPacksList as $val) {
            $name = $uupCleanFunc($val);
            $filesPacksKeys[] = $name;

            if(isset($filesInfoList[$name])) {
                $newFiles[$name] = $filesInfoList[$name];
            }
        }

        $filesInfoList = $newFiles;
        $filesInfoKeys = array_keys($filesInfoList);

        $filesPacksKeys = array_unique($filesPacksKeys);
        sort($filesPacksKeys);
        $compare = array_diff($filesPacksKeys, $filesInfoKeys);

        if(count($compare)) {
            foreach($compare as $val) {
                consoleLogger("Missing file: $val");
            }
            return array('error' => 'MISSING_FILES');
        }
    }

    if(empty($filesInfoKeys)) {
        return array('error' => 'NO_FILES');
    }

    $filesNew = array();
    foreach($filesInfoKeys as $val) {
       $filesNew[$val] = $filesInfoList[$val];
    }

    $files = $filesNew;
    ksort($files);

    consoleLogger('Successfully parsed the information.');

    return array(
        'apiVersion' => uupApiVersion(),
        'updateName' => $updateName,
        'arch' => $updateArch,
        'build' => $updateBuild,
        'sku' => $updateSku,
        'files' => $files,
    );
}

function uupGetOnlineFiles($updateId, $rev, $info, $cacheRequests, $type) {
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
        $postData = composeFileGetRequest($updateId, uupDevice(), $info, $rev, $type);
        $out = sendWuPostRequest('https://fe3cr.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured', $postData);
        consoleLogger('Information has been successfully fetched.');
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

    $uupCleanFunc = 'uupCleanName';
    if($info['sku'] == 189) $uupCleanFunc = 'uupCleanWCOS';
    if($info['sku'] == 135) $uupCleanFunc = 'uupCleanHolo';

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
            $size = -1;
        } else {
            $name = $info[$sha1]['name'];
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
            $temp['debug'] = $val->asXML();

            $newName = $uupCleanFunc($name);
            $files[$newName] = $temp;
        }
    }

    if($cacheRequests == 1 && $cached == 0) {
        $cache = array(
            'expires' => time()+90,
            'content' => $out,
            'fetchTime' => $fetchTime,
        );

        if(!file_exists('cache')) mkdir('cache');
        @file_put_contents('cache/'.$cacheHash.'.json.gz', gzencode(json_encode($cache)."\n"));
    }

    return $files;
}

function uupGetOfflineFiles($info) {
    if(empty($info['files'])) return array();

    $uupCleanFunc = 'uupCleanName';
    if($info['sku'] == 189) $uupCleanFunc = 'uupCleanWCOS';
    if($info['sku'] == 135) $uupCleanFunc = 'uupCleanHolo';

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
            $temp['debug'] = null;

            $newName = $uupCleanFunc($name);
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

function uupCleanWCOS($name) {
    $name = preg_replace('/^(appx)_(messaging_desktop|.*?)_/i', '$1/$2/', $name);
    $name = preg_replace('/^(retail)_(.{3,5})_fre_/i', '$1/$2/fre/', $name);
    return strtr($name, 'QWERTYUIOPASDFGHJKLZXCVBNM', 'qwertyuiopasdfghjklzxcvbnm');
}

function uupCleanHolo($name) {
    $name = preg_replace('/^(appx)_(Cortana_WCOS|FeedbackHub_WCOS|HEVCExtension_HoloLens|MixedRealityViewer_arm64|MoviesTV_Hololens|Outlook_WindowsTeam|WinStore_HoloLens)_/i', '$1/$2/', $name);
    $name = preg_replace('/^(appx)_(.*?)_/i', '$1/$2/', $name);
    $name = preg_replace('/^(retail)_(.{3,5})_fre_/i', '$1/$2/fre/', $name);
    return strtr($name, 'QWERTYUIOPASDFGHJKLZXCVBNM', 'qwertyuiopasdfghjklzxcvbnm');
}
