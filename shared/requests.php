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

// Composes DeviceAttributes parameter needed to fetch data
function composeDeviceAttributes($flight, $ring, $build, $arch, $sku) {
    $branch = branchFromBuild($build);

    if($ring == 'RETAIL') {
        $flightEnabled = 0;
        $isRetail = 1;
    } else {
        $flightEnabled = 1;
        $isRetail = 0;
    }

    $attrib = array(
        'App=WU_OS',
        'AppVer='.$build,
        'AttrDataVer=55',
        'BranchReadinessLevel=CB',
        'CurrentBranch='.$branch,
        'DefaultUserRegion=191',
        'DataVer_RS5='.PHP_INT_MAX,
        'DeviceFamily=Windows.Desktop',
        'FlightContent='.$flight,
        'FlightRing='.$ring,
        'FlightingBranchName=external',
        'Free=32to64',
        'GStatus_RS5=2',
        'InstallDate=1438196400',
        'InstallLanguage=en-US',
        'InstallationType=Client',
        'IsDeviceRetailDemo=0',
        'IsFlightingEnabled='.$flightEnabled,
        'IsRetailOS='.$isRetail,
        'OEMModel=Largehard Device Model 42069',
        'OEMModelBaseBoard=Largehard Base Board',
        'OEMName_Uncleaned=Largehard Corporation',
        'OSArchitecture='.$arch,
        'OSSkuId='.$sku,
        'OSUILocale=en-US',
        'OSVersion='.$build,
        'ProcessorIdentifier=Intel64 Family 6 Model 142 Stepping 9',
        'ProcessorManufacturer=GenuineIntel',
        'TelemetryLevel=3',
        'UpdateManagementGroup=2',
        'UpgEx_RS5=Green',
        'Version_RS5='.PHP_INT_MAX,
        'WuClientVer='.$build,
    );

    return htmlentities('E:'.implode('&', $attrib));
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

        case 17134:
            $branch = 'rs4_release';
            break;

        case 17763:
            $branch = 'rs5_release';
            break;

        default:
            $branch = 'rs_prerelease';
            break;
    }

    return $branch;
}

// Composes POST data for gathering list of urls for download
function composeFileGetRequest($updateId, $device, $info, $rev = 1) {
    $uuid = genUUID();

    $createdTime = time();
    $expiresTime = $createdTime + 120;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);

    $branch = branchFromBuild($info['checkBuild']);

    $deviceAttributes = composeDeviceAttributes(
        $info['flight'],
        $info['ring'],
        $info['checkBuild'],
        $info['arch'],
        $info['sku']
    );

    return <<<XML
<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Header>
        <a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/GetExtendedUpdateInfo2</a:Action>
        <a:MessageID>urn:uuid:$uuid</a:MessageID>
        <a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx/secured</a:To>
        <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <Created>$created</Created>
                <Expires>$expires</Expires>
            </Timestamp>
            <wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization">
                <TicketType Name="MSA" Version="1.0" Policy="MBI_SSL">
                    <Device>$device</Device>
                </TicketType>
            </wuws:WindowsUpdateTicketsToken>
        </o:Security>
    </s:Header>
    <s:Body>
        <GetExtendedUpdateInfo2 xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService">
            <updateIDs>
                <UpdateIdentity>
                    <UpdateID>$updateId</UpdateID>
                    <RevisionNumber>$rev</RevisionNumber>
                </UpdateIdentity>
            </updateIDs>
            <infoTypes>
                <XmlUpdateFragmentType>FileUrl</XmlUpdateFragmentType>
                <XmlUpdateFragmentType>FileDecryption</XmlUpdateFragmentType>
            </infoTypes>
            <deviceAttributes>$deviceAttributes</deviceAttributes>
        </GetExtendedUpdateInfo2>
    </s:Body>
</s:Envelope>
XML;
}

// Composes POST data for fetching the latest update information from Windows Update
function composeFetchUpdRequest($device, $encData, $arch, $flight, $ring, $build, $sku = 48) {
    $uuid = genUUID();

    $createdTime = time();
    $expiresTime = $createdTime + 120;
    $cookieExpiresTime = $createdTime + 604800;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);
    $cookieExpires = gmdate(DATE_W3C, $cookieExpiresTime);

    $branch = branchFromBuild($build);

    $products = array(
        'PN=Client.OS.rs2.'.$arch.'&Branch='.$branch.'&PrimaryOSProduct=1&Repairable=1&V='.$build,
        'PN=Windows.Appraiser.'.$arch.'&Repairable=1&V='.$build,
        'PN=Windows.AppraiserData.'.$arch.'&Repairable=1&V='.$build,
        'PN=Windows.EmergencyUpdate.'.$arch.'&Repairable=1&V='.$build,
        'PN=Windows.OOBE.'.$arch.'&IsWindowsOOBE=1&Repairable=1&V='.$build,
        'PN=Windows.UpdateStackPackage.'.$arch.'&Name=Update Stack Package&Repairable=1&V='.$build,
        'PN=Hammer.'.$arch.'&Source=UpdateOrchestrator&V=0.0.0.0',
        'PN=MSRT.'.$arch.'&Source=UpdateOrchestrator&V=0.0.0.0',
        'PN=SedimentPack.'.$arch.'&Source=UpdateOrchestrator&V=0.0.0.0',
    );

    $callerAttrib = array(
        'Id=UpdateOrchestrator',
        'SheddingAware=1',
        'Interactive=1',
        'IsSeeker=1',
    );

    $products = htmlentities(implode(';', $products));
    $callerAttrib = htmlentities('E:'.implode('&', $callerAttrib));

    $deviceAttributes = composeDeviceAttributes(
        $flight,
        $ring,
        $build,
        $arch,
        $sku
    );

    return <<<XML
