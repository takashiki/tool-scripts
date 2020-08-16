<?php

/**
 * 这个脚本是我自己用来将 Chrome 书签转换成阅读周刊和知识图谱的
 */

$options = getopt('f:t:o');

$bookmarkFile = $options['f'] ?? null;
if (empty($bookmarkFile)) {
    die('Must set file path!');
}

// t 参数取值 1：阅读周刊，2：知识图谱
$folder = '阅读周刊';
$doClear = true;
if (isset($options['t']) && intval($options['t']) === 2) {
    $folder = '知识图谱';
    $doClear = false;
}

$outputFile = $options['o'] ?? 'output.md';

$srcJson = file_get_contents($bookmarkFile);
$srcData = json_decode($srcJson, true);

$bookmarkGroups = &$srcData['roots']['bookmark_bar']['children'];
foreach ($bookmarkGroups as $k => $group) {
    if ($group['name'] != $folder) {
        continue;
    }

    $res = parse($group['children']);
    file_put_contents($outputFile, $res['markdown']);
    if ($doClear) {
        $bookmarkGroups[$k]['children'] = $res['trimArr'];
        file_put_contents($bookmarkFile, json_encode($srcData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}

function parse($children, $depth = 1)
{
    $markdown = '';
    foreach ($children as $k => $r) {
        if ($r['type'] == 'folder') {
            $markdown .= str_repeat('#', $depth + 1) . ' ' . $r['name'] . PHP_EOL . PHP_EOL;
            $res = parse($r['children'], $depth + 1);
            $markdown .= $res['markdown'];
            $children[$k]['children'] = $res['trimArr'];
        } else {
            $markdown .= '- [' . $r['name'] . '](' . $r['url'] . ')' . PHP_EOL;
            unset($children[$k]);
        }
    }

    return [
        'markdown' => $markdown,
        'trimArr' => $children,
    ];
}
