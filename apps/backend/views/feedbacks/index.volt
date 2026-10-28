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
				<a href="/admin/feedbacks"><h2>Feedbacks</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Feedbacks</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Feedbacks</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<!-- Main Content //-->
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="5%"><b>No</b></th>
							<th><b>Dari</b></th>
							<th><b>Tanggal</b></th>
							<th><b>Subject</b></th>
						</tr>
					</thead>
					<tbody>
					{% for feedback in feedbacks %}
						<tr>
							<td>{{ feedback.rank }}</td>
							<td>{{ feedback.user.mobile_phone }} / {{ feedback.user.name }}</td>
							<td>{{ feedback.created_at }}</td>
							<td>{{ feedback.content }}</td>
						</tr>
					{% elsefor %}
					<tr>
						<td colspan="4">Belum ada data</td>
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
							<a href="/admin/feedbacks/index/page:{{ i }}">{{ i }}</a>
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