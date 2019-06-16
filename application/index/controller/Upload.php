<?php

namespace app\index\controller;

use ErrorException;
use Exception;
use think\Controller;

class Upload extends Controller
{

    public function index()
    {
        return $this->fetch();
    }

    //上传文件以及获取所有报告
    public function uoploadfile()
    {
        // 获取表单上传文件
        $file = request()->file('file');
        if (empty($file)) {
            return alert_error('未检测到文件，请重新上传');
        }

        // 移动到框架应用根目录/public/uploads/ 目录下
        $time1 = date('Ymd', time());
        $time2 = date('YmdHis', time());
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $time1, $time2 . '_' . $file->getInfo()['name']);

        $dir = 'uploads/' . $time1;
        $filename = $time2 . '_' . $file->getInfo()['name'];

        if ($info) {
            $data = $this->getReport($dir, $filename);
            if ($data['code'] == 0) {
                $report = $data['url'];
                if (empty($report)) {
                    return alert_error('生成报告失败, 请重新上传工程文件！');
                }
                $this->assign('report', 'report.pdf');

                list($result1,$result2) = $this->getTreeInfo($report);
                $this->assign('allcomps',$result1);
                $this->assign('comps', json_encode($result2));
//                $com_info = $this->restspid($result1[9]["gav"]);
//                var_dump($com_info);
//                return json($com_info);
                return $this->fetch('report/index');
            }
        } else {
            // 上传失败获取错误信息
            return alert_error($file->getError());
        }
    }

    //获取组件依赖报告
    public function getReport($d, $filename)
    {
        $dir = $d;
        $drep = $d . "/result";
        $shdir = "/home/wwwroot/mohs/application/index/controller/usesh";
        $name = "$filename";
        $report = explode(".", $name);
        system("sudo bash" . " " . $shdir . "/ms.sh" . " " . $dir . " " . $name . " " . "2>&1", $result);
        if ($result == 0) {
            $data = array(
                'code' => 0,
                'url' => $drep . "/" . $report[0] .".txt"
            );
            return $data;
        } else {
            $data = array(
                'code' => 1
            );
            return $data;
        }
    }


    //获取树形结构信息
    public function getTreeInfo($file){
        $handle = fopen($file, "r");//读取文件
        $tmp = array();
        $result = array();
        //通过filesize获得文件大小，将整个文件一下子读到一个字符串中
        if(filesize($file) > 0){
            $contents = fread($handle, filesize($file));
        }
        fclose($handle);
        $contents = explode("[INFO]", $contents);
        array_shift($contents);
        array_shift($contents);
        $img = '/static/assets2/images/layers.png';
        $count = 0;
        foreach($contents as $line){
            $line_arr = explode('"',$line);
            $parent = explode(':',$line_arr[1]);
            $parent_info = $parent[0].":".$parent[1].":".$parent[3];
            $child = explode(':',$line_arr[3]);
            $child_info = $child[0].":".$child[1].":".$child[3];
            $node = array(
                'code' => $count,
                'id' => $child_info,
                'pid' => $parent_info,
                'text' => $child_info,
                'tags' => [0],
                'image' => $img,
                'gav' => explode(':',$child_info)
            );
            $tmp[] = $node;
            $count++;
        }
        $result['info'] =  $this->getTree($tmp);
        $result['count'] = $count;
        return array($tmp,$result);
    }


    //根据一位数组生成树形结构
    public function getTree($items,$pid ="pid") {
        $map  = [];
        $tree = [];
        foreach ($items as &$it){ $map[$it['id']] = &$it; }  //数据的ID名生成新的引用索引树
        foreach ($items as &$it){
            $parent = &$map[$it[$pid]];
            if($parent) {
                $parent['nodes'][] = &$it;
                $parent['tags'][0] += 1;
            }else{
                $tree[] = &$it;
            }
        }
        foreach ($items as &$it){
            $it['tags'][0] = (string)$it['tags'][0];
        }
        return $tree;
    }

    function geturl($info, $flag=0){
        $res = "";
        $arr = explode(":", $info);
        if(is_array($arr) && count($arr)==3){
            $res .= "https://mvnrepository.com/artifact/".$arr[0].'/'.$arr[1];
            if($flag == 1){
                $res .= '/'.$arr[2];
            }
        }
        return $res;
    }

    //获取组件的license详情
    public function getLicense(){
        $info = $_POST['info'];

        $ch = curl_init();
        $res = array();
        if($info == ''){
            return array();
        }
        $url = $this->geturl($info, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_URL, "https://mvnrepository.com/artifact/junit/junit/4.11");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $html = curl_exec($ch);
        curl_close($ch);
        $html = strstr($html, '<span class="b lic">');
        $pos = strpos($html, '</td>');
        $html = substr($html, 0 , $pos);
        $info = explode('</span>', $html);
        if(is_array($info) && !empty($info)){
            foreach($info as $k => $v){
                $pos = strpos($info[$k], '>');
                $info[$k] = substr($info[$k], $pos+1);
            }
            array_pop($info);
        }
        return $info;
    }

    //获取组件详情，包括热度
    public function restspid(){
        $info = $_POST['info'];

        $ch = curl_init();
        $res = array();
        if($info == ''){
            return array();
        }
        $url = $this->geturl($info);
        //curl_setopt($ch, CURLOPT_URL, "https://mvnrepository.com/artifact/junit/junit");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $html = curl_exec($ch);
        curl_close($ch);
        $html = strstr($html, '<table class="grid versions" width="100%">');
        $pos = strpos($html, '</table>');
        $html = substr($html, 0, $pos+8);
        $info = explode('<tbody>', $html);
        $j = 0;
        $max = 0;
        $maxi = 0;
        if(is_array($info) && !empty($info)){
            array_splice ($info, 0, 1);
            foreach($info as $k => $v){
                $info[$k] = explode('<tr>', $info[$k]);
                if(is_array($info[$k]) && !empty($info[$k])){
                    foreach($info[$k] as $key => $val){
                        $info[$k][$key] = explode('<td>',$info[$k][$key]);
                        if(is_array($info[$k][$key]) && !empty($info[$k][$key])){
                            if(count($info[$k][$key]) == 5){
                                $t = strstr($info[$k][$key][1], '>');
                                $pos = strpos($t, '</');
                                $t = substr($t, 1, $pos-1);
                                $res['info'][$j]['version'] = $t;
                                $t = strstr($info[$k][$key][2], '>');
                                $pos = strpos($t, '</');
                                $t = substr($t, 1, $pos-1);
                                $res['info'][$j]['Repository'] = $t;
                                $t = strstr($info[$k][$key][3], '>');
                                $pos = strpos($t, '</');
                                $t = substr($t, 1, $pos-1);
                                if($t == ''){
                                    $t = '0';
                                }
                                if(intval(str_replace(",","",$t)) > $max){
                                    $max = intval(str_replace(",","",$t));
                                    $maxi = $j;
                                }
                                $res['info'][$j]['Usages'] = $t;
                                $pos = strpos($info[$k][$key][4], '</');
                                $t = substr($info[$k][$key][4], 0, $pos);
                                $res['info'][$j]['Date'] = $t;
                                $j ++;
                            }
                        }
                    }
                }
            }
        }
        $res['count'] = $j;
        $res['latest'] = $res['info'][0]['version'];
        $res['most'] = $res['info'][$maxi]['version'];
        //echo json_encode($res);
        return $res;
    }
}
