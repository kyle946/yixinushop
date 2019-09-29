<?php

/**
 * Description of imageController
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */


namespace controllers;

$business_no = dechex($_SESSION['business_no']);
define('IMAGE_PATH_EXT', IMAGE_PATH.$business_no.DIRECTORY_SEPARATOR);
define('IMAGE_URL_EXT', IMAGE_URL.$business_no.DIRECTORY_SEPARATOR);

class imageController extends adminController {
    
    public $business_no = null;


    public function __construct() {
        parent::__construct();
        $this->business_no = dechex($_SESSION['business_no']);
        if (!file_exists(IMAGE_PATH_EXT)) {//检查目录是否存在
            mkdir(IMAGE_PATH_EXT, 0770, true);  //mkdir("temp/sub, 0777, true);
        }
        if (!file_exists(IMAGE_PATH_EXT)) {
            mkdir(IMAGE_PATH_EXT);
        }
    }

    public function show() {
        //获取类型为本地上传还是图片库  , 参数有 local  list   net  三个
        $type='paste';
        if(isset($_GET['type'])):
            $type = $_GET['type'];
        endif;
        $this->assign('type',$type);
        
        //获取选择模式是单一还是多选 ， 参数有 single   multiple  两个
        $mode='single';
        if(isset($_GET['mode'])):
            $mode = $_GET['mode'];
        endif;
        $this->assign('mode',$mode);
        
        
        //允许插入图片 的大小级别
        $levelData_ = array(
            1=>array('k'=>'1','name'=>'缩略图'),
            2=>array('k'=>'2','name'=>'400 X 300'),
            3=>array('k'=>'3','name'=>'800 X 600'),
            4=>array('k'=>'4','name'=>'原图'),
            5=>array('k'=>'1','name'=>'商品图'),  //这里其实就是插入缩略图，因为商品图选择后会自动更换为400大小的图片 ，这里选择缩略图只为后台显示用。
        );
        $level_ = $_GET['level'];
        $this->assign('level', $level_);
        $level = explode(',', $level_);
        foreach ($level as $value) {
            $levelData[$value] = $levelData_[$value];
        }
        $this->assign('levellist',$levelData);
        
        //父窗口显示图片的元素id
        if(isset($_GET['id'])) $this->assign('id',$_GET['id']);
        //父窗口保存图片路径的input 名称
        if(isset($_GET['inputName'])) $this->assign('inputName',$_GET['inputName']);
        
        if($type=='list'){
            if(isset($_GET['name'])){ //如果指定了目录，则读取目录下的缩略图
                    $result = scandir(IMAGE_PATH_EXT.$_GET['name'].DIRECTORY_SEPARATOR);
                    $dirData = array();
                    foreach ($result as $key => $value) {
                        if($value != '.' && $value != '..' && $value !=".svn"){
                            if(strstr($value, 'thumb')){
                                $dirData[] = $value;
                            }
                        }
                    }
                    $this->assign("dirdata",$dirData);
            }else{//如果没有$_GET['name']变量，则读取目录
                    $result = scandir(IMAGE_PATH_EXT);
                    $dirData = array();
                    foreach ($result as $key => $value) {
                        if(is_dir(IMAGE_PATH_EXT.$value) && $value != '.' && $value != '..' && $value !=".svn"){
                            $dirData[] = $value;
                        }
                    }
                    $this->assign("dirdata",$dirData);
            }
        }
        $this->assign('business_no', $this->business_no);
        $this->display("imageShow");
    }
    
