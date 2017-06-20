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
				<a href="/admin/users/{{ user.id }}/coverage_areas{% if page.current > 1%}/index/page:{{ page.current }}{% endif %}"><h2>Area Operasional</h2></a>
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
							<form method="POST" action="/admin/users/{{ user.id }}/coverage_areas/create">
								<table class="table table-striped">
									<tr>
										<td>
											<b>Propinsi :</b><br>
											{% if coverage_area.id %}
											{{ coverage_area.village.subdistrict.city.province.name }}
											{% else %}
											<select id="province_id">
												{% for id, name in provinces %}
												<option value="{{ id }}"{% if id == coverage_area.village.subdistrict.city.province.id %} selected{% endif %}>{{ name }}</option>
												{% endfor %}
											</select>
											{% endif %}
										</td>
										<td>
											<b>Kabupaten / Kota :</b><br>
											{% if coverage_area.id %}
											{{ coverage_area.village.subdistrict.city.name }}
											{% else %}
											<select id="city_id">
												{% for id, name in current_cities %}
												<option value="{{ id }}"{% if id == coverage_area.village.subdistrict.city.id %} selected{% endif %}>{{ name }}</option>
												{% endfor %}
											</select>
											{% endif %}
										</td>
										<td>
											<b>Kecamatan :</b><br>
											{% if coverage_area.id %}
											{{ coverage_area.village.subdistrict.name }}
											{% else %}
											<select id="subdistrict_id">
												{% for id, name in current_subdistricts %}
												<option value="{{ id }}"{% if id == coverage_area.village.subdistrict.id %} selected{% endif %}>{{ name }}</option>
												{% endfor %}
											</select>
											{% endif %}
										</td>
									</tr>
									<tr>
										<td>
											<b>Kelurahan :</b><br>
											{% if coverage_area.id %}
											{{ coverage_area.village.name }}
											{% else %}
											<select name="village_id" id="village_id">
												{% for id, name in current_villages %}
												<option value="{{ id }}"{% if id == coverage_area.village.id %} selected{% endif %}>{{ name }}</option>
												{% endfor %}
											</select>
											{% endif %}
										</td>
										<td>
											<b>Minimum Order :</b><br>
											<input type="text" name="minimum_purchase" value="{{ coverage_area.minimum_purchase }}">
										</td>
										<td class="text-right">
											<br>
											<button type="submit" class="btn btn-info"><i class="fa fa-plus-square"></i> Tambah</button>
										</td>
									</tr>
								</table>
							</form>
							{% if coverage_areas %}
							<p style="margin-left:5px" class="text-right">
								<a type="button" href="/admin/users/{{ user.id }}/coverage_areas/update{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" class="btn btn-info"><i class="fa fa-pencil"></i> Update Minimum Order</a>
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
										<th><b>Minimum Order</b></th>
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
										<td>{% if coverage_area.minimum_purchase %}Rp. {{ number_format(coverage_area.minimum_purchase, 0, ',', '.') }}{% else %}-{% endif %}</td>
										<td>
											<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ coverage_area.village_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
										</td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="7"><i>Belum ada area operasional</i></td>
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
	let cities = {{ cities | json_encode }}, subdistricts = {{ subdistricts | json_encode }}, villages = {{ villages | json_encode }}, province = document.getElementById('province_id'), city = document.getElementById('city_id'), subdistrict = document.getElementById('subdistrict_id'), village = document.getElementById('village_id'), items = document.querySelectorAll('.delete');
	province.onchange = () => {
		let current_cities = cities[province.value], city_id = Object.keys(current_cities)[0], current_subdistricts = subdistricts[city_id], subdistrict_id = Object.keys(current_subdistricts)[0], current_villages = villages[subdistrict_id], new_cities = '', new_subdistricts = '', new_villages = '';
		for (let id in current_cities) {
			new_cities += '<option value="' + id + '">' + current_cities[id] + '</option>'
		}
		for (let id in current_subdistricts) {
			new_subdistricts += '<option value="' + id + '">' + current_subdistricts[id] + '</option>'
		}
		for (let id in current_villages) {
			new_villages += '<option value="' + id + '">' + current_villages[id] + '</option>'
		}
		city.innerHTML = new_cities, subdistrict.innerHTML = new_subdistricts, village.innerHTML = new_villages
	}
	city.onchange = () => {
		let current_subdistricts = subdistricts[city.value], subdistrict_id = Object.keys(current_subdistricts)[0], current_villages = villages[subdistrict_id], new_subdistricts = '', new_villages = '';
		for (let id in current_subdistricts) {
			new_subdistricts += '<option value="' + id + '">' + current_subdistricts[id] + '</option>'
		}
		for (let id in current_villages) {
			new_villages += '<option value="' + id + '">' + current_villages[id] + '</option>'
		}
		subdistrict.innerHTML = new_subdistricts, village.innerHTML = new_villages
	}
	subdistrict.onchange = () => {
		let current_villages = villages[subdistrict.value], new_options = '';
		for (let id in current_villages) {
			new_options += '<option value="' + id + '">' + current_villages[id] + '</option>'
		}
		village.innerHTML = new_options
	}
	for (let i = items.length; i--; ) {
		let item = items[i];
		item.onclick = () => {
			if (confirm('Anda yakin menghapus data ini ?')) {
				let form = document.createElement('form');
				form.method = 'POST',
				form.action = '/admin/users/' + item.dataset.userId + '/coverage_areas/' + item.dataset.id + '/delete{% if page.current > 1%}?page={{ page.current }}{% endif %}',
				document.body.appendChild(form),
				form.submit()
			}
		}
	}
</script>