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

function uupGetPacks($build = 15063) {
    $fancyLangNames = array(
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
        'CLOUD' => 'Windows 10 S',
        'CLOUDN' => 'Windows 10 S N',
        'CLOUDE' => 'Windows 10 CloudE',
        'CORE' => 'Windows 10 Home',
        'CORECOUNTRYSPECIFIC' => 'Windows 10 Home China',
        'COREN' => 'Windows 10 Home N',
        'CORESINGLELANGUAGE' => 'Windows 10 Home Single Language',
        'EDUCATION' => 'Windows 10 Education',
        'EDUCATIONN' => 'Windows 10 Education N',
        'ENTERPRISE' => 'Windows 10 Enterprise',
        'ENTERPRISEN' => 'Windows 10 Enterprise N',
        'PPIPRO' => 'Windows 10 Team',
        'PROFESSIONAL' => 'Windows 10 Pro',
        'PROFESSIONALN' => 'Windows 10 Pro N',
    );

    $allEditions = array(
        'ANALOGONECORE',
        'ANDROMEDA',
        'CLOUD',
        'CLOUDE',
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
        'IOTUAP',
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
        'SERVERWEB',
        'SERVERWEBCORE',
        'STARTER',
        'STARTERN',
    );

    if($build < 17063) {
        require dirname(__FILE__).'/packs/legacy.php';
    } elseif ($build >= 17661) {
        require dirname(__FILE__).'/packs/17661.php';
    } elseif ($build >= 17655) {
        require dirname(__FILE__).'/packs/17655.php';
    } elseif ($build >= 17650) {
        require dirname(__FILE__).'/packs/17650.php';
    } elseif ($build >= 17634) {
        require dirname(__FILE__).'/packs/17634.php';
    } elseif ($build >= 17623) {
        require dirname(__FILE__).'/packs/17623.php';
    } elseif ($build >= 17093) {
        require dirname(__FILE__).'/packs/17093.php';
    } elseif ($build >= 17063) {
        require dirname(__FILE__).'/packs/17063.php';
    }

    return array(
        'packs' => $packs,
        'packsForLangs' => $packsForLangs,
        'editionPacks' => $editionPacks,
        'fancyEditionNames' => $fancyEditionNames,
        'fancyLangNames' => $fancyLangNames,
        'allEditions' => $allEditions,
        'skipNeutral' => $skipNeutral,
        'skipLangPack' => $skipLangPack,
    );
}
?>
