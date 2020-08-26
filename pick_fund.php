<?php

/**
 * 基金对比选购
 */

$api = 'http://fund.eastmoney.com/Data/FundCompare_Interface.aspx?t=0,1&bzdm=%s';
$pattern = '/var fundinfo_yjpj = ({.+});/';

$codes = $argv[1];
if (empty($codes)) {
    die('need fund codes!');
}

$url = sprintf($api, $codes);
$srcData = file_get_contents($url);
preg_match($pattern, $srcData, $matches);
$basis = convert($matches[1]);

// 这里定义两个类会更好，不过小脚本就简单来吧
$funds = [];
$profits = [];
foreach ($basis['jdsy'] as $key => $fund) {
    $info = explode(',', $fund);
    $funds[$info[0]] = [
        'code' => $info[0],
        'name' => $info[1],
        'date' => $info[2],
    ];
    $annual = $basis['lsndsy'][$key];
    
    $profits['thisYear'][$info[0]] = parseNum($info[3]);
    $profits['latestWeek'][$info[0]] = parseNum($info[4]);
    $profits['latestMonth'][$info[0]] = parseNum($info[5]);
    $profits['latestThreeMonth'][$info[0]] = parseNum($info[6]);
    $profits['latestSixMonth'][$info[0]] = parseNum($info[7]);
    $profits['latestYear'][$info[0]] = parseNum($info[8]);
/*     $profits['latestTwoYear'][$info[0]] = parseNum($info[9]);
    $profits['latestThreeYear'][$info[0]] = parseNum($info[10]);
    $profits['latestFiveYear'][$info[0]] = parseNum($info[11]); */
    $profits['year2019'][$info[0]] = parseNum($annual[2019] ?? 0);
/*     $profits['year2018'][$info[0]] = parseNum($annual[2018] ?? 0);
    $profits['year2017'][$info[0]] = parseNum($annual[2017] ?? 0);
    $profits['year2016'][$info[0]] = parseNum($annual[2016] ?? 0);
    $profits['year2015'][$info[0]] = parseNum($annual[2015] ?? 0); */
}

$rank = [];
$rankNum = range(1, count($funds));
foreach ($profits as $tensor => $values) {
    arsort($values);
    $rank[$tensor] = array_combine(array_keys($values), $rankNum);
}

$scores = [];
foreach ($funds as $code => $fund) {
    $scores['top1'][$code] = 0;
    $scores['top5'][$code] = 0;
    foreach ($rank as $tensor => $list) {
        if ($list[$code] <= 5) {
            $scores['top5'][$code] += 1;
        }

        if ($list[$code] == 1) {
            $scores['top1'][$code] += 1;
        }
    }
}

foreach ($scores as $tensor => $values) {
    arsort($values);
    $values = array_filter($values);
    var_dump($values);
}

function parseNum($str)
{
    return floatval(rtrim($str, '%'));
}

function convert($str)
{
    $search = array("jdsy", "lsndsy", "dtsy", "jjpj");
    $replace = array('"jdsy"', '"lsndsy"', '"dtsy"', '"jjpj"');
    $str = str_replace($search, $replace, $str);
    
    return json_decode($str, true);
}
