<script type="text/javascript" src="/js/jquery-1.10.1.min.js"></script>
<script type="text/javascript" src="/js/jquery.qrcode.min.js"></script>

<?php 
if($html['pay_type'] == 1){
	$pay_name = '微信';
}
if($html['pay_type'] == 2){
	$pay_name = '支付宝';
}
?>

<style type="text/css">
	<?php if($html['pay_type'] == 1){ ?>
.collc{color: #2aa144;}
.collb {  border: 1px solid #2aa144; }
.collbg{ background: #2aa144;}
<?php } ?>
<?php if($html['pay_type'] == 2){ ?>

.collc { color: #00a0e9; }
.collb { border: 1px solid #00a0e9; }
.collbg { background: #00a0e9; }
<?php } ?>
</style>

<?php if($html['pay_type'] == 1){ ?>
<img src="/images/wxzf.jpg" style="display: block; margin: 20px auto; width: 200px;">
<?php } ?>
<?php if($html['pay_type'] == 2){ ?>
<img src="/images/zfbzf.jpg" style="display: block; margin: 20px auto; width: 200px;">
<?php } ?>

<h3 class="collc collb" style="display: block; height: 40px; line-height: 40px; text-align: center; border-radius: 10px; width: 80%; margin: 0 auto;">欢迎使用<?php echo $pay_name;?>支付</h3>
<div id="code" style="margin: 6% auto 10%; width: 260px;"></div>
<div id="imgDiv" style="margin: 6% auto 10%; width: 260px;display: none;"></div>
<p style="text-align: center;">请先保存二维码，再用<?php echo $pay_name;?>扫一扫。</p>
<a class="collbg" style="width: 80%; display: block; margin: 0 auto; color: #fff; height: 50px; line-height: 50px; text-decoration: none; text-align: center;" href="/user/index">确认支付</a>
<script type="text/javascript">
	$("#code").empty().qrcode({
			render: "canvas",
			text: '<?= $html['data'] ?>',
			width: 260,
			height: 260
		});

	//从canvas中提取图片image
        // function convertCanvasToImage(canvas) {
        //     //新Image对象，可以理解为DOM
        //     var image = new Image();
        //     // canvas.toDataURL 返回的是一串Base64编码的URL，当然,浏览器自己肯定支持
        //     // 指定格式PNG
        //     image.src = canvas.toDataURL("image/png");
        //     return image;
        // }

        // //获取网页中的canvas对象
        // var mycanvas1=document.getElementsByTagName('canvas')[0];

        // //将转换后的img标签插入到html中
        // var img = convertCanvasToImage(mycanvas1);
        // $('#imgDiv').append(img);//imgDiv表示你要插入的容器id


</script>