    /**
     * 粘贴上传图片
     */
    public function paste_upload() {
            $_POST['image'] = str_replace('data:image/jpeg;base64,', '', $_POST['image']);
            $_POST['image'] = str_replace('data:image/jpg;base64,', '', $_POST['image']);
            $_POST['image'] = str_replace('data:image/png;base64,', '', $_POST['image']);
            $str = base64_decode($_POST['image']);
            $info = getimagesizefromstring($str);
            if (!$info) {
                echo json_encode(array('error' => 1, 'info' => '解码失败') );
                exit();
            }
            switch ($info['mime']) {
                case 'image/png':
                    $ext = '.png';
                    break;
                case 'image/jpeg':
                    $ext = '.jpg';
                    break;
                case 'image/gif':
                    $ext = '.gif';
                    break;
                case 'image/pjpeg':
                    $ext = '.jpg';
                    break;
            }

            //判断要不要添加新的目录，每个图片目录只放200张图片 
            $tmpvar1 = scandir(IMAGE_PATH_EXT);
            $tmpvar2 = end($tmpvar1);
            $tmpvar3 = 1000;    //设置一个默认值
            if( $tmpvar2==false || $tmpvar2=='..' || $tmpvar2=='.' ){
                $tmpvar3++;
            }else{
                $tmpvar4 = scandir(IMAGE_PATH_EXT.DIRECTORY_SEPARATOR.$tmpvar2);
                if( count($tmpvar4) > 802 ){    //4 * 200 + 2,因为每张图片会生成四张小图，加上目录里面本身有  .  和   ..   两个目录 
                    $tmpvar3 = $tmpvar2+1;
                }else{
                    $tmpvar3 = $tmpvar2;
                }
            }
            $PathId = $tmpvar3.DIRECTORY_SEPARATOR;
            $photo_folder = IMAGE_PATH_EXT .$PathId; //上传照片路径
            ///////////////////////////////////////////////////开始处理上传
            if (!file_exists($photo_folder)) {//检查照片目录是否存在
                mkdir($photo_folder, 0770, true);  //mkdir("temp/sub, 0777, true);
            }
            if (!file_exists($photo_folder)){//照片目录
                mkdir($photo_folder);
            }
            $time_ = time();
            $new_file_name = $photo_folder . $time_  . $ext; 
           $res = file_put_contents($new_file_name, $str);//返回的是字节数
           
           //如果写入成功
           if($res){
                    $thumbImage = $photo_folder.'thumb_' . $time_ . $ext; //缩略图文件名，这里是全路径 
                    $thumbImage2 = $photo_folder.'800_' . $time_ . $ext; //缩略图文件名，这里是全路径 
                    $thumbImage3 = $photo_folder.'400_' . $time_ . $ext; //缩略图文件名，这里是全路径 
                    //生成缩略图
                    $this->makeThumb($new_file_name, $thumbImage);
                    $this->makeThumb($new_file_name, $thumbImage2,800,800);
                    $this->makeThumb($new_file_name, $thumbImage3,400,400);
                    
                    //取文件名
                    $pinfo = pathinfo($thumbImage);
                    $fname = $pinfo['basename'];
                    $data['error'] = 0;
                    $data['path'] = $this->business_no.'/'.$PathId.$fname;   //echo " 已经成功上传：".$photo_server_folder."<br />";
                    echo json_encode($data);
                    exit();
           }
    }

    /**
     * ajax 即时上传图片
     * return -1:文件超过规定大小
     * return -2:文件类型不符
     * return -3:移动文件出错
     */
    public function upload() {
        $data = array();
       if (is_uploaded_file($_FILES['upfile']['tmp_name'])) {
                    $photo_types = array('image/jpg', 'image/jpeg', 'image/png', 'image/pjpeg', 'image/gif', 'image/x-png'); //定义上传格式
                    $max_size = 1024000;    //上传照片大小限制,默认1M
                    //获取文件信息，检验文件
                    $upfile = $_FILES['upfile'];
                    $name = $upfile['name'];
                    $type = $upfile['type'];
                    $size = $upfile['size'];
                    $tmp_name = $upfile['tmp_name'];
                    $file = $_FILES["upfile"];
                    $photo_name = $file["tmp_name"];
                    $photo_size = getimagesize($photo_name);
                    //更新表单唯一验证码，为了避免出出非法操作的情况 。
                    $data['verifyKey'] = $this->formverify();
                    if ($max_size < $file["size"]){//检查文件大小
                        $data['info'] = "-1";       //echo "<script>alert('对不起，文件超过规定大小!');history.go(-1);</script>";
                        echo json_encode($data);
                        exit();
                    }
                    if (!in_array($file["type"], $photo_types)){//检查文件类型
                        $data['info'] = "-2";       //echo "<script>alert('对不起，文件类型不符!');history.go(-1);</script>";
                        echo json_encode($data);
                        exit();
                    }
                    
                    //判断要不要添加新的目录，每个图片目录只放200张图片 
                    $tmpvar1 = scandir(IMAGE_PATH_EXT);
                    $tmpvar2 = end($tmpvar1);
                    $tmpvar3 = 1000;    //设置一个默认值
                    if(!$tmpvar2){
                        $tmpvar3++;
                    }else{
                        $tmpvar4 = scandir(IMAGE_PATH_EXT.DIRECTORY_SEPARATOR.$tmpvar2);
                        if( count($tmpvar4) > 802 ){    //4 * 200 + 2,因为每张图片会生成四张小图，加上目录里面本身有  .  和   ..   两个目录 
                            $tmpvar3 = $tmpvar2+1;
                        }else{
                            $tmpvar3 = $tmpvar2;
                        }
                    }
                    $PathId = $tmpvar3.DIRECTORY_SEPARATOR;
                    $photo_folder = IMAGE_PATH_EXT .$PathId; //上传照片路径
                    ///////////////////////////////////////////////////开始处理上传
                    if (!file_exists($photo_folder)) {//检查照片目录是否存在
                        mkdir($photo_folder, 0770, true);  //mkdir("temp/sub, 0777, true);
                    }
                    if (!file_exists($photo_folder)){//照片目录
                        mkdir($photo_folder);
                    }
                    $pinfo = pathinfo($file["name"]);
                    $photo_type = $pinfo['extension']; //上传文件扩展名
                    $time_ = time();
                    $photo_server_folder = $photo_folder . $time_ . "." . $photo_type; //原图文件名，这里是全路径 
                    $thumbImage = $photo_folder.'thumb_'.$time_.".".$photo_type; //缩略图文件名，这里是全路径 
                    $thumbImage2 = $photo_folder.'800_'.$time_.".".$photo_type; //缩略图文件名，这里是全路径 
                    $thumbImage3 = $photo_folder.'400_'.$time_.".".$photo_type; //缩略图文件名，这里是全路径 

                    if (!move_uploaded_file($photo_name, $photo_server_folder)) {
                        $data['info'] = "-3"; //echo "移动文件出错";
                        echo json_encode($data);
                        exit;
                    }
                    //生成缩略图
                    $this->makeThumb($photo_server_folder, $thumbImage);
                    $this->makeThumb($photo_server_folder, $thumbImage2,800,800);
                    $this->makeThumb($photo_server_folder, $thumbImage3,400,400);
                    //取文件名
                    $pinfo = pathinfo($thumbImage);
                    $fname = $pinfo['basename'];
                    $data['path'] = $this->business_no.'/'.$PathId.$fname;   //echo " 已经成功上传：".$photo_server_folder."<br />";
                    echo json_encode($data);
                    exit();
        }
    }
    
