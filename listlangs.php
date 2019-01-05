<?php
/*
Copyright 2019 UUP dump API authors

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
require_once dirname(__FILE__).'/shared/packs.php';
require_once dirname(__FILE__).'/updateinfo.php';

function uupListLangs($updateId = 0) {
    if($updateId) {
        $info = uupUpdateInfo($updateId);
    }

    if(isset($info['info'])) $info = $info['info'];

    if(isset($info['build'])) {
        $build = explode('.', $info['build']);
        $build = $build[0];
    } else {
        $build = 9841;
    }

    if(!isset($info['arch'])) {
        $arch = null;
    }

    $genPack = uupGetGenPacks($build, $info['arch'], $updateId);
    $fancyTexts = uupGetInfoTexts();
    $fancyLangNames = $fancyTexts['fancyLangNames'];

    $langList = array();
    $langListFancy = array();
    foreach(array_keys($genPack) as $val) {
        if(isset($fancyLangNames[$val])) {
            $fancyName = $fancyLangNames[$val];
        } else {
            $fancyName = $val;
        }

        $langList[] = $val;
        $langListFancy[$val] = $fancyName;
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'langList' => $langList,
        'langFancyNames' => $langListFancy,
    );
}
