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
				<a href="/admin/push_notifications"><h2>Push Notifikasi</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/push_notifications">Push Notifikasi</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Push Notifikasi</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<a href="/admin/push_notifications/create" title="Kirim Notifikasi"><i class="fa fa-paper-plane"></i> Kirim Notifikasi</a><br><br>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%"><b>No</b></th>
							<th><b>Keterangan</b></th>
							<th><b>Penerima</b></th>
							<th><b>Tanggal Pengiriman</b></th>
						</tr>
					</thead>
					<tbody>
					{% for notification in notifications %}
						<tr>
							<td>{{ notification.rank }}</td>
							<td>
								<p><strong>Judul :</strong><br>{% if notification.title %}{{ notification.title }}{% else %}{% endif %}</p>
								<p><strong>Pesan :</strong><br>{{ notification.message }}</p>
								<strong>Link:</strong>&nbsp;{{ notification.admin_target_url }}
							</td>
							<td>{{ notification.recipients }}</td>
							<td>{{ notification.created_at }}</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="4"><i>Belum ada data</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/push_notifications{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
							{% endif %}
						{% endfor %}
					</p>
				</div>
				{% endif %}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>