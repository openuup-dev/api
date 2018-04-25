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

$packs = array(
    // Base pack
    0 => array(
        'editionNeutral' => array(
            'Microsoft-Windows-Foundation-Package',
            'Microsoft-Windows-Client-Desktop-Required',
            'Microsoft-Windows-Client-Desktop-Required-WOW64-Package',
            'Microsoft-Windows-Client-Desktop-Required-arm64arm-Package',
            'Microsoft-Windows-Client-Features-Package',
            'Microsoft-Windows-Client-Features-WOW64-Package',
            'Microsoft-Windows-Client-Features-arm64arm-Package',
            'Microsoft-Windows-WowPack-CoreARM-arm64arm-Package',
            'Microsoft-Windows-ContactSupport-Package',
            'Microsoft-Windows-Not-Supported-On-LTSB-Package',
            'Microsoft-Windows-Not-Supported-On-LTSB-WOW64-Package',
            'Microsoft-Windows-Not-Supported-On-LTSB-arm64arm-Package',
            'Microsoft-Windows-RegulatedPackages-Package',
            'Microsoft-Windows-RegulatedPackages-WOW64-Package',
            'Microsoft-Windows-RegulatedPackages-arm64arm-Package',
            'Microsoft-Windows-Holographic-Desktop-Merged-Package',
            'Microsoft-Windows-Holographic-Desktop-Merged-WOW64-Package',
            'Microsoft-Windows-Holographic-Desktop-Analog-Package',
            'Microsoft-Windows-QuickAssist-Package',
            'Microsoft-Windows-InternetExplorer-Optional-Package',
            'Microsoft-Windows-MediaPlayer-Package',
            'Microsoft-Windows-Hello-Face-Resource-.-Package',
            'Microsoft-OneCore-ApplicationModel-Sync-Desktop-FOD-Package',
            'Microsoft-Windows-Hello-Face-Migration-Package',
            'Microsoft-Windows-Hello-Face-Package',
            'OpenSSH-Client-Package',
            'Windows10\.0-KB',
        ),
        'CORE' => array(
            'Microsoft-Windows-EditionPack-Core-Package',
            'Microsoft-Windows-EditionPack-Core-WOW64-Package',
            'Microsoft-Windows-EditionPack-Core-arm64arm-Package',
            'Microsoft-Windows-EditionSpecific-Core-Package',
            'Microsoft-Windows-EditionSpecific-Core-WOW64-Package',
            'Microsoft-Windows-EditionSpecific-Core-arm64arm-Package',
            'Microsoft\.ModernApps\.Client\.All',
            'Microsoft\.ModernApps\.Client\.core',
        ),
        'PROFESSIONAL' => array(
            'Microsoft-Windows-EditionPack-Professional-Package',
            'Microsoft-Windows-EditionPack-Professional-WOW64-Package',
            'Microsoft-Windows-EditionPack-Professional-arm64arm-Package',
            'Microsoft-Windows-EditionSpecific-Professional-Package',
            'Microsoft-Windows-EditionSpecific-Professional-WOW64-Package',
            'Microsoft-Windows-EditionSpecific-Professional-arm64arm-Package',
            'Microsoft\.ModernApps\.Client\.All',
            'Microsoft\.ModernApps\.Client\.professional',
        ),
        'CLOUDE' => array(),
    ),

    // European "N" Editions
    1 => array(
        'COREN' => array(
            'Microsoft-Windows-EditionPack-Core-Package',
            'Microsoft-Windows-EditionPack-Core-WOW64-Package',
            'Microsoft-Windows-EditionPack-Core-arm64arm-Package',
            'Microsoft-Windows-EditionSpecific-CoreN-Package',
            'Microsoft-Windows-EditionSpecific-CoreN-WOW64-Package',
            'Microsoft-Windows-EditionSpecific-CoreN-arm64arm-Package',
            'Microsoft\.ModernApps\.ClientN\.All',
        ),
        'PROFESSIONALN' => array(
            'Microsoft-Windows-EditionPack-Professional-Package',
            'Microsoft-Windows-EditionPack-Professional-WOW64-Package',
            'Microsoft-Windows-EditionPack-Professional-arm64arm-Package',
            'Microsoft-Windows-EditionSpecific-ProfessionalN-Package',
            'Microsoft-Windows-EditionSpecific-ProfessionalN-WOW64-Package',
            'Microsoft-Windows-EditionSpecific-ProfessionalN-arm64arm-Package',
            'Microsoft\.ModernApps\.ClientN\.All',
        ),
    ),

    // China specific editions
    2 => array(
        'CORECOUNTRYSPECIFIC' => array(
            'Microsoft-Windows-EditionPack-Core-Package',
            'Microsoft-Windows-EditionPack-Core-WOW64-Package',
            'Microsoft-Windows-EditionPack-Core-arm64arm-Package',
            'Microsoft-Windows-EditionSpecific-CoreCountrySpecific-Package',
            'Microsoft-Windows-EditionSpecific-CoreCountrySpecific-WOW64-Package',
            'Microsoft-Windows-EditionSpecific-CoreCountrySpecific-arm64arm-Package',
            'Microsoft\.ModernApps\.Client\.All',
        ),
    ),

    // Additional packages for some languages
    3 => array(
        'editionNeutral' => array(
            'Microsoft-Windows-LanguageFeatures-Basic-en-us-Package',
            'Microsoft-Windows-LanguageFeatures-OCR-en-us-Package',
        ),
    ),

    // Additional packages for ar-sa language
    4 => array(
        'editionNeutral' => array(
            'Microsoft-Windows-LanguageFeatures-TextToSpeech-ar-eg-Package',
        ),
    ),

    // Additional packages for fr-ca language
    5 => array(
        'editionNeutral' => array(
            'Microsoft-Windows-LanguageFeatures-Basic-fr-fr-Package',
            'Microsoft-Windows-LanguageFeatures-Handwriting-fr-fr-Package',
        ),
    ),

    // Additional packages for zh-tw language
    6 => array(
        'editionNeutral' => array(
            'Microsoft-Windows-LanguageFeatures-Speech-zh-hk-Package',
            'Microsoft-Windows-LanguageFeatures-TextToSpeech-zh-hk-Package',
        ),
    ),
);

