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
				<a href="/admin/bank_accounts{% if pagination.current > 1%}/index/page:{{ pagination.current }}{% endif %}"><h2>Daftar Rekening Bank</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Rekening Bank</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Rekening Bank</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/bank_accounts/create" class="new">Tambah Rekening Bank</a></p>
				<table class="table table-striped">
					<thead>
						<tr>
							<th class="text-center" width="5%"><b>No</b></th>
							<th class="text-center"><b>Bank</b></th>
							<th class="text-center"><b>Nomor Rekening</b></th>
							<th class="text-center"><b>Atas Nama</b></th>
							<th class="text-center"><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for bank_account in bank_accounts %}
						<tr id="{{ bank_account.id }}">
							<td class="text-right">{{ bank_account.rank }}</td>
							<td>{{ bank_account.bank }}</td>
							<td>{{ bank_account.number }}</td>
							<td>{{ bank_account.holder }}</td>
							<td class="text-center">
								<a href="javascript:void(0)" class="published" data-id="{{ bank_account.id }}">
									{% if bank_account.published %}
										<i class="fa fa-eye fa-2x"></i>
									{% else %}
										<font color="#FF0000"><i class="fa fa-eye-slash fa-2x"></i></font>
									{% endif %}
								</a>
								<a href="/admin/bank_accounts/{{ bank_account.id }}/update" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="5"><i>Belum ada nomor rekening</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if pagination.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == pagination.current %}
								<b>{{ i }}</b>
							{% else %}
								<a href="/admin/bank_accounts{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
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
	document.querySelectorAll('.published').forEach(item => {
		item.addEventListener('click', event => {
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/bank_accounts/' + item.dataset.id + '/toggle_status' + '?next=' + window.location.href.split('#')[0] + '#' + item.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}, false)
	})
</script>