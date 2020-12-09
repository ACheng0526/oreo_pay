<?php
$mod='blank';
include "../oreo/Oreo.Cron.php";
include './oreo_static.php';
if ($islogin == 1) {
} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
if(isset($_GET['reset'])){
	$batch=$_GET['batch'];
	unset($_SESSION['privatekey']);
	exit("<script language='javascript'>window.location.href='./transfer.php?batch={$batch}';</script>");
}elseif(isset($_POST['batch']) && isset($_POST['privatekey'])){
	$batch=$_POST['batch'];
	if(strlen($_POST['privatekey'])<100)exit("<script language='javascript'>alert('商户私钥不正确');history.go(-1);</script>");
	$_SESSION['privatekey']=$_POST['privatekey'];
	exit("<script language='javascript'>window.location.href='./transfer.php?batch={$batch}';</script>");
}elseif(isset($_GET['batch']) && isset($_SESSION['privatekey'])){
	$batch=$_GET['batch'];
	$count=$DB->query("SELECT * from oreo_batch where batch='$batch'")->rowCount();
	if($count<1)showmsg('批次号不存在');
	$list=$DB->query("SELECT * FROM oreo_settle WHERE batch='{$batch}' and type=1")->fetchAll();

?>
	<script src="../assets/admin/js/jquery.min.js"></script>
	<script>
function SelectAll(chkAll) {
	var items = $('.uins');
	for (i = 0; i < items.length; i++) {
		if (items[i].id.indexOf("uins") != -1) {
			if (items[i].type == "checkbox") {
				items[i].checked = chkAll.checked;
			}
		}
	}
}
function Transfer(){
	var url="transfer_do.php";
	$("input[name=uins]:checked:first").each(function(){
		var checkself=$(this);
		var id=checkself.val();
		var statusself=$('#id'+id);
		statusself.html("<img src='../assets/admin/images/load.gif' height=22>");
		xiha.postData(url,'id='+id, function(d) {
			if(d.code==0){
				transnum++;
				var num = $('#hydx').text();
				num=parseInt(num);
				num++;
				$('#hydx').text(num);
				if(d.ret==1){
					statusself.html('<font color="green">成功</font>');
				}else if(d.ret==2){
					statusself.html('<font color="green">已完成</font>');
				}else{
					statusself.html('<font color="red">失败</font>');
				}
				$('#res'+id).html('<font color="blue">'+d.result+'</font>');
				checkself.attr('checked',false);
				Transfer();
			}else if(d.code==-1){
				statusself.html('<font color="red">失败</font>');
				alert(d.msg);
			}else{
				statusself.html('<font color="red">失败</font>');
			}
		});
		return true;
	});
}
var transnum = 0;
$(document).ready(function(){
	var allmoney = 0;
	var items = $('.money');
	for (i = 0; i < items.length; i++) {
		allmoney+=parseFloat(items[i].innerHTML);
	}
	$('#allmoney').html('总金额:'+allmoney.toFixed(2));
	$('#startsend').click(function(){
		var self=$(this);
		if (self.attr("data-lock") === "true") return;
			else self.attr("data-lock", "true");
		self.html('正在转账中');
		Transfer();
		if(transnum<1) self.html('没有待转账的记录');
		else self.html('转账处理完成');
		self.attr("data-lock", "false");
	});
	$('.recheck').click(function(){
		var self=$(this),
			id=self.attr('uin');
		var url="transfer_do.php";
		self.html("<img src='../assets/admin/images/load.gif' height=22>");
		xiha.postData(url,'id='+id, function(d) {
			if(d.code==0){
				if(d.ret==1){
					self.html('<font color="green">成功</font>');
				}else if(d.ret==2){
					self.html('<font color="green">已完成</font>');
				}else{
					self.html('<font color="red">失败</font>');
				}
				$('#res'+id).html('<font color="blue">'+d.result+'</font>');
				$('.uins[value='+id+']').attr('checked',false);
				self.removeClass('nocheck');
			}else if(d.code==-1){
				self.html('<font color="red">失败</font>');
				alert(d.msg);
			}else{
				self.html('<font color="red">失败</font>');
			}
		});
	});
});
var xiha={
	postData: function(url, parameter, callback, dataType, ajaxType) {
		if(!dataType) dataType='json';
		$.ajax({
			type: "POST",
			url: url,
			async: true,
			dataType: dataType,
			json: "callback",
			data: parameter,
			success: function(data,status) {
				if (callback == null) {
					return;
				}
				callback(data);
			},
			error: function(error) {
				//alert('创建连接失败');
			}
		});
	}
}
</script>
                              <div class="row">
                                <div class="col-sm-12">
                                    <div class="page-title-box">
                                        <div class="btn-group float-right">
                                            <ol class="breadcrumb hide-phone p-0 m-0">
                                                <li class="breadcrumb-item"><a href="#">控制台</a></li>
                                                <li class="breadcrumb-item"><a href="#">系统参数</a></li>
                                                <li class="breadcrumb-item active">批量转账到支付宝</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">批量转账到支付宝</h4>
                                    </div>
                                </div>
                            </div>
<div class="card">

<div class="card-header">
		<div class="panel-title">
			<div class="input-group" style="padding:8px 0;">
				<div class="input-group-addon btn">全选<input type="checkbox" onclick="SelectAll(this)" /></div>
				<div class="input-group-addon btn" id="startsend">点此开始转账</div>
				<div class="input-group-addon btn"><span onclick="window.location.href='transfer.php?reset=1&batch=<?php echo $batch?>'">重置商户私钥</span></div>
				<div class="input-group-addon btn"><span id="allmoney">总金额</span></div>
			</div>
			<div id="result"></div>
		</div>
	</div>
</div>

<div class="card">

<div class="card-header">
	<table class="table table-bordered table-condensed">
		<tbody>
			<tr>
			<td align="center"><span style="color:silver;"><b>ID</b></span></td>
			<td align="center"><span style="color:silver;"><b>商户ID</b></span></td>
			<td align="center"><span style="color:silver;"><b>结算账号</b></span></td>
			<td align="center"><span style="color:silver;"><b>结算姓名</b></span></td>
			<td align="center"><span style="color:silver;"><b>金额</b></span></td>
			<td align="center"><span style="color:silver;"><b>操作</b></span></td>
			</tr>
			<?php
			echo '<tr><td colspan="6" align="center">总共<span id="hyall">'.count($list).'<span>个记录,已经处理<span id="hydx">0</span>个记录！</td></tr>';
			foreach($list as $row) {
			echo '<tr>
			<td uin="'.$row['id'].'">
			<input name="uins" type="checkbox" id="uins" class="uins" value="'.$row['id'].'" '.($row['transfer_status']!=1?'checked':null).'>'.$row['id'].'</td>
			<td>'.$row['pid'].'</td>
			<td>'.$row['account'].'</td>
			<td>'.$row['username'].'</td>
			<td class="money">'.$row['money'].'</td>
			<td id="id'.$row['id'].'" uin="'.$row['id'].'" class="nocheck recheck" align="center">'.($row['transfer_status']!=1?'<span class="btn btn-xs btn-block btn-primary">立即转账</span>':'<font color="green">已完成</font>').'</td>
			</tr>
			<tr>
			<td>
			<span style="color:silver;">结果</span>
			</td>
			<td colspan="5" id="res'.$row['id'].'">
			<font color="blue">'.($row['transfer_status']==1?'支付宝转账单据号:'.$row['transfer_result'].' 支付时间:'.$row['transfer_date']:$row['transfer_result']).'</font>
			</td></tr>';
			}
			?>
		</tbody>
	</table>
</div>
</div>
    </div>
<?php }elseif(isset($_GET['batch'])){
$batch=$_GET['batch'];
?>
	 <div class="page-content-wrapper ">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="page-title-box">
                                        <div class="btn-group float-right">
                                            <ol class="breadcrumb hide-phone p-0 m-0">
                                                <li class="breadcrumb-item"><a href="#">控制台</a></li>
                                                <li class="breadcrumb-item"><a href="#">系统参数</a></li>
                                                <li class="breadcrumb-item active">批量转账到支付宝</li>
                                            </ol>
                                        </div>
                                        <h4 class="page-title">批量转账到支付宝</h4>
                                    </div>
                                </div>
                            </div>
                            <!-- end page title end breadcrumb -->
                            <div class="row">
                                <div class="col-lg98-6">
                                    <div class="card m-b-30">
                                        <div class="card-body">
                                            <h4 class="mt-0 header-title">批量转账到支付宝说明</h4>
                                            <p class="text-muted m-b-30 font-14">批量转账到支付宝是目前只针对企业认证的客户使用，你若是企业认证用户，请先确认您是否开启支付宝有关权限！</p>
                                                <div class="form-group">
												<form action="transfer.php" method="post">
												<input type="hidden" name="batch" value="<?php echo $batch?>"/>
                                                    <label>服务条款配置</label>
                                                    <div>
                                                       <textarea  placeholder="填写商户私钥" name="privatekey" rows="8" class="form-control"></textarea>
                                                       <small>* 此转账功能只针对开通第三方有关权限的对企业用户有效！</small>
                                                  </div>
                                                </div>
                                                <div class="form-group m-b-0">
                                                    <div> 
                                                        <button type="submit" id="save"  value="保存修改" class="btn btn-primary waves-effect waves-light" >
                                                            提交
                                                        </button>
                                                        <button type="reset" class="btn btn-secondary waves-effect m-l-5">
                                                            重置
                                                        </button>
                                                    </div>
                                                </div>
												</form>
                                        </div>
                                    </div>
                                </div> <!-- end col -->
                </div> <!-- content -->
				<?php }?>
<?php include'foot.php';?>