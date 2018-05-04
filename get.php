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

require_once dirname(__FILE__).'/shared/main.php';
require_once dirname(__FILE__).'/shared/requests.php';
require_once dirname(__FILE__).'/shared/packs.php';

function uupGetFiles($updateId = 'c2a1d787-647b-486d-b264-f90f3782cdc6', $usePack = 0, $desiredEdition = 0) {
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

    $packs = uupGetPacks($build);
    $packsForLangs = $packs['packsForLangs'];
    $editionPacks = $packs['editionPacks'];
    $checkEditions = $packs['allEditions'];
    $skipNeutral = $packs['skipNeutral'];
    $skipLangPack = $packs['skipLangPack'];
    $packs = $packs['packs'];

    $noLangPack = 0;
    $noNeutral = 0;

    $useGeneratedPacks = 0;

    if(file_exists('packs/'.$updateId.'.json.gz') && $usePack) {
        $genPack = @gzdecode(@file_get_contents('packs/'.$updateId.'.json.gz'));

        if(!empty($genPack)) {
            $genPack = json_decode($genPack, 1);

            if(!isset($genPack[$usePack])) {
                return array('error' => 'UNSUPPORTED_LANG');
            }

            $packsForLangs = array();
            $packsForLangs[$usePack] = array(0);

            $useGeneratedPacks = 1;
        }
    }

    if($usePack) {
        $usePack = strtolower($usePack);
        if(!isset($packsForLangs[$usePack])) {
            return array('error' => 'UNSUPPORTED_LANG');
        }
    }

    $desiredEdition = strtoupper($desiredEdition);

    switch($desiredEdition) {
        case '0':
            if($useGeneratedPacks) {
                $desiredEdition = 'GENERATEDPACKS';

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

        case 'UPDATEONLY':
            if(!isset($info['containsCU']) || !$info['containsCU']) {
                return array('error' => 'NOT_CUMULATIVE_UPDATE');
            }
            break;

        default:
            if($useGeneratedPacks) {
                if(!isset($genPack[$usePack][$desiredEdition])) {
                    return array('error' => 'UNSUPPORTED_COMBINATION');
                }

                $filesList = $genPack[$usePack][$desiredEdition];
                $desiredEdition = 'GENERATEDPACKS';
                break;
            }

            if(!$usePack) {
                return array('error' => 'UNSPECIFIED_LANG');
            }

            if(!isset($editionPacks[$desiredEdition])) {
                return array('error' => 'UNSUPPORTED_EDITION');
            }

            $supported = 0;
            foreach($packsForLangs[$usePack] as $val) {
                if($editionPacks[$desiredEdition] == $val) $supported = 1;
            }

            if(!$supported) {
                return array('error' => 'UNSUPPORTED_COMBINATION');
            }
            unset($supported);

            if(isset($skipLangPack[$desiredEdition])) {
                if($skipLangPack[$desiredEdition]) {
                    $noLangPack = 1;
                }
            }

            if(isset($skipNeutral[$desiredEdition])) {
                if($skipNeutral[$desiredEdition]) {
                    $noNeutral = 1;
                }
            }

            $checkEditions = array($desiredEdition);
            break;
    }

    if($noNeutral) {
        foreach($packsForLangs[$usePack] as $num) {
            if(isset($packs[$num]['editionNeutral'])) {
                unset($packs[$num]['editionNeutral']);
            }
        }
    }

    $rev = 1;
    if(preg_match('/_rev\./', $updateId)) {
        $rev = preg_replace('/.*_rev\./', '', $updateId);
        $updateId = preg_replace('/_rev\..*/', '', $updateId);
    }

    $fetchTime = time();
    consoleLogger('Fetching information from the server...');
    $postData = composeFileGetRequest($updateId, uupDevice(), $info, $rev);
    $out = sendWuPostRequest('https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured', $postData);
    consoleLogger('Information has been successfully fetched.');

    consoleLogger('Parsing information...');
    preg_match_all('/<FileLocation>.*?<\/FileLocation>/', $out, $out);
    if(empty($out[0])) {
        consoleLogger('An error has occurred');
        return array('error' => 'EMPTY_FILELIST');
    }

    $updateArch = (isset($info['arch'])) ? $info['arch'] : 'UNKNOWN';
    $updateBuild = (isset($info['build'])) ? $info['build'] : 'UNKNOWN';
    $updateName = (isset($info['title'])) ? $info['title'] : 'Unknown update: '.$updateId;

    $info = $info['files'];
    $out = preg_replace('/<FileLocation>|<\/FileLocation>/', '', $out[0]);

    $files = array();
    foreach($out as $val) {
        $sha1 = explode('<FileDigest>', $val, 2);
        $sha1 = explode('</FileDigest>', $sha1[1], 2);
        $sha1 = bin2hex(base64_decode($sha1[0]));

        preg_match('/files\/.{8}-.{4}-.{4}-.{4}-.{12}/', $val, $guid);
        $guid = preg_replace('/files\/|\?$/', '', $guid[0]);

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
            $url = explode('<Url>', $val, 2);
            $url = explode('</Url>', $url[1], 2);
            $url = html_entity_decode($url[0]);

            preg_match('/P1=.*?&/', $url, $expire);
            if(isset($expire[0])) {
                $expire = preg_replace('/P1=|&$/', '', $expire[0]);
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

            $newName = preg_replace('/^cabs_|~31bf3856ad364e35/i', '', $name);
            $newName = preg_replace('/~~\.|~\./', '.', $newName);
            $newName = preg_replace('/~/', '-', $newName);

            $files[$newName] = $temp;
        }
    }
    unset($temp, $newName);

    $baseless = preg_grep('/^baseless_/i', array_keys($files));
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

        if(isset($files[$val.'.ESD'])) {
            if(isset($files[$val.'.cab'])) unset($files[$val.'.cab']);
        }
    }
    unset($removeFiles);

    $filesKeys = array_keys($files);

    switch($desiredEdition) {
        case 'UPDATEONLY':
            $skipPackBuild = 1;
            $removeFiles = preg_grep('/Windows10\.0-KB.*-EXPRESS/i', $filesKeys);

            foreach($removeFiles as $val) {
                if(isset($files[$val])) unset($files[$val]);
            }

            unset($removeFiles, $temp);
            $filesKeys = array_keys($files);

            $filesKeys = preg_grep('/Windows10\.0-KB/i', $filesKeys);
            break;

        case 'WUBFILE':
            $skipPackBuild = 1;
            $filesKeys = preg_grep('/WindowsUpdateBox.exe/i', $filesKeys);
            break;

        case 'GENERATEDPACKS':
            $skipPackBuild = 1;
            break;

        default:
            $skipPackBuild = 0;
            break;
    }

    if($usePack && !$skipPackBuild) {
        $esd = array_keys($files);
        $esd = preg_grep('/\.esd$/i', $esd);

        foreach($esd as $key => $val) {
            $esd[$key] = strtoupper($val);
        }

        foreach($checkEditions as $val) {
            $testEsd[] = $val.'_'.strtoupper($usePack).'.ESD';
        }

        $foundMetadata = array_intersect($testEsd, $esd);
        consoleLogger('Found '.count($foundMetadata).' metadata ESD file(s).');

        if(empty($foundMetadata)) {
            return array('error' => 'NO_METADATA_ESD');
        }

        $removeFiles = array();
        $removeFiles[0] = preg_grep('/RetailDemo-OfflineContent/i', $filesKeys);
        $removeFiles[1] = preg_grep('/Windows10\.0-KB.*-EXPRESS/i', $filesKeys);

        foreach($removeFiles as $val) {
            foreach($val as $temp) {
                if(isset($files[$temp])) unset($files[$temp]);
            }
        }
        unset($removeFiles, $temp, $val);

        $filesKeys = array_keys($files);
        $filesTemp = array();

        if(!$noLangPack) {
            $temp = preg_grep('/.*'.$usePack.'-Package.*/i', $filesKeys);
            $filesTemp = array_merge($filesTemp, $temp);

            $temp = preg_grep('/.*'.$usePack.'_lp..../i', $filesKeys);
            $filesTemp = array_merge($filesTemp, $temp);
        }

        foreach($packsForLangs[$usePack] as $num) {
            foreach($packs[$num] as $key => $val) {
                if($key == 'editionNeutral'
                || $key == $desiredEdition
                || !$desiredEdition) {
                    $temp = packsByEdition($key, $val, $usePack, $filesKeys);
                    $filesTemp = array_merge($filesTemp, $temp);
                }
            }
        }

        $filesKeys = array_unique($filesTemp);
        unset($filesTemp, $temp, $val, $num);
    }

    if($desiredEdition == 'GENERATEDPACKS') {
        $newFiles = array();
        foreach($filesList as $val) {
            $name = preg_replace('/~31bf3856ad364e35/', '', $val);
            $name = preg_replace('/~~\.|~\./', '.', $name);
            $name = preg_replace('/~/', '-', $name);

            $newFiles[$name] = $files[$name];
        }

        $files = $newFiles;
        $filesKeys = array_keys($files);
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

function packsByEdition($edition, $pack, $lang, $filesKeys) {
    $filesTemp = array();

    if($edition != 'editionNeutral') {
        $temp = preg_grep('/'.$edition.'_'.$lang.'\.esd/i', $filesKeys);
        $filesTemp = array_merge($filesTemp, $temp);
    }

    foreach($pack as $val) {
        $temp = preg_grep('/'.$val.'.*/i', $filesKeys);
        $filesTemp = array_merge($filesTemp, $temp);
    }

    return $filesTemp;
}
?>
