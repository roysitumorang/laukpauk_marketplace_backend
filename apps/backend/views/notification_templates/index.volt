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
				<a href="/admin/notification_templates{% if page.current > 1%}/index/page={{ page.current }}{% endif %}"><h2>Daftar Template Notifikasi</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Template Notifikasi</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Template Notifikasi</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/notification_templates/create" class="new">Tambah Template Notifikasi</a></p>
				<table class="table table-striped">
					<thead>
						<tr>
							<th class="text-center" width="5%">No</th>
							<th class="text-center">Nama</th>
							<th class="text-center">Teks</th>
							<th class="text-center">Link Admin</th>
							<th class="text-center">Link Merchant</th>
							<th class="text-center">Link Mobile Lama</th>
							<th class="text-center">Link Mobile Baru</th>
							<th class="text-center">#</th>
						</tr>
					</thead>
					<tbody>
					{% for notification_template in notification_templates %}
						<tr id="{{ notification_template.id }}">
							<td class="text-right">{{ notification_template.rank }}</td>
							<td>{{ notification_template.name }}</td>
							<td>{{ notification_template.title }}</td>
							<td>{{ notification_template.admin_target_url }}</td>
							<td>{{ notification_template.merchant_target_url }}</td>
							<td>{{ notification_template.old_mobile_target_url }}</td>
							<td>{{ notification_template.new_mobile_target_url }}</td>
							<td class="text-center">
								<a href="/admin/notification_templates/{{ notification_template.id }}/update" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="8"><i>Belum ada data</i></td>
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
							<a href="/admin/notification_templates{% if i > 1 %}/index/page={{ i }}{% endif %}">{{ i }}</a>
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