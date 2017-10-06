UUP dump API
------------

### Functions
#### fetchupd.php: `uupFetchUpd($arch, $ring, $flight, $build);`
Fetches latest update information from Windows Update servers.

Parameters:
 - `arch` - Architecture of build to find
   - **Supported values:** `amd64`, `arm64`, `x86`

 - `ring` - Ring to use when fetching information
   - **Supported values:** `WIF`, `WIS`, `RP`

 - `flight` - Flight to use when fetching information
   - **Supported values:** `Active`, `Skip`, `Current`
   - **NOTE:** `Skip` is for `WIF` ring only. `Current` is for `RP` ring only.

 - `build` - Build number to use when fetching information
   - **Supported values:** > 15063 and < 65536

#### get.php: `uupGetFiles($updateId, $usePack, $desiredEdition);`
Fetches files from `updateId` update and parses to array.

Parameters:
 - `updateId` - Update identifier
   - **Supported values:** any update UUID

 - `usePack` - Generate list of files for selected language
   - **Supported values:** language name in xx-xx format

 - `desiredEdition` - Generate list of files for selected edition
   - **Supported values:** any update UUID
   - **NOTE:** You need to specify `usePack` to get successful request

#### listeditions.php: `uupListEditions($lang);`
Outputs list of supported editions for selected language.

Parameters:
 - `lang` - Generate list for selected language
   - **Supported values:** language name in xx-xx format

#### listid.php: `uupListIds();`
Outputs list of updates in fileinfo database.

Parameters:
 - None

#### listlangs.php: `uupListLangs();`
Outputs list of languages supported by project.

Parameters:
 - None

#### updateinfo.php: `uupUpdateInfo($updateId, $onlyInfo);`
Outputs specified information of specified `updateId`.

Parameters:
 - `updateId` - Update identifier
   - **Supported values:** any update UUID

 - `onlyInfo` - Key to output
   - **Supported values:** any string

#### shared/main.php: `uupApiVersion();`
Returns version of the API.

Parameters:
 - None

### Error codes thrown by API
**fetchupd.php**
 - UNKNOWN_ARCH
 - UNKNOWN_RING
 - UNKNOWN_FLIGHT
 - UNKNOWN_COMBINATION
 - ILLEGAL_BUILD
 - EMPTY_FILELIST

**get.php**
 - UNSUPPORTED_LANG
 - UNSPECIFIED_LANG
 - UNSUPPORTED_EDITION
 - UNSUPPORTED_COMBINATION
 - EMPTY_FILELIST

**listeditions.php**
 - UNSUPPORTED_LANG

**listid.php**
 - NO_FILEINFO_DIR

**updateinfo.php**
 - UPDATE_INFORMATION_NOT_EXISTS
 - KEY_NOT_EXISTS
