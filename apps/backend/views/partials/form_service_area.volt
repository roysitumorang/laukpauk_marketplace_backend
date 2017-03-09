{{ flashSession.output() }}
<form method="POST" action="{{ action }}{% if page.current > 1 %}?page={{ page.current }}{% endif %}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Propinsi :</b><br>
				{% if service_area.id %}
				{{ service_area.village.subdistrict.city.province.name }}
				{% else %}
				<select id="province_id">
					{% for id, name in provinces %}
					<option value="{{ id }}"{% if id == service_area.village.subdistrict.city.province.id %} selected{% endif %}>{{ name }}</option>
					{% endfor %}
				</select>
				{% endif %}
			</td>
			<td>
				<b>Kabupaten / Kota :</b><br>
				{% if service_area.id %}
				{{ service_area.village.subdistrict.city.name }}
				{% else %}
				<select id="city_id">
					{% for id, name in current_cities %}
					<option value="{{ id }}"{% if id == service_area.village.subdistrict.city.id %} selected{% endif %}>{{ name }}</option>
					{% endfor %}
				</select>
				{% endif %}
			</td>
			<td>
				<b>Kecamatan :</b><br>
				{% if service_area.id %}
				{{ service_area.village.subdistrict.name }}
				{% else %}
				<select id="subdistrict_id">
					{% for id, name in current_subdistricts %}
					<option value="{{ id }}"{% if id == service_area.village.subdistrict.id %} selected{% endif %}>{{ name }}</option>
					{% endfor %}
				</select>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				<b>Kelurahan :</b><br>
				{% if service_area.id %}
				{{ service_area.village.name }}
				{% else %}
				<select name="village_id" id="village_id">
					{% for id, name in current_villages %}
					<option value="{{ id }}"{% if id == service_area.village.id %} selected{% endif %}>{{ name }}</option>
					{% endfor %}
				</select>
				{% endif %}
			</td>
			<td>
				<b>Minimum Order :</b><br>
				<input type="text" name="minimum_purchase" value="{{ service_area.minimum_purchase }}">
			</td>
			<td>
				<br>
				<button type="submit" class="btn btn-info">SIMPAN</button>
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
			<th><b>Minimum Order</b></th>
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
			<td>{% if service_area.minimum_purchase %}Rp. {{ number_format(service_area.minimum_purchase, 0, ',', '.') }}{% else %}-{% endif %}</td>
			<td>
				<a href="/admin/users/{{ user.id }}/service_areas/{{ service_area.village_id }}/update" title="Edit"><i class="fa fa-pencil fa-2x"></i></a>
				<a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ service_area.village_id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
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
			<a href="/admin/users/{{ user.id }}/service_areas{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
			{% endif %}
		{% endfor %}
	</p>
</div>
{% endif %}
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
				form.action = '/admin/users/' + item.dataset.userId + '/service_areas/' + item.dataset.id + '/delete',
				document.body.appendChild(form),
				form.submit()
			}
		}
	}
</script>