$packsForLangs = array(
    'ar-sa' => array(0, 3, 4),
    'bg-bg' => array(0, 1, 3),
    'cs-cz' => array(0, 1),
    'da-dk' => array(0, 1, 3),
    'de-de' => array(0, 1),
    'el-gr' => array(0, 1, 3),
    'en-gb' => array(0, 1),
    'en-us' => array(0, 1),
    'es-es' => array(0, 1),
    'es-mx' => array(0),
    'et-ee' => array(0, 1),
    'fi-fi' => array(0, 1),
    'fr-ca' => array(0, 3, 5),
    'fr-fr' => array(0, 1),
    'he-il' => array(0, 3),
    'hr-hr' => array(0, 1),
    'hu-hu' => array(0, 1),
    'it-it' => array(0, 1),
    'ja-jp' => array(0),
    'ko-kr' => array(0, 1),
    'lt-lt' => array(0, 1),
    'lv-lv' => array(0, 1),
    'nb-no' => array(0, 1),
    'nl-nl' => array(0, 1),
    'pl-pl' => array(0, 1),
    'pt-br' => array(0),
    'pt-pt' => array(0, 1),
    'ro-ro' => array(0, 1),
    'ru-ru' => array(0, 3),
    'sk-sk' => array(0, 1),
    'sl-si' => array(0, 1),
    'sr-latn-rs' => array(0),
    'sv-se' => array(0, 1),
    'th-th' => array(0, 3),
    'tr-tr' => array(0),
    'uk-ua' => array(0, 3),
    'zh-cn' => array(0, 2),
    'zh-tw' => array(0, 6),
);

$editionPacks = array(
    'CLOUDE' => 0,
    'CORE' => 0,
    'CORECOUNTRYSPECIFIC' => 2,
    'COREN' => 1,
    'PROFESSIONAL' => 0,
    'PROFESSIONALN' => 1,
);

$fancyEditionNames = array(
    'CLOUDE' => 'Windows 10 Lean',
    'CORE' => 'Windows 10 Home',
    'CORECOUNTRYSPECIFIC' => 'Windows 10 Home China',
    'COREN' => 'Windows 10 Home N',
    'PROFESSIONAL' => 'Windows 10 Pro',
    'PROFESSIONALN' => 'Windows 10 Pro N',
);

$skipNeutral = array(
    'CLOUDE' => 1,
);

$skipLangPack = array(
    'CLOUDE' => 1,
);
?>
