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
				<a href="/admin/payments{% if page.current > 1%}/index/page:{{ page.current }}{% endif %}"><h2>Daftar Pembayaran Deposit</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Daftar Pembayaran Deposit</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Daftar Pembayaran Deposit</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<div style="padding:10px;background:#e5f2ff;font-size:14px;color:#333333">
					<strong>Total Pembayaran:</strong>&nbsp;{{ total_payments }} /
					<font size="2">
						<strong>Menunggu konfirmasi:</strong>&nbsp;{{ total_pending_payments }} /
						<strong>Diterima:</strong>&nbsp;{{ total_approved_payments }} /
						<strong>Ditolak:</strong>&nbsp;{{ total_rejected_payments }}
					</font>
				</div>
				<table class="table table-striped">
					<tr>
						<td>
							<!-- Main Content //-->
							<form action="/admin/payments" method="GET" id="search">
								<b>Cari berdasarkan:</b>
								status
								<select name="status">
									<option value="">Semua Status</option>
									{% for value, label in all_status %}
									<option value="{{ value }}"{% if current_status === value %} selected{% endif %}>{{ label }}</option>
									{% endfor %}
								</select>&nbsp;&nbsp;
								<input type="text" name="keyword" value="{{ keyword }}" size="25" placeholder="Merchant / Kode Pembayaran">&nbsp;
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Cari</button>
							</form>
						</td>
					</tr>
				</table>
				<table class="table table-striped">
					<thead>
						<tr>
							<th class="text-center" width="5%">No</th>
							<th class="text-center">Kode Pembayaran</th>
							<th class="text-center">Pembayar</th>
							<th class="text-center">Rekening Asal Pembayaran</th>
							<th class="text-center">Rekening Tujuan Pembayaran</th>
							<th class="text-center">Jumlah Pembayaran</th>
							<th class="text-center">Tanggal Jam</th>
							<th class="text-center">Status Pembayaran</th>
							<th class="text-center" colspan="2">#</th>
						</tr>
					</thead>
					<tbody>
					{% for payment in payments %}
						<tr id="{{ payment.id }}">
							<td class="text-right">{{ payment.rank }}</td>
							<td>#{{ payment.code }}</td>
							<td><a href="/admin/users/{{ payment.user_id }}" target="_blank">{{ payment.user.company }}</a></td>
							<td>{{ payment.payer_bank }} / {{ payment.payer_account_number }}</td>
							<td>{{ payment.bankAccount.bank }} / {{ payment.bankAccount.number }}</td>
							<td>Rp. {{ payment.amount | number_format }}</td>
							<td class="text-nowrap">{{ payment.created_at }}</td>
							<td>
								{% if payment.status == 1 %}
									Diterima
								{% elseif payment.status == -1 %}
									Ditolak
								{% else %}
									Menunggu Konfirmasi
								{% endif %}
							</td>
							<td class="text-center">
								{% if payment.status == 0 %}
								<form method="POST" action="/admin/payments/{{ payment.id }}/approve">
									<input type="hidden" name="next" value="{{ next }}">
									<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i></button>
								</form>
								{% endif %}
							</td>
							<td class="text-center">
								{% if payment.status == 0 %}
								<form method="POST" action="/admin/payments/{{ payment.id }}/reject">
									<input type="hidden" name="next" value="{{ next }}">
									<button type="submit" class="btn btn-danger"><i class="fa fa-times"></i></button>
								</form>
								{% endif %}
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="8"><i>Belum ada nomor rekening</i></td>
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
							<a href="/admin/payments{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = '/admin/payments/index', replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.status.value) {
			url += '/status:' + search.status.value;
		}
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false)
</script>