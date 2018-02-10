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

function uupListIds($search = null) {
    uupApiPrintBrand();

    if(!file_exists('fileinfo')) return array('error' => 'NO_FILEINFO_DIR');

    $files = scandir('fileinfo');
    $files = preg_grep('/\.json$/', $files);

    consoleLogger('Parsing database info...');

    $database = @file_get_contents('cache/fileinfo.json');
    $database = json_decode($database, true);
    if(empty($database)) $database = array();

    $newDb = array();
    $builds = array();
    foreach($files as $file) {
        if($file == '.' || $file == '..') continue;
        $uuid = preg_replace('/\.json$/', '', $file);

        if(!isset($database[$uuid])) {
            $info = @file_get_contents('fileinfo/'.$file);
            $info = json_decode($info, true);

            $title = isset($info['title']) ? $info['title'] : 'UNKNOWN';
            $build = isset($info['build']) ? $info['build'] : 'UNKNOWN';
            $arch = isset($info['arch']) ? $info['arch'] : 'UNKNOWN';

            $temp = array(
                'title' => $title,
                'build' => $build,
                'arch' => $arch,
            );

            $newDb[$uuid] = $temp;
        } else {
            $title = $database[$uuid]['title'];
            $build = $database[$uuid]['build'];
            $arch = $database[$uuid]['arch'];

            $newDb[$uuid] = $database[$uuid];
        }

        $temp = array(
            'title' => $title,
            'build' => $build,
            'arch' => $arch,
            'uuid' => $uuid,
        );

        $tmp = explode('.', $build);
        $tmp[0] = str_pad($tmp[0], 10, '0', STR_PAD_LEFT);
        $tmp[1] = str_pad($tmp[1], 10, '0', STR_PAD_LEFT);
        $tmp = $tmp[0].$tmp[1];

        $buildAssoc[$tmp][] = $arch.$title.$uuid;
        $builds[$tmp.$arch.$title.$uuid] = $temp;
    }

    krsort($buildAssoc);
    $buildsNew = array();

    foreach($buildAssoc as $key => $val) {
        sort($val);
        foreach($val as $id) {
            $buildsNew[] = $builds[$key.$id];
        }
    }

    $builds = $buildsNew;
    consoleLogger('Done parsing database info.');

    if($newDb != $database) {
        if(!file_exists('cache')) mkdir('cache');

        $success = @file_put_contents(
            'cache/fileinfo.json',
            json_encode($newDb)."\n"
        );

        if(!$success) consoleLogger('Failed to update database cache.');
    }

    if($search) {
        $searchSafe = preg_quote($search, '/');
        if(preg_match('/^".*"$/', $searchSafe)) {
            $searchSafe = preg_replace('/^"|"$/', '', $searchSafe);
        } else {
            $searchSafe = str_replace(' ', '.*', $searchSafe);
        }

        foreach($builds as $key => $val) {
            $buildString[$key] = $val['title'].' '.$val['build'].' '.$val['arch'];
        }

        $remove = preg_grep('/.*'.$searchSafe.'.*/i', $buildString, PREG_GREP_INVERT);
        $removeKeys = array_keys($remove);

        foreach($removeKeys as $value) {
            unset($builds[$value]);
        }

        if(empty($builds)) {
            return array('error' => 'SEARCH_NO_RESULTS');
        }

        unset($remove, $removeKeys, $buildString);
    }

    return array(
        'apiVersion' => uupApiVersion(),
        'builds' => $builds,
    );
}
?>
