{include file='header.html'}

	<body>
		<section class="body">

			<!-- start: header -->
			{include file='top_menu.html'}
			<!-- end: header -->

			<div class="inner-wrapper">
				<!-- start: sidebar -->
				{include file='left_side.html'}
				<!-- end: sidebar -->

				<section role="main" class="content-body">
					<header class="page-header">
						<a href="message.php?do=inbox"><h2>Pesan</h2></a>

						<div class="right-wrapper pull-right">
							<ol class="breadcrumbs">
								<li>
									<a href="main.php">
										<i class="fa fa-home"></i>
									</a>
								</li>
								<li><span><a href="message.php?do=inbox">Inbox</a></span></li>
								<li><span>Detail Pesan</span></li>
							</ol>

							<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
						</div>
					</header>

<!-- start: page -->
									<header class="panel-heading">
										<h2 class="panel-title">{$Detail.vSubject}</h2>
									</header>
<div class="panel-body">
<!-- Content //-->

{$reportMessage}

<table class="table table-striped">
<tr>
	<td><b>Dari:</b>&nbsp;{$Detail.vUsername}<br /><a href="message.php?do=add&id={$Detail.id}" title="Reply"><img src="assets/image/send-email-small.png" border="0" /></a></td>
</tr>
<tr>
	<td><b>Kepada:</b><br />{$Detail.vTo}</td>
</tr>
<tr>
	<td><b>Tanggal:</b><br />{$Detail.dTanggal|date_format:"%A, %d %B %Y - %H:%M:%S"}</td>
</tr>
<tr>
	<td><b>Subject:</b><br />{$Detail.vSubject}</td>
</tr>
<tr>
	<td><b>Pesan:</b><br />{$Detail.tMessage|replace:"\n":"<br />"}</td>
</tr>
</table>

<!-- eof Content //-->
</div>
<!-- end: page -->

				</section>
			</div>

			{include file='right_side.html'}

		</section>

{include file='footer.html'}