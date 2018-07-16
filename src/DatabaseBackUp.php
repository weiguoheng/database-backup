<?php
namespace think;

use Chumper\Zipper\Zipper;

class DatabaseBackUp{
	public function __construct(){

	}
	public function Hello(){
		return "123";
	}
	// 数据备份
    public function dataExport($path='./data/'){
        $zip = new Zipper;
        header("Content-type:text/html;charset=utf-8");  
        $path = $path;
        $model = model();
        return $model;
        //查询所有表
        $sql="show tables";
        $result=$model->query($sql);
        return $result;
        //print_r($result);exit;  
        //echo "运行中，请耐心等待...<br/>";  
        $info = "-- ----------------------------\r\n";  
        $info .= "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";  
        $info .= "-- MySQL - 5.1.73 : Database - ".C('DB_NAME')."\r\n";  
        $info .= "-- ----------------------------\r\n\r\n";  
        $info .= "CREATE DATAbase IF NOT EXISTS `".C('DB_NAME')."` DEFAULT CHARACTER SET utf8 ;\r\n\r\n";
        $info .= "USE `".C('DB_NAME')."`;\r\n\r\n";  
        // 检查目录是否存在  
        if(is_dir($path)){  
            // 检查目录是否可写  
            if(is_writable($path)){
                //echo '目录可写';exit;  
            }else{  
                echo '目录不可写';exit;  
                //chmod($path,0777);  
            }  
        }else{  
            //echo '目录不存在';exit;
            // 新建目录  
            mkdir($path, 0777, true);  
        }  
        // 检查文件是否存在  
        $file_name = C('DB_NAME').'-'.date("Y-m-d",time()).'.sql';
        $file_zip_name = $path.C('DB_NAME').'-'.date("Y-m-d",time()).'.zip';
        if(file_exists($file_zip_name)){  
            $error['status'] = 0;
            $error['msg'] = '备份已存在！';
            $this->ajaxReturn($error);
        }
        fopen($file_zip_name, "w");//创建压缩文件
        file_put_contents($file_name,$info,FILE_APPEND);  
        foreach ($result as $k=>$v) {
            //查询表结构  
            $val = $v['tables_in_'.C('DB_NAME')];
            if(in_array($v['tables_in_wfx'], array('wfx_cms_set','wfx_set','wfx_shop_set','wfx_user_oath'))){
                continue;
            }
            $sql_table = "show create table ".$val;  
            $res = $model->query($sql_table);  
            //print_r($res);exit;  
            $info_table = "-- ----------------------------\r\n";  
            $info_table .= "-- Table structure for `".$val."`\r\n";  
            $info_table .= "-- ----------------------------\r\n\r\n";  
            $info_table .= "DROP TABLE IF EXISTS `".$val."`;\r\n\r\n";  
            $info_table .= $res[0]['create table'].";\r\n\r\n";  
            //查询表数据  
            $info_table .= "-- ----------------------------\r\n";  
            $info_table .= "-- Data for the table `".$val."`\r\n";  
            $info_table .= "-- ----------------------------\r\n\r\n"; 
            file_put_contents($file_name,$info_table,FILE_APPEND);
            $sql_data = "select * from ".$val;  
            $data = $model->query($sql_data);  
            //print_r($data);exit;  
            $count= count($data);  
            //print_r($count);exit;  
            if($count<1) continue;  
            foreach ($data as $key => $value){
                $sqlStr = '';
                if($key===0){
                    $sqlStr = "INSERT INTO `".$val."` VALUES (";  
                }else {
                    $sqlStr = "(";
                }
                foreach($value as $v_d){  
                    $v_d = str_replace("'","\'",$v_d);
                    if(intval(strlen($v_d))){
                        $sqlStr .= "'".$v_d."', ";
                    } else {
                        $sqlStr .= "NULL, ";
                    }
                }
                //去掉最后一个逗号和空格  
                $sqlStr = substr($sqlStr,0,strlen($sqlStr)-2);
                if(count($data)===$key+1){
                    $sqlStr .= ");\r\n";
                } else {
                    $sqlStr .= "),\r\n";
                }
                file_put_contents($file_name,$sqlStr,FILE_APPEND);  
            }  
            $info = "\r\n";  
            file_put_contents($file_name,$info,FILE_APPEND);
        }
        $res = $zip->open($file_zip_name);
        if ($res) {
            if($zip->addFile(C('DB_NAME').'-'.date("Y-m-d",time()).'.sql')){
                $zip->close();
                unlink($file_name);
                $success['status'] = 1;
                $success['msg'] = '备份成功！';
                $this->ajaxReturn($success);
            }
        }else{
            $success['status'] = 0;
            $success['msg'] = '备份失败！';
            $this->ajaxReturn($success);
        }
    }  
}
?>