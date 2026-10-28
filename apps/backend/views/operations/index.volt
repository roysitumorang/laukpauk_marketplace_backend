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
				<a href="/admin/operations"><h2>Hari Jam Operasional</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Hari Jam Operasional</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Hari Jam Operasional</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="GET" action="/admin/operations/index" id="search">
					<table class="table table-striped">
						<tr>
							<td>
								<input type="text" name="keyword" value="{{ keyword }}" placeholder="Merchant">
								<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> CARI</button>
								<strong>{{ page.total_items }} Merchant</strong>
							</td>
						</tr>
					</table>
				</form>
				<form method="POST" action="/admin/operations/update_all{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
					<table class="table table-striped">
						<tr>
							<td>
								<input type="checkbox" name="open_on_sunday" value="1"{% if merchant.open_on_sunday %} checked{% endif %}> Minggu
								<input type="checkbox" name="open_on_monday" value="1"{% if merchant.open_on_monday %} checked{% endif %}> Senin
								<input type="checkbox" name="open_on_tuesday" value="1"{% if merchant.open_on_tuesday %} checked{% endif %}> Selasa
								<input type="checkbox" name="open_on_wednesday" value="1"{% if merchant.open_on_wednesday %} checked{% endif %}> Rabu
								<input type="checkbox" name="open_on_thursday" value="1"{% if merchant.open_on_thursday %} checked{% endif %}> Kamis
								<input type="checkbox" name="open_on_friday" value="1"{% if merchant.open_on_friday %} checked{% endif %}> Jumat
								<input type="checkbox" name="open_on_saturday" value="1"{% if merchant.open_on_saturday %} checked{% endif %}> Sabtu
								<button type="submit" class="btn btn-danger"><i class="fa fa-exclamation-triangle"></i> SIMPAN UNTUK SEMUA MERCHANT</button>
							</td>
						</tr>
					</table>
				</form>
				<form method="POST" action="/admin/operations/update{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}">
					<table class="table table-striped">
						<thead>
							<tr>
								<th width="25" class="text-center">No</th>
								<th class="text-center">Nama</th>
								<th class="text-center">Operasional</th>
							</tr>
						</thead>
						<tbody>
						{% for merchant in merchants %}
							<tr>
								<td class="text-right">{{ merchant.rank }}</td>
								<td>{{ merchant.company }}</td>
								<td>
									Hari :
									<br>
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_sunday]" value="1"{% if merchant.open_on_sunday %} checked{% endif %}> Minggu
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_monday]" value="1"{% if merchant.open_on_monday %} checked{% endif %}> Senin
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_tuesday]" value="1"{% if merchant.open_on_tuesday %} checked{% endif %}> Selasa
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_wednesday]" value="1"{% if merchant.open_on_wednesday %} checked{% endif %}> Rabu
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_thursday]" value="1"{% if merchant.open_on_thursday %} checked{% endif %}> Kamis
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_friday]" value="1"{% if merchant.open_on_friday %} checked{% endif %}> Jumat
									<input type="checkbox" name="merchants[{{ merchant.id }}][open_on_saturday]" value="1"{% if merchant.open_on_saturday %} checked{% endif %}> Sabtu
									<br>
									Jam :
									<br>
									Buka
									<select name="merchants[{{ merchant.id }}][business_opening_hour]">
										<option value="">-</option>
										{% for hour, label in business_hours %}
											<option value="{{ hour }}"{% if merchant.business_opening_hour == hour %} selected{% endif %}>{{ label }}</option>
										{% endfor %}
									</select>
									- Tutup
									<select name="merchants[{{ merchant.id }}][business_closing_hour]">
										<option value="">-</option>
										{% for hour, label in business_hours %}
											<option value="{{ hour }}"{% if merchant.business_closing_hour == hour %} selected{% endif %}>{{ label }}</option>
										{% endfor %}
									</select>
									<br>
									Pengantaran :
									<br>
									{% for hour, label in business_hours %}
									<input type="checkbox" name="merchants[{{ merchant.id }}][delivery_hours][]" value="{{ hour }}"{% if in_array(hour, merchant.delivery_hours) %} checked{% endif %}> {{ label }}&nbsp;&nbsp;
									{% endfor %}
									<br>
									Minimal Order :
									<br>
									<input type="text" name="merchants[{{ merchant.id }}][minimum_purchase]" value="{{ merchant.minimum_purchase }}">
									<br>
									Alamat :
									<br>
									<input type="text" name="merchants[{{ merchant.id }}][address]" value="{{ merchant.address }}">
								</td>
							</tr>
						{% elsefor %}
							<tr>
								<td colspan="3"><i>Belum ada produk</i></td>
							</tr>
						{% endfor %}
						</tbody>
					</table>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
				</form>
				{% if page.last > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/operations/index{% if keyword %}/keyword:{{ keyword }}{% endif %}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
	let search = document.getElementById('search'), url = search.action, replacement = {' ': '+', ':': '', '\/': ''};
	search.addEventListener('submit', event => {
		event.preventDefault();
		if (search.keyword.value) {
			url += '/keyword:' + search.keyword.value.trim().replace(/ |:|\//g, match => {
				return replacement[match];
			});
		}
		location.href = url;
	}, false);
</script>