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
				<a href="/admin/messages"><h2>Inbox</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Inbox</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Inbox</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<!-- Main Content //-->
				<form method="GET" action="/admin/messages/index/unread:1">
					<table class="table table-striped">
						<tr>
							<td>
								<input type="text" name="keyword" value="{{ keyword }}" size="40" placeholder="Keyword">&nbsp;
								<button type="submit" class="btn btn-info">CARI</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="5%"><b>No</b></th>
							<th><b>Dari</b></th>
							<th><b>Tanggal</b></th>
							<th><b>Subject</b></th>
							<th><b>Pesan</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for message in messages %}
						<tr style="font-weight:{% if message.read_at %}normal{% else %}bold{% endif %}">
							<td>{{ message.rank }}</td>
							<td>{{ message.sender.name }}</td>
							<td>{{ date_format(message.created_at, '%A, %d %B %Y - %H:%M:%S') }}</td>
							<td><a href="/admin/messages/show/{{ message.id }}">{{ message.subject }}</a></td>
							<td><a href="/admin/messages/show/{{ message.id }}">{{ truncate(message.body, 80, '....') }}</a></td>
							<td><a href="javascript:void(0)" data-id="{{ message.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a></td>
						</tr>
					{% elsefor %}
					<tr>
						<td colspan="6">Belum ada data</td>
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
							<a href="/admin/messages/index/page={{ i }}">{{ i }}</a>
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