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

require_once 'shared/main.php';

function uupListIds() {
    uupApiPrintBrand();

    if(!file_exists('fileinfo')) return array('error' => 'NO_FILEINFO_DIR');

    $files = scandir('fileinfo');
    $files = preg_grep('/\.json$/', $files);

    consoleLogger('Parsing database info...');
    $builds = array();
    foreach($files as $file) {
        if($file == '.' || $file == '..') continue;

        $info = @file_get_contents('fileinfo/'.$file);
        $info = json_decode($info, true);

        $uuid = preg_replace('/\.json$/', '', $file);

        $title = isset($info['title']) ? $info['title'] : 'UNKNOWN';
        $build = isset($info['build']) ? $info['build'] : 'UNKNOWN';
        $arch = isset($info['arch']) ? $info['arch'] : 'UNKNOWN';

        $temp = array(
            'title' => $title,
            'build' => $build,
            'arch' => $arch,
            'uuid' => $uuid,
        );

        $builds = array_merge($builds, array($build.$arch.$title.$uuid => $temp));
    }

    krsort($builds);

    $buildsNew = array();
    foreach($builds as $val) {
        $buildsNew = array_merge($buildsNew, array($val));
    }

    $builds = $buildsNew;

    consoleLogger('Done parsing database info.');

    return array(
        'apiVersion' => uupApiVersion(),
        'builds' => $builds,
    );
}
?>
