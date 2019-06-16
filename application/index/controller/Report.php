<?php

namespace app\index\controller;

use think\Controller;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class Report extends Controller
{
    public function index()
    {
        if (!session('?report')) {
            $this->redirect('upload/index');
        }
        return $this->fetch();
    }

    public function download($file_dir)
    {
        $this->getAllData($file_dir);
    }

    public function getAllData($dataUrl)
    {
        $contrller = controller('Upload');
        list($data, $treedata) = $contrller->getTreeInfo($dataUrl);
        //dump($data);
        $PHPWord = new PhpWord();
        $section = $PHPWord->createSection();
        $title = "MOHS maven 工程健康扫描报告";
        $section->addText($title, 'rStyle', 'pStyle');
        $section->addTextBreak(2);
        //向word中写入组件信息
        $section->addText('项目所有组件信息', 'rStyle', 'pStyle');
        $section->addTextBreak(2);
        //打开文件写入内容
        $file = fopen($dataUrl, "r");
        $user = array();
        $i = 0;
        while (!feof($file)) {
            $user[$i] = fgets($file);//fgets()函数从文件指针中读取一行
            $i++;
        }
        fclose($file);
        $user = array_filter($user);

        foreach ($user as $line) {
            $section->addText("[INFO] ------------------< demo.mr:mapreduce-serializebean >-------------------", 'rStyle', 'pStyle');
            break;
        }
        $section->addTextBreak(2);
        //文件内容写入结束

        //$this->MavenFileToWord($dataUrl,$section);
        //word中表格对应的样式
        $styleTable = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80);
        $fontStyle = array('bold' => true, 'align' => 'center');
        $PHPWord->addTableStyle('myOwnTableStyle', $styleTable);
        $styleTable2 = array('borderColor' => '006699', 'borderLeftSize' => 6, 'borderRightSize' => 6, 'cellMargin' => 80);
        $fontStyle2 = array('align' => 'center');
        $PHPWord->addTableStyle('myOwnTableStyle2', $styleTable2);
        //将组建的热度信息写入word文档
//        $section->addText('项目所有组件热度信息', 'rStyle', 'pStyle');
//        $section->addTextBreak(2);
//        foreach ($data as $comp){
//            $parm = $comp['gav'][0].':'. $comp['gav'][1].':'.$comp['gav'][2];
//            //获取单个热度信息数据
//            $redu =  $this->restspid($parm);
//            //将全部热度信息写入word
//            $table2 = $section->addTable('myOwnTableStyle2');
//            $table2->addRow();
//            $table2->addCell(10000)->addText($parm,$fontStyle);
//            $lastVersion = $redu['latest'];
//            $mostVersion = $redu['most'];
//            $table2->addCell(5000)->addText("最新版本",$fontStyle);
//            $table2->addCell(5000)->addText($lastVersion,$fontStyle);
//            $table2->addCell(5000)->addText("最热版本",$fontStyle);
//            $table2->addCell(5000)->addText($mostVersion,$fontStyle);
//            foreach ($redu['info'] as $re){
//                $version = $re['version'];
//                $uses = $re['Usages'];
//                $table3 = $section->addTable('myOwnTableStyle');
//                $table3->addRow();
//                $table3->addCell(2000)->addText("版本",$fontStyle2);
//                $table3->addCell(3000)->addText($version,$fontStyle2);
//                $table3->addCell(2000)->addText("热度(使用数量)",$fontStyle2);
//                $table3->addCell(3000)->addText($uses,$fontStyle2);
//            }
//            $section->addTextBreak(2);
//        }

        //将所有证书信息写入word
//        $section->addText('项目全部证书信息', 'rStyle', 'pStyle');
//        $table1 = $section->addTable('myOwnTableStyle2');
//        $table1->addRow();
//        $table1->addCell(10000)->addText("项目证书信息",$fontStyle);
//        foreach ($data as $comp) {
//            $parm = $comp['gav'][0] . ':' . $comp['gav'][1] . ':' . $comp['gav'][2]; //获取单个证书信息数据
//            $result = $this->getLicense($parm);
//            foreach($result as $lien){
//                $url = explode(" ",$lien.trim(""));
//                $table2 = $section->addTable('myOwnTableStyle2');
//                $table2->addRow();
//                $table2->addCell(10000)->addText($parm,$fontStyle);
//                $table3 = $section->addTable('myOwnTableStyle');
//                $table3->addRow();
//                $table3->addCell(2000)->addText("License名称",$fontStyle2);
//                $table3->addCell(3000)->addText($lien,$fontStyle2);
//                $table3->addCell(2000)->addText("详细链接",$fontStyle2);
//                if(count($url)==2) {
//                    $lineUrl = "https://spdx.org/licenses/" . $url[0] . "-" . $url[1] . ".html#licenseText";
//                    $table3->addCell(3000)->addText($lineUrl,$fontStyle2);
//                }
//                else{
//                    $lineUrl= "https://spdx.org/licenses/".$url[0].".html#licenseText";
//                    $table3->addCell(3000)->addText($lineUrl,$fontStyle2);
//                }
//
//            }
//
//        }

        $section->addTextBreak(2);

        $PHPWord->addFontStyle('cOntent', array('bold' => false, 'size' => 12));
        $PHPWord->addFontStyle('rStyle', array('bold' => true, 'italic' => false, 'size' => 16, 'align' => 'center'));
        $PHPWord->addParagraphStyle('pStyle', array('align' => 'center', 'spaceAfter' => 100));
        $objWriter = IOFactory::createWriter($PHPWord, 'Word2007');
        $path = '1.' . 'doc';
        $objWriter->save($path);
        $file1 = fopen($path, "r");
        // 输入文件标签s
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:" . filesize($path));
        Header("Content-Disposition: attachment;filename=" . '1.doc');
        ob_clean();     // 重点！！！
        flush();        // 重点！！！！可以清除文件中多余的路径名以及解决乱码的问题：
        //输出文件内容
        //读取文件内容并直接输出到浏览器
        echo fread($file1, filesize($path));
        fclose($file1);
        exit();
    }

    //获取Maven项目生成组件的文件内容，并写入到word中
    public function MavenFileToWord($dataUrl, $section)
    {
        $handle = fopen($dataUrl, "r");//读取文件
        while (!feof($handle)) {
            $out = fgetcsv($handle, 2028);
            $section->addText($out, 'rStyle', 'pStyle');
        }
        fclose($handle);
        $section->addTextBreak(2);
    }

    //获取组件的license详情
    public function getLicense($parm)
    {
        $info = $parm;

        $ch = curl_init();
        $res = array();
        if ($info == '') {
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
        $html = substr($html, 0, $pos);
        $info = explode('</span>', $html);
        if (is_array($info) && !empty($info)) {
            foreach ($info as $k => $v) {
                $pos = strpos($info[$k], '>');
                $info[$k] = substr($info[$k], $pos + 1);
            }
            array_pop($info);
        }
        return $info;
    }

    function geturl($info, $flag = 0)
    {
        $res = "";
        $arr = explode(":", $info);
        if (is_array($arr) && count($arr) == 3) {
            $res .= "https://mvnrepository.com/artifact/" . $arr[0] . '/' . $arr[1];
            if ($flag == 1) {
                $res .= '/' . $arr[2];
            }
        }
        return $res;
    }

    //获取组件详情，包括热度
    public function restspid($parm)
    {
        $info = $parm;

        $ch = curl_init();
        $res = array();
        if ($info == '') {
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
        $html = substr($html, 0, $pos + 8);
        $info = explode('<tbody>', $html);
        $j = 0;
        $max = 0;
        $maxi = 0;
        if (is_array($info) && !empty($info)) {
            array_splice($info, 0, 1);
            foreach ($info as $k => $v) {
                $info[$k] = explode('<tr>', $info[$k]);
                if (is_array($info[$k]) && !empty($info[$k])) {
                    foreach ($info[$k] as $key => $val) {
                        $info[$k][$key] = explode('<td>', $info[$k][$key]);
                        if (is_array($info[$k][$key]) && !empty($info[$k][$key])) {
                            if (count($info[$k][$key]) == 5) {
                                $t = strstr($info[$k][$key][1], '>');
                                $pos = strpos($t, '</');
                                $t = substr($t, 1, $pos - 1);
                                $res['info'][$j]['version'] = $t;
                                $t = strstr($info[$k][$key][2], '>');
                                $pos = strpos($t, '</');
                                $t = substr($t, 1, $pos - 1);
                                $res['info'][$j]['Repository'] = $t;
                                $t = strstr($info[$k][$key][3], '>');
                                $pos = strpos($t, '</');
                                $t = substr($t, 1, $pos - 1);
                                if ($t == '') {
                                    $t = '0';
                                }
                                if (intval(str_replace(",", "", $t)) > $max) {
                                    $max = intval(str_replace(",", "", $t));
                                    $maxi = $j;
                                }
                                $res['info'][$j]['Usages'] = $t;
                                $pos = strpos($info[$k][$key][4], '</');
                                $t = substr($info[$k][$key][4], 0, $pos);
                                $res['info'][$j]['Date'] = $t;
                                $j++;
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
