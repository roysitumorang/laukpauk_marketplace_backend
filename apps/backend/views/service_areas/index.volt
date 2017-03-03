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
				<a href="/admin/service_areas/index/user_id:{{ user.id }}"><h2>Area Operasional</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/show/{{ user.id }}">{{ user.name }}</a></span></li>
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
				{{ flashSession.output() }}
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'areas']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							<!-- Main Content //-->
							<form method="POST" action="/admin/service_areas/create/user_id:{{ user.id }}">
								<table class="table table-striped">
									<tr>
										<td class="text-right">
											<b>Propinsi :</b>&nbsp;&nbsp;
											<select id="province_id">
												{% for id, name in provinces %}
												<option value="{{ id }}">{{ name }}</option>
												{% endfor %}
											</select>
										</td>
										<td class="text-right">
											<b>Kabupaten / Kota :</b>&nbsp;&nbsp;
											<select id="city_id">
												{% for id, name in current_cities %}
												<option value="{{ id }}">{{ name }}</option>
												{% endfor %}
											</select>
										</td>
										<td class="text-right"></td>
									</tr>
									<tr>
										<td class="text-right">
											<b>Kecamatan :</b>&nbsp;&nbsp;
											<select id="subdistrict_id">
												{% for id, name in current_subdistricts %}
												<option value="{{ id }}">{{ name }}</option>
												{% endfor %}
											</select>
										</td>
										<td class="text-right">
											<b>Kelurahan :</b>&nbsp;&nbsp;
											<select name="village_id" id="village_id">
												{% for id, name in current_villages %}
												<option value="{{ id }}">{{ name }}</option>
												{% endfor %}
											</select>
										</td>
										<td class="text-right">
											<button type="submit" class="btn btn-info">TAMBAH</button>
										</td>
									</tr>
								</table>
							</form>
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="5%"><b>No</b></th>
										<th><b>Propinsi</b></th>
										<th><b>Kabupaten / Kota</b></th>
										<th><b>Kecamatan</b></th>
										<th><b>Kelurahan</b></th>
										<th><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for service_area in service_areas %}
									<tr>
										<td>{{ service_area.rank }}</td>
										<td>{{ service_area.province_name }}</td>
										<td>{{ service_area.city_name }}</td>
										<td>{{ service_area.subdistrict_name }}</td>
										<td>{{ service_area.village_name }}</td>
										<td><a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ service_area.village_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a></td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="6"><i>Belum ada area operasional</i></td>
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
										<a href="/admin/service_areas/index/user_id:{{ user.id }}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
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
		let current_cities = cities[province.value], new_options = '';
		for (let id in current_cities) {
			new_options += '<option value="' + id + '">' + current_cities[id] + '</option>'
		}
		city.innerHTML = new_options
	}
	city.onchange = () => {
		let current_subdistricts = subdistricts[city.value], new_options = '';
		for (let id in current_subdistricts) {
			new_options += '<option value="' + id + '">' + current_subdistricts[id] + '</option>'
		}
		subdistrict.innerHTML = new_options
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
				form.action = '/admin/service_areas/delete/' + item.dataset.id + '/user_id:' + item.dataset.userId,
				document.body.appendChild(form),
				form.submit()
			}
		}
	}
</script>