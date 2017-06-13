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
				<a href="/admin/notifications"><h2>Notifikasi</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/notifications">Notifikasi</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Notifikasi</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%"><b>No</b></th>
							<th><b>Keterangan</b></th>
							<th><b>Tanggal</b></th>
							<th><b>Status</b></th>
						</tr>
					</thead>
					<tbody>
					{% for notification in notifications %}
						<tr class="notification"{% if !notification.read_at %} data-id="{{ notification.id }}"{% endif %} data-link="{{ notification.link }}">
							<td>{{ notification.rank }}</td>
							<td>{{ notification.title }}<br><strong>Link:</strong>&nbsp;{{ notification.target_url }}</td>
							<td>{{ notification.created_at }}</td>
							<td>{% if notification.read_at %}Read{% else %}<i><font color="#FF0000">Unread</font></i>{% endif %}</td>
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
							<a href="/admin/notifications/index/page:{{ i }}">{{ i }}</a>
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
<script>
	for (let notifications = document.querySelectorAll('.notification'), i = notifications.length; i--; ) {
		let notification = notifications[i];
		notification.setAttribute('style', 'cursor:pointer'),
		notification.onclick = function() {
			if (this.dataset.id) {
				fetch('/admin/notifications/update/' + this.dataset.id + '/read:1')
			}
			location.href = this.dataset.link
		}
	}
</script>