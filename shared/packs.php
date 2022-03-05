<?php
/*
Copyright 2022 UUP dump API authors

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

require_once dirname(__FILE__).'/../listid.php';

function uupGetInfoTexts() {
    $fancyLangNames = array(
        'neutral' => 'Any Language',
        'ar-sa' => 'Arabic (Saudi Arabia)',
        'bg-bg' => 'Bulgarian',
        'cs-cz' => 'Czech',
        'da-dk' => 'Danish',
        'de-de' => 'German',
        'el-gr' => 'Greek',
        'en-gb' => 'English (United Kingdom)',
        'en-us' => 'English (United States)',
        'es-es' => 'Spanish (Spain)',
        'es-mx' => 'Spanish (Mexico)',
        'et-ee' => 'Estonian',
        'fi-fi' => 'Finnish',
        'fr-ca' => 'French (Canada)',
        'fr-fr' => 'French (France)',
        'he-il' => 'Hebrew',
        'hr-hr' => 'Croatian',
        'hu-hu' => 'Hungarian',
        'it-it' => 'Italian',
        'ja-jp' => 'Japanese',
        'ko-kr' => 'Korean',
        'lt-lt' => 'Lithuanian',
        'lv-lv' => 'Latvian',
        'nb-no' => 'Norwegian (Bokmal)',
        'nl-nl' => 'Dutch',
        'pl-pl' => 'Polish',
        'pt-br' => 'Portuguese (Brazil)',
        'pt-pt' => 'Portuguese (Portugal)',
        'ro-ro' => 'Romanian',
        'ru-ru' => 'Russian',
        'sk-sk' => 'Slovak',
        'sl-si' => 'Slovenian',
        'sr-latn-rs' => 'Serbian (Latin)',
        'sv-se' => 'Swedish',
        'th-th' => 'Thai',
        'tr-tr' => 'Turkish',
        'uk-ua' => 'Ukrainian',
        'zh-cn' => 'Chinese (Simplified)',
        'zh-hk' => 'Chinese (Hong Kong)',
        'zh-tw' => 'Chinese (Traditional)',
    );

    $fancyEditionNames = array(
        'APP' => 'Microsoft Store Inbox Apps',
        'FOD' => 'Features on Demand (Capabilities)',
        'CLOUD' => 'Windows S',
        'CLOUDN' => 'Windows S N',
        'CLOUDE' => 'Windows Lean',
        'CLOUDEDITION' => 'Windows SE',
        'CLOUDEDITIONN' => 'Windows SE N',
        'CORE' => 'Windows Home',
        'CORECOUNTRYSPECIFIC' => 'Windows Home China',
        'COREN' => 'Windows Home N',
        'CORESINGLELANGUAGE' => 'Windows Home Single Language',
        'EDUCATION' => 'Windows Education',
        'EDUCATIONN' => 'Windows Education N',
        'ENTERPRISE' => 'Windows Enterprise',
        'ENTERPRISEN' => 'Windows Enterprise N',
        'HOLOGRAPHIC' => 'Windows Holographic',
        'LITE' => 'Windows 10X',
        'PPIPRO' => 'Windows Team',
        'PROFESSIONAL' => 'Windows Pro',
        'PROFESSIONALN' => 'Windows Pro N',
        'SERVERSTANDARD' => 'Windows Server Standard',
        'SERVERSTANDARDCORE' => 'Windows Server Standard, Core',
        'SERVERDATACENTER' => 'Windows Server Datacenter',
        'SERVERDATACENTERCORE' => 'Windows Server Datacenter, Core',
        'SERVERAZURESTACKHCICOR' => 'Azure Stack HCI',
        'SERVERTURBINE' => 'Windows Server Datacenter Azure',
        'SERVERTURBINECOR' => 'Windows Server Datacenter Azure, Core',
        'SERVERSTANDARDACOR' => 'Windows Server Standard SAC',
        'SERVERDATACENTERACOR' => 'Windows Server Datacenter SAC',
        'SERVERARM64' => 'Windows Server ARM64',
    );

    $allEditions = array(
        'ANALOGONECORE',
        'ANDROMEDA',
        'CLOUD',
        'CLOUDE',
        'CLOUDEN',
        'CLOUDN',
        'CORE',
        'CORECOUNTRYSPECIFIC',
        'COREN',
        'CORESINGLELANGUAGE',
        'CORESYSTEMSERVER',
        'EDUCATION',
        'EDUCATIONN',
        'EMBEDDED',
        'EMBEDDEDE',
        'EMBEDDEDEEVAL',
        'EMBEDDEDEVAL',
        'ENTERPRISE',
        'ENTERPRISEEVAL',
        'ENTERPRISEG',
        'ENTERPRISEGN',
        'ENTERPRISEN',
        'ENTERPRISENEVAL',
        'ENTERPRISES',
        'ENTERPRISESEVAL',
        'ENTERPRISESN',
        'ENTERPRISESNEVAL',
        'HOLOGRAPHIC',
        'HUBOS',
        'IOTENTERPRISE',
        'IOTENTERPRISES',
        'IOTOS',
        'IOTUAP',
        'LITE',
        'MOBILECORE',
        'ONECOREUPDATEOS',
        'PPIPRO',
        'PROFESSIONAL',
        'PROFESSIONALCOUNTRYSPECIFIC',
        'PROFESSIONALEDUCATION',
        'PROFESSIONALEDUCATIONN',
        'PROFESSIONALN',
        'PROFESSIONALSINGLELANGUAGE',
        'PROFESSIONALWORKSTATION',
        'PROFESSIONALWORKSTATIONN',
        'SERVERARM64',
        'SERVERARM64CORE',
        'SERVERAZURECOR',
        'SERVERAZURECORCORE',
        'SERVERAZURENANO',
        'SERVERAZURENANOCORE',
        'SERVERCLOUDSTORAGE',
        'SERVERCLOUDSTORAGECORE',
        'SERVERDATACENTER',
        'SERVERDATACENTERACOR',
        'SERVERDATACENTERACORCORE',
        'SERVERDATACENTERCOR',
        'SERVERDATACENTERCORCORE',
        'SERVERDATACENTERCORE',
        'SERVERDATACENTEREVAL',
        'SERVERDATACENTEREVALCOR',
        'SERVERDATACENTEREVALCORCORE',
        'SERVERDATACENTEREVALCORE',
        'SERVERDATACENTERNANO',
        'SERVERDATACENTERNANOCORE',
        'SERVERHYPERCORE',
        'SERVERRDSH',
        'SERVERRDSHCORE',
        'SERVERSOLUTION',
        'SERVERSOLUTIONCORE',
        'SERVERSTANDARD',
        'SERVERSTANDARDACOR',
        'SERVERSTANDARDACORCORE',
        'SERVERSTANDARDCOR',
        'SERVERSTANDARDCORCORE',
        'SERVERSTANDARDCORE',
        'SERVERSTANDARDEVAL',
        'SERVERSTANDARDEVALCOR',
        'SERVERSTANDARDEVALCORCORE',
        'SERVERSTANDARDEVALCORE',
        'SERVERSTANDARDNANO',
        'SERVERSTANDARDNANOCORE',
        'SERVERSTORAGESTANDARD',
        'SERVERSTORAGESTANDARDCORE',
        'SERVERSTORAGESTANDARDEVAL',
        'SERVERSTORAGESTANDARDEVALCORE',
        'SERVERSTORAGEWORKGROUP',
        'SERVERSTORAGEWORKGROUPCORE',
        'SERVERSTORAGEWORKGROUPEVAL',
        'SERVERSTORAGEWORKGROUPEVALCORE',
        'SERVERAZURESTACKHCICOR',
        'SERVERTURBINE',
        'SERVERTURBINECOR',
        'SERVERWEB',
        'SERVERWEBCORE',
        'STARTER',
        'STARTERN',
    );

    return array(
        'fancyEditionNames' => $fancyEditionNames,
        'fancyLangNames' => $fancyLangNames,
        'allEditions' => $allEditions,
    );
}

function uupGetGenPacks($build = 15063, $arch = null, $updateId = null) {
    $internalPacks = dirname(__FILE__).'/packs';

    if(!file_exists($internalPacks.'/metadata.json')) {
        if(!uupCreateInternalPacksMetadata($internalPacks)) {
            return array();
        }
    }

    if(!empty($updateId)) {
        if(file_exists('packs/'.$updateId.'.json.gz')) {
            $genPack = @gzdecode(@file_get_contents('packs/'.$updateId.'.json.gz'));
            if(empty($genPack)) return array();

            $genPack = json_decode($genPack, 1);
            return $genPack;
        }
    }

    $metadata = @file_get_contents($internalPacks.'/metadata.json');
    if(empty($metadata)) {
        return array();
    } else {
        $metadata = json_decode($metadata, 1);
    }

    $hashDetermined = 0;
    $useAllHashesForBuild = 0;

    if($updateId) {
        if(isset($metadata['knownIds'][$updateId])) {
            $hash = $metadata['knownIds'][$updateId];
            $hashDetermined = 1;
        }
    }

    if(!$hashDetermined) {
        foreach($metadata['knownBuilds'] as $buildNum => $val) {
            if($build < $buildNum) continue;
            $useBuild = $buildNum;
            break;
        }

        if(!isset($useBuild)) {
            return array();
        }

        if(!$arch && !isset($metadata['knownBuilds'][$useBuild][$arch])) {
            $genPack = array();
            foreach($metadata['knownBuilds'][$useBuild] as $hash) {
                $temp = @gzdecode(@file_get_contents($internalPacks.'/'.$hash.'.json.gz'));
                if(!empty($temp)) {
                    $temp = json_decode($temp, 1);
                    $genPack = array_merge_recursive($genPack, $temp);
                    unset($temp);
                }
            }
        } elseif(!isset($metadata['knownBuilds'][$useBuild][$arch])) {
            return array();
        } else {
            $hash = $metadata['knownBuilds'][$useBuild][$arch];
        }
    }

    if(!isset($genPack)) {
        $genPack = @gzdecode(@file_get_contents($internalPacks.'/'.$hash.'.json.gz'));
        if(!empty($genPack)) {
            $genPack = json_decode($genPack, 1);
        } else {
            $genPack = array();
        }
    }

    return $genPack;
}

//Function to regenerate internal packs. Should not be used when not needed.
function uupCreateInternalPacksMetadata($internalPacks) {
    $metadataCreationAllowed = 0;
    if(!$metadataCreationAllowed) return false;

    $builds = uupListIds();
    if(isset($ids['error'])) {
        return false;
    }

    $builds = $builds['builds'];

    if(!file_exists('packs')) return false;

    if(!file_exists($internalPacks)) {
        if(!mkdir($internalPacks)) {
            return false;
        }
    } else {
        rmdir($internalPacks);
        mkdir($internalPacks);
    }

    $files = scandir('packs');
    $files = preg_grep('/\.json.gz$/', $files);

    $packs = array();
    foreach($builds as $build) {
        $uuid = $build['uuid'];
        $file = $uuid.'.json.gz';

        if(!file_exists('packs/'.$file)) continue;

        $genPack = @gzdecode(@file_get_contents('packs/'.$file));
        $hash = hash('sha1', $genPack);

        if(!file_exists($internalPacks.'/'.$hash.'.json.gz')) {
            if(!copy('packs/'.$file, $internalPacks.'/'.$hash.'.json.gz')) {
                return false;
            }
        }

        $packs['knownIds'][$uuid] = $hash;

        $buildNum = explode('.', $build['build']);
        $buildNum = $buildNum[0];

        $packs['knownBuilds'][$buildNum][$build['arch']] = $hash;
    }

    file_put_contents($internalPacks.'/metadata.json', json_encode($packs)."\n");
    return true;
}

//Emulation of legacy packs. Do not use in new scripts due to extremely slow process.
function uupGetPacks($build = 15063) {
    $returnArray = uupGetInfoTexts();
    $genPack = uupGetGenPacks($build);

    foreach($genPack as $lang => $editions) {
        $packsForLangs[$lang] = array_keys($editions);
        $packsForLangs[$lang][] = $lang;

        foreach(array_keys($editions) as $edition) {
            foreach($editions[$edition] as $name) {
                $newName = preg_replace('/^cabs_|^metadataesd_|~31bf3856ad364e35/i', '', $name);
                $newName = preg_replace('/~~\.|~\./', '.', $newName);
                $newName = preg_replace('/~/', '-', $newName);
                $newName = strtolower($newName);
                $packs[$lang][$edition][] = $newName;
            }

            $editionPacks[$edition] = $edition;
            $packs[$edition][$edition] = array();
            $skipNeutral[$edition] = 1;
            $skipLangPack[$edition] = 1;
        }
    }

    $returnArray['packs'] = $packs;
    $returnArray['packsForLangs'] = $packsForLangs;
    $returnArray['editionPacks'] = $editionPacks;
    $returnArray['skipNeutral'] = $skipNeutral;
    $returnArray['skipLangPack'] = $skipLangPack;

    return $returnArray;
}
