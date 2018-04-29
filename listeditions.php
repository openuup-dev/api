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

require_once dirname(__FILE__).'/shared/main.php';
require_once dirname(__FILE__).'/shared/packs.php';
require_once dirname(__FILE__).'/updateinfo.php';

function uupListEditions($lang = 'en-us', $updateId = 0) {
    if($updateId) {
        $info = uupUpdateInfo($updateId, 'build');
    }

    if(isset($info['info'])) {
        $build = explode('.', $info['info']);
        $build = $build[0];
    } else {
        $build = 9841;
    }

    $packs = uupGetPacks($build);
    $packsForLangs = $packs['packsForLangs'];
    $fancyEditionNames = $packs['fancyEditionNames'];
    $packs = $packs['packs'];

    if(file_exists('packs/'.$updateId.'.json.gz')) {
        $genPack = @gzdecode(@file_get_contents('packs/'.$updateId.'.json.gz'));

        if(!empty($genPack)) {
            $genPack = json_decode($genPack, 1);

            if(!isset($genPack[$lang])) {
                return array('error' => 'UNSUPPORTED_LANG');
            }

            $packsForLangs = array();
            $packsForLangs[$lang] = array(0);

            $packs = array();
            $packs[0] = $genPack[$lang];
        }
    }

    if($lang) {
        $lang = strtolower($lang);
        if(!isset($packsForLangs[$lang])) {
            return array('error' => 'UNSUPPORTED_LANG');
        }
    }

    $editionList = array();
    $editionListFancy = array();
    foreach($packsForLangs[$lang] as $val) {
        foreach(array_keys($packs[$val]) as $edition) {
            if($edition == 'editionNeutral') continue;

            if(isset($fancyEditionNames[$edition])) {
                $fancyName = $fancyEditionNames[$edition];
            } else {
                $fancyName = $edition;
            }

            $editionList[] = $edition;
            $editionListFancy[$edition] = $fancyName;
        }
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'editionList' => $editionList,
        'editionFancyNames' => $editionListFancy,
    );
}
?>
