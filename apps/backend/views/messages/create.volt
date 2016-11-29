<section class="body">
	<!-- start: header -->
	{{ partial('partials/top_menu') }}
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		{{ partial('partials/left_side') }}
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<a href="/admin/messages"><h2>Kirim Pesan</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/messages">Semua Pesan</a></span></li>
						<li><span>Kirim Pesan</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Kirim Pesan</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="POST" action="/admin/messages/create">
					<table class="table table-striped">
						<tr>
							<td>
								<b>Dari :</b><br>
								{{ current_user.name }}
							</td>
						</tr>
						<tr>
							<td>
								<b>Kepada :</b><br>
								<input type="text" name="recipients" value="" class="form form-control" size="40"><br>
								<input type="checkbox" name="email_recipient">&nbsp;Kirim pesan ke email user<br>
								<input type="checkbox" name="send_to_all_users">&nbsp;Kirim pesan ini kepada semua member
							</td>
						</tr>
						<tr>
							<td>
								<b>Subject :</b><br>
								<input type="text" name="subject" value="{{ message.subject }}" class="form form-control" size="60">
							</td>
						</tr>
						<tr>
							<td>
								<b>Pesan :</b><br>
								<textarea name="body" cols="80" rows="10" class="form form-control">{{ message.body }}</textarea>
							</td>
						</tr>
						<tr>
							<td><button type="submit" class="btn btn-info">KIRIM</button></td>
						</tr>
					</table>
				</form>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>