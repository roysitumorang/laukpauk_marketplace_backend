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
				<a href="/admin/users/{{ user.id }}/coverage_areas{% if pagination.current > 1%}/index/page:{{ pagination.current }}{% endif %}"><h2>Area Operasional</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/{{ user.id }}">{{ user.name }}</a></span></li>
						<li><span>Area Operasional</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Area Operasional {{ user.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'areas']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							{{ flashSession.output() }}
							{% if provinces %}
								{{ form('/admin/users/' ~ user.id ~ '/coverage_areas/create') }}
									<table class="table table-striped">
										<tr>
											<td>
												<b>Propinsi :</b><br>
												{% if coverage_area.id %}
													{{ coverage_area.village.subdistrict.city.province.name }}
												{% else %}
													{{ select_static({'province_id', provinces, 'using': ['id', 'name'], 'useEmpty': true, 'emptyText': '- pilih -', 'value': province_id}) }}
												{% endif %}
												<br>
												<b>Kecamatan :</b><br>
												{% if coverage_area.id %}
													{{ coverage_area.village.subdistrict.name }}
												{% else %}
													{{ select_static({'subdistrict_id', subdistricts, 'useEmpty': true, 'emptyText': '- pilih -', 'value': subdistrict_id}) }}
												{% endif %}
											</td>
											<td>
												<b>Kab/Kota :</b><br>
												{% if coverage_area.id %}
													{{ coverage_area.village.subdistrict.city.name }}
												{% else %}
													{{ select_static({'city_id', cities, 'useEmpty': true, 'emptyText': '- pilih -', 'value': city_id}) }}
												{% endif %}
												<br>
												<b>Kelurahan :</b><br>
												{% if coverage_area.id %}
													{{ coverage_area.village.name }}
												{% else %}
													{{ select_static({'village_id', villages, 'value': coverage_area.village_id}) }}
												{% endif %}
											</td>
											<td>
												<b>Ongkos Kirim :</b><br>
												{{ text_field('shipping_cost', 'value': coverage_area.shipping_cost, 'placeholder': 'Ongkos Kirim') }}
											</td>
											<td class="text-right">
												<br>
												<button type="submit" class="btn btn-primary"><i class="fa fa-plus-square"></i> Tambah</button>
											</td>
										</tr>
									</table>
								{{ endForm() }}
							{% endif %}
							{% if coverage_areas %}
								<p style="margin-left:5px" class="text-right">
									<a type="button" href="/admin/users/{{ user.id }}/coverage_areas/update{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}" class="btn btn-primary"><i class="fa fa-pencil"></i> Update Ongkos Kirim</a>
								</p>
							{% endif %}
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="5%"><b>No</b></th>
										<th><b>Propinsi</b></th>
										<th><b>Kabupaten / Kota</b></th>
										<th><b>Kecamatan</b></th>
										<th><b>Kelurahan</b></th>
										<th><b>Ongkos Kirim</b></th>
										<th><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for coverage_area in coverage_areas %}
									<tr>
										<td>{{ coverage_area.rank }}</td>
										<td>{{ coverage_area.province_name }}</td>
										<td>{{ coverage_area.city_name }}</td>
										<td>{{ coverage_area.subdistrict_name }}</td>
										<td>{{ coverage_area.village_name }}</td>
										<td>{% if coverage_area.shipping_cost %}Rp. {{ coverage_area.shipping_cost | number_format(0, ',', '.') }}{% else %}-{% endif %}</td>
										<td>
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ coverage_area.village_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="7"><i>Belum ada data</i></td>
									</tr>
								{% endfor %}
								</tbody>
							</table>
							{% if pagination.last > 1 %}
							<div class="weepaging">
								<p>
									<b>Halaman:</b>&nbsp;&nbsp;
									{% for i in pages %}
										{% if i == pagination.current %}
											<b>{{ i }}</b>
										{% else %}
											<a href="/admin/users/{{ user.id }}/coverage_areas{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
										{% endif %}
									{% endfor %}
								</p>
							</div>
							{% endif %}
						</div>
					</div>
				</div>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	let province = document.querySelector('#province_id'), city = document.querySelector('#city_id'), subdistrict = document.querySelector('#subdistrict_id'), village = document.querySelector('#village_id');
	if (province) {
		province.onchange = event => {
			let value = event.target.value;
			city.options.length = 1,
			subdistrict.options.length = 1,
			village.options.length = 0,
			value && fetch('/admin/users/{{ user.id }}/coverage_areas/cities/' + value, { credentials: 'include' }).then(response => response.json()).then(items => {
				items.forEach(item => {
					let option = document.createElement('option');
					option.value = item.id,
					option.appendChild(document.createTextNode(item.name)),
					city.appendChild(option)
				})
			})
		},
		city.onchange = event => {
			let value = event.target.value;
			subdistrict.options.length = 1,
			village.options.length = 0,
			value && fetch('/admin/users/{{ user.id }}/coverage_areas/subdistricts/' + value, { credentials: 'include' }).then(response => response.json()).then(items => {
				items.forEach(item => {
					let option = document.createElement('option');
					option.value = item.id,
					option.appendChild(document.createTextNode(item.name)),
					subdistrict.appendChild(option)
				})
			})
		},
		subdistrict.onchange = event => {
			let value = event.target.value;
			village.options.length = 0,
			value && fetch('/admin/users/{{ user.id }}/coverage_areas/villages/' + value, { credentials: 'include' }).then(response => response.json()).then(items => {
				items.forEach(item => {
					let option = document.createElement('option');
					option.value = item.id,
					option.appendChild(document.createTextNode(item.name)),
					village.appendChild(option)
				})
			})
		}
	}
	document.querySelectorAll('.delete').forEach(item => {
		item.onclick = event => {
			if (confirm('Anda yakin menghapus data ini ?')) {
				let form = document.createElement('form'), dataset = event.target.parentNode.dataset;
				form.method = 'POST',
				form.action = '/admin/users/' + dataset.userId + '/coverage_areas/' + dataset.id + '/delete?next={{ request.get('_url') }}',
				document.body.appendChild(form),
				form.submit()
			}
		}
	})
</script>