<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Header>
        <a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/SyncUpdates</a:Action>
        <a:MessageID>urn:uuid:$uuid</a:MessageID>
        <a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx</a:To>
        <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <Created>$created</Created>
                <Expires>$expires</Expires>
            </Timestamp>
            <wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization">
                <TicketType Name="MSA" Version="1.0" Policy="MBI_SSL">
                    <Device>$device</Device>
                </TicketType>
            </wuws:WindowsUpdateTicketsToken>
        </o:Security>
    </s:Header>
    <s:Body>
        <SyncUpdates xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService">
            <cookie>
                <Expiration>$cookieExpires</Expiration>
                <EncryptedData>$encData</EncryptedData>
            </cookie>
            <parameters>
                <ExpressQuery>false</ExpressQuery>
                <InstalledNonLeafUpdateIDs/>
                <OtherCachedUpdateIDs/>
                <SkipSoftwareSync>false</SkipSoftwareSync>
                <NeedTwoGroupOutOfScopeUpdates>true</NeedTwoGroupOutOfScopeUpdates>
                <AlsoPerformRegularSync>true</AlsoPerformRegularSync>
                <ComputerSpec/>
                <ExtendedUpdateInfoParameters>
                    <XmlUpdateFragmentTypes>
                        <XmlUpdateFragmentType>Extended</XmlUpdateFragmentType>
                        <XmlUpdateFragmentType>LocalizedProperties</XmlUpdateFragmentType>
                        <XmlUpdateFragmentType>Eula</XmlUpdateFragmentType>
                    </XmlUpdateFragmentTypes>
                    <Locales>
                        <string>en-US</string>
                    </Locales>
                </ExtendedUpdateInfoParameters>
                <ClientPreferredLanguages/>
                <ProductsParameters>
                    <SyncCurrentVersionOnly>false</SyncCurrentVersionOnly>
                    <DeviceAttributes>$deviceAttributes</DeviceAttributes>
                    <CallerAttributes>$callerAttrib</CallerAttributes>
                    <Products>$products</Products>
                </ProductsParameters>
            </parameters>
        </SyncUpdates>
    </s:Body>
</s:Envelope>
XML;
}

// Composes POST data for Get Cookie request
function composeGetCookieRequest($device) {
    $uuid = genUUID();

    $createdTime = time();
    $expiresTime = $createdTime + 120;

    $created = gmdate(DATE_W3C, $createdTime);
    $expires = gmdate(DATE_W3C, $expiresTime);

    return <<<XML
<s:Envelope xmlns:a="http://www.w3.org/2005/08/addressing" xmlns:s="http://www.w3.org/2003/05/soap-envelope">
    <s:Header>
        <a:Action s:mustUnderstand="1">http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService/GetCookie</a:Action>
        <a:MessageID>urn:uuid:$uuid</a:MessageID>
        <a:To s:mustUnderstand="1">https://fe3.delivery.mp.microsoft.com/ClientWebService/client.asmx</a:To>
        <o:Security s:mustUnderstand="1" xmlns:o="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <Timestamp xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <Created>$created</Created>
                <Expires>$expires</Expires>
            </Timestamp>
            <wuws:WindowsUpdateTicketsToken wsu:id="ClientMSA" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:wuws="http://schemas.microsoft.com/msus/2014/10/WindowsUpdateAuthorization">
                <TicketType Name="MSA" Version="1.0" Policy="MBI_SSL">
                    <Device>$device</Device>
                </TicketType>
            </wuws:WindowsUpdateTicketsToken>
        </o:Security>
    </s:Header>
    <s:Body>
        <GetCookie xmlns="http://www.microsoft.com/SoftwareDistribution/Server/ClientWebService">
            <oldCookie>
                <Expiration>$created</Expiration>
            </oldCookie>
            <lastChange>$created</lastChange>
            <currentTime>$created</currentTime>
            <protocolVersion>2.0</protocolVersion>
        </GetCookie>
    </s:Body>
</s:Envelope>
XML;
}
?>
