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
if (isset($options['t']) && intval($options['t']) === 2) {
    $folder = '知识图谱';
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
    $markdown = $res['content'];
    if ($folder == '知识图谱') {
        $markdown = $res['toc'] . PHP_EOL . $markdown;
    }

    file_put_contents($outputFile, $markdown);
}

function parse($children, $depth = 1)
{
    $toc = '';
    $content = '';
    foreach ($children as $k => $r) {
        if ($r['type'] == 'folder') {
            $content .= PHP_EOL . str_repeat('#', $depth + 1) . ' ' . $r['name'] . PHP_EOL . PHP_EOL;
            $res = parse($r['children'], $depth + 1);
            $content .= $res['content'];
            $toc = str_repeat('  ', $depth - 1) . '- [' . $r['name'] . '](#' . $r['name'] . ')' . PHP_EOL;
            $toc .= $res['toc'];
        } else {
            $content .= '- [' . $r['name'] . '](' . $r['url'] . ')' . PHP_EOL;
        }
    }

    return [
        'toc' => $toc,
        'content' => $content,
    ];
}
