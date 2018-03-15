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

// Composes DeviceAttributes parameter needed to fetch data
function composeDeviceAttributes($flight, $ring, $build, $arch) {
    $branch = branchFromBuild($build);

    if($ring == 'RETAIL') {
        $flightEnabled = 0;
    } else {
        $flightEnabled = 1;
    }

    $attrib = array(
        'App=WU',
        'AppVer='.$build,
        'AttrDataVer=38',
        'BranchReadinessLevel=CB',
        'CurrentBranch='.$branch,
        'DeviceFamily=Windows.Desktop',
        'FirmwareVersion=6.00',
        'FlightContent='.$flight,
        'FlightingBranchName=external',
        'FlightRing='.$ring,
        'Free=32to64',
        'GStatus_RS3=2',
        'GStatus_RS4=2',
        'InstallationType=Client',
        'InstallLanguage=en-US',
        'IsDeviceRetailDemo=0',
        'IsFlightingEnabled='.$flightEnabled,
        'OEMModel=Microsoft',
        'OEMName_Uncleaned=Microsoft',
        'OSArchitecture='.$arch,
        'OSSkuId=48',
        'OSUILocale=en-US',
        'OSVersion='.$build,
        'PonchAllow=1',
        'ProcessorIdentifier=Intel64 Family 6 Model 142 Stepping 9',
        'ProcessorManufacturer=GenuineIntel',
        'TelemetryLevel=1',
        'UpdateManagementGroup=2',
        'UpgEx_RS3=Green',
        'UpgEx_RS4=Green',
        'WuClientVer='.$build,
    );

    return implode(';', $attrib);
}

// Returns the most possible branch for selected build
function branchFromBuild($build) {
    $build = explode('.', $build);
    $build = $build[2];

    switch($build) {
        case 15063:
            $branch = 'rs2_release';
            break;

        case 16299:
            $branch = 'rs3_release';
            break;

        default:
            $branch = 'rs_prerelease';
            break;
    }

    return $branch;
}

// Composes POST data for gathering list of urls for download
function composeFileGetRequest($updateId, $device, $info, $rev = 1) {
    $uuid = randStr(8).'-'.randStr(4).'-'.randStr(4).'-'.randStr(4).'-'.randStr(12);

    $createdTime = time();
    $expiresTime = $createdTime + 120;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);

    $branch = branchFromBuild($info['checkBuild']);

    $deviceAttributes = composeDeviceAttributes(
        $info['flight'],
        $info['ring'],
        $info['checkBuild'],
        $info['arch']
    );

    return '<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope"><s:Header><a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/GetExtendedUpdateInfo2</a:Action><a:MessageID>urn:uuid:'.$uuid.'</a:MessageID><a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured</a:To><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><Created>'.$created.'</Created><Expires>'.$expires.'</Expires></Timestamp><wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization"><TicketType Name="MSA" Version="1.0" Policy="MBI_SSL"><Device>'.$device.'</Device></TicketType></wuws:WindowsUpdateTicketsToken></o:Security></s:Header><s:Body><GetExtendedUpdateInfo2 xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService"><updateIDs><UpdateIdentity><UpdateID>'.$updateId.'</UpdateID><RevisionNumber>'.$rev.'</RevisionNumber></UpdateIdentity></updateIDs><infoTypes><XmlUpdateFragmentType>FileUrl</XmlUpdateFragmentType><XmlUpdateFragmentType>FileDecryption</XmlUpdateFragmentType></infoTypes><deviceAttributes>'.$deviceAttributes.'</deviceAttributes></GetExtendedUpdateInfo2></s:Body></s:Envelope>';
}

// Composes POST data for fetching the latest update information from Windows Update
function composeFetchUpdRequest($device, $encData, $arch, $flight, $ring, $build) {
    $uuid = randStr(8).'-'.randStr(4).'-'.randStr(4).'-'.randStr(4).'-'.randStr(12);

    $createdTime = time();
    $expiresTime = $createdTime + 120;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);

    $branch = branchFromBuild($build);

    $products = array(
        'Branch='.$branch,
        'PN=Client.OS.rs2.'.$arch,
        'PrimaryOSProduct=1',
        'V='.$build,
    );

    $callerAttrib = array(
        'Id=UpdateOrchestrator',
        'Interactive=1',
        'IsSeeker=1',
    );

    $products = implode('&amp;', $products);
    $callerAttrib = 'E:'.implode('&amp;', $callerAttrib);

    $deviceAttributes = composeDeviceAttributes(
        $flight,
        $ring,
        $build,
        $arch
    );

    return '<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope"><s:Header><a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/SyncUpdates</a:Action><a:MessageID>urn:uuid:'.$uuid.'</a:MessageID><a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx</a:To><o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd"><Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd"><Created>'.$created.'</Created><Expires>'.$expires.'</Expires></Timestamp><wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization"><TicketType Name="MSA" Version="1.0" Policy="MBI_SSL"><Device>'.$device.'</Device></TicketType></wuws:WindowsUpdateTicketsToken></o:Security></s:Header><s:Body><SyncUpdates xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService"><cookie><Expiration>2045-04-07T12:38:34Z</Expiration><EncryptedData>'.$encData.'</EncryptedData></cookie><parameters><ExpressQuery>false</ExpressQuery><InstalledNonLeafUpdateIDs></InstalledNonLeafUpdateIDs><OtherCachedUpdateIDs></OtherCachedUpdateIDs><SkipSoftwareSync>false</SkipSoftwareSync><NeedTwoGroupOutOfScopeUpdates>true</NeedTwoGroupOutOfScopeUpdates><AlsoPerformRegularSync>true</AlsoPerformRegularSync><ComputerSpec/><ExtendedUpdateInfoParameters><XmlUpdateFragmentTypes><XmlUpdateFragmentType>Extended</XmlUpdateFragmentType><XmlUpdateFragmentType>LocalizedProperties</XmlUpdateFragmentType><XmlUpdateFragmentType>Eula</XmlUpdateFragmentType></XmlUpdateFragmentTypes><Locales><string>en-US</string></Locales></ExtendedUpdateInfoParameters><ClientPreferredLanguages></ClientPreferredLanguages><ProductsParameters><SyncCurrentVersionOnly>false</SyncCurrentVersionOnly><DeviceAttributes>'.$deviceAttributes.'</DeviceAttributes><CallerAttributes>'.$callerAttrib.'</CallerAttributes><Products>'.$products.'</Products></ProductsParameters></parameters></SyncUpdates></s:Body></s:Envelope>';
}
?>
