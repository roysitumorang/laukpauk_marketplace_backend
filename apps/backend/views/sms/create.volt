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
				<a href="/admin/sms"><h2>Kirim SMS</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/sms">SMS</a></span></li>
						<li><span>Kirim SMS</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Kirim SMS</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="POST" action="/admin/sms/create">
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
								<select name="user_id">
									<option value="">Semua Member</option>
									{% for user in users %}
										<option value="{{ user.id }}"{% if user.id == user_id %} selected{% endif %}>{{ user.mobile_phone }} / {{ user.name }}</option>
									{% endfor %}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<b>Pesan :</b><br>
								<textarea name="body" cols="80" rows="3" maxlength="140">{{ sms.body }}</textarea>
							</td>
						</tr>
						<tr>
							<td>
								<a type="button" class="btn btn-default" href="/admin/sms"><i class="fa fa-chevron-left"></i> Kembali</a>
								<button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Kirim</button>
							</td>
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