    /**
     * 生成缩略图
     * @param type $filename  原文件 ，这里是全路径 
     * @param type $dst  目标文件 ，这里是全路径 
     * @param type $width  生成 缩略 图的宽度
     * @param type $height  生成 缩略 图的高度
     * @return boolean
     */
    public function makeThumb($filename,$dst,$width=250,$height=250) {
            $thumb_width = $width;
            $thumb_height = $height;

            //取得图片信息
            list($width_orig , $height_orig , $mime_type) = getimagesize($filename);
//            $imagetype = image_type_to_extension($mime_type,1);
            //如果图片尺寸小于生成的尺寸则使用图片原尺寸缩放
            if($width_orig<$width && $height_orig<$height){
                $width = $width_orig;
                $height = $height_orig;
            }
            //算出缩放比例
            if ($width && ($width_orig < $height_orig)) {
                $width = ($height / $height_orig) * $width_orig;
            } else {
                $height = ($width / $width_orig) * $height_orig;
            }

            //创建一张图片
            $image_p = imagecreatetruecolor($thumb_width, $thumb_height);
            //用白色填充背景
            $clr = imagecolorallocate($image_p, 253, 253, 253);
            imagefilledrectangle($image_p, 0, 0, $thumb_width, $thumb_height, $clr);
            switch ($mime_type)
            {
                case 1:
                case 'image/gif':
                    $image = imagecreatefromgif($filename);
                    break;

                case 2:
                case 'image/pjpeg':
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($filename);
                    break;

                case 3:
                case 'image/x-png':
                case 'image/png':
                    $image = imagecreatefrompng($filename);
                    break;

                default:
                    return false;
            }
            //算出复制图片居中的坐标
            $dst_x = ($thumb_width  - $width)  / 2;
            $dst_y = ($thumb_height - $height) / 2;
            //完成复制
            imagecopyresampled($image_p, $image, $dst_x, $dst_y, 0, 0, $width, $height, $width_orig, $height_orig);

            // 输出图片
            switch ($mime_type)
            {
                case 1:
                case 'image/gif':
                    imagegif($image_p, $dst);
                    break;

                case 2:
                case 'image/pjpeg':
                case 'image/jpeg':
                    imagejpeg($image_p, $dst, 85);
                    break;

                case 3:
                case 'image/x-png':
                case 'image/png':
                    imagepng($image_p, $dst);
                    break;
                default:
                    return false;
            }
    }
    
    public function exemption() {
        header("Location: ".__ROOT__);
    }
    
}
