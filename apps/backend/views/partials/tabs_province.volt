<ul class="nav nav-tabs">
	<li{% if active_tab == 'provinces' %} class="active"{% endif %}><a href="/admin/provinces">Propinsi</a></li>
	{% if in_array(active_tab, ['cities', 'subdistricts', 'villages']) %}
		<li{% if active_tab == 'cities' %} class="active"{% endif %}><a href="/admin/cities/index/province_id:{{ province.id }}">Kabupaten / Kota</a></li>
		{% if in_array(active_tab, ['subdistricts', 'villages']) %}
			<li{% if active_tab == 'subdistricts' %} class="active"{% endif %}><a href="/admin/subdistricts/index/city_id:{{ city.id }}">Kecamatan</a></li>
			{% if active_tab == 'villages' %}
				<li class="active"><a href="#villages">Kelurahan</a></li>
			{% endif %}
		{% endif %}
	{% endif %}
</ul>