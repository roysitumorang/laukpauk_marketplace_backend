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
				<a href="/admin/sms"><h2>SMS</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/sms">SMS</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">SMS</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<a href="/admin/sms/create" title="Kirim SMS"><i class="fa fa-paper-plane"></i> Kirim SMS</a><br><br>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%"><b>No</b></th>
							<th><b>Pesan</b></th>
							<th><b>Penerima</b></th>
							<th><b>Tanggal Pengiriman</b></th>
						</tr>
					</thead>
					<tbody>
					{% for text in texts %}
						<tr>
							<td>{{ text.rank }}</td>
							<td>{{ text.body }}</td>
							<td>{{ text.recipients }}</td>
							<td>{{ text.created_at }}</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="4"><i>Belum ada SMS</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if page.last > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/sms/index/page:{{ i }}">{{ i }}</a>
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