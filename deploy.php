<?php
/**
 * deploy script
 * @author takashiki
 */

$config = [
    'git_user' => 'username',
    'git_pass' => 'password',
    'projects' => [
        'test' => [
            'password' => 'test@osc',
            'branch' => 'master',
            'web_path' => '/data/wwwroot/test',
        ],
    ],
];

header("Content-type: text/html; charset=utf-8");
if (!isset($_POST['hook'])) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}
$data = json_decode($_POST['hook'], true);
$repository = $data['push_data']['repository'];
if (!array_key_exists($repository['name'], $config['projects']) || $data['password'] != $config['projects'][$repository['name']]['password']) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}
$branch = trim(strrchr($data['push_data']['ref'], '/'), '/');
if ($branch != $config['projects'][$repository['name']]['branch']) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}

$repoUrl = 'https://'.$config['git_user'].':'.urlencode($config['git_pass']).'@'.str_replace('https://', '', $repository['url']);
$descriptorspec = array(
   0 => array("pipe", "r"),
   1 => array("pipe", "w"),
   2 => array("pipe", "w"),
);

$process = proc_open('git pull '.$repoUrl.' '.$branch, $descriptorspec, $pipes, $config['projects'][$repository['name']]['web_path'], NULL);

if(is_resource($process)){
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $output .= stream_get_contents($pipes[2]);
    fclose($pipes[2]);
}
$return_value = proc_close($process);
file_put_contents('deploy.log', $output);
