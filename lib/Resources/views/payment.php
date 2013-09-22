<?
namespace Destiny;
use Destiny\Common\Utils\Tpl;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
?>
<!DOCTYPE html>
<html>
<head>
<title><?=Tpl::title($model->title)?></title>
<meta charset="utf-8">
<?php include Tpl::file('seg/commontop.php') ?>
<?php include Tpl::file('seg/google.tracker.php') ?>
</head>
<body id="payment">

	<?php include Tpl::file('seg/top.php') ?>
	
	<section class="container">
		<h1 class="title">
			<span>Payment</span> <small><?=Tpl::mask($model->payment['transactionId'])?></small>
		</h1>
		<div class="content content-dark clearfix">
			<div class="control-group clearfix">
				<dl class="dl-horizontal">
					<dt>Status:</dt>
					<dd><span class="label label-<?=($model->payment['paymentStatus'] == 'Completed') ? 'success':'warning'?>"><?=Tpl::out($model->payment['paymentStatus'])?></span></dd>
					<dt>Amount:</dt>
					<dd><?=Tpl::currency($model->payment['currency'], $model->payment['amount'])?></dd>
					<dt>Reference:</dt>
					<dd><?=Tpl::mask($model->payment['transactionId'])?></dd>
					<dt>Type:</dt>
					<dd><?=Tpl::out($model->payment['transactionType'])?></dd>
					<dt>Payer:</dt>
					<dd><?=Tpl::mask($model->payment['payerId'])?></dd>
					<dt>Payment:</dt>
					<dd><?=Tpl::out($model->payment['paymentType'])?></dd>
					<dt>Payed on:</dt>
					<dd style="margin-bottom:2em;"><?=Tpl::moment(Date::getDateTime($model->payment['paymentDate']), Date::STRING_FORMAT_YEAR)?></dd>
					<dt title="This is the related order description">Description:</dt>
					<dd><?=Tpl::out($model->order['description'])?></dd>
					<dt>Order:</dt>
					<dd>#<?=Tpl::out($model->order['orderId'])?></dd>
					<?if(!empty($model->paymentProfile['paymentProfileId'])): ?>
					<dt>Recurring:</dt>
					<dd><?=Tpl::mask($model->paymentProfile['paymentProfileId'])?></dd>
					<?php endif; ?>
				</dl>
			</div>
			<div class="form-actions block-foot">
				<img class="pull-right" title="Powered by Paypal" src="<?=Config::cdn()?>/web/img/Paypal.logosml.png" />
				<a class="btn" href="/profile/subscription">Back to profile</a>
			</div>
		</div>
	</section>
	
	<?php include Tpl::file('seg/foot.php') ?>
	<?php include Tpl::file('seg/commonbottom.php') ?>
	
</body>
</html>