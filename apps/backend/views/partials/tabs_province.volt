<ul class="nav nav-tabs">
	<li{% if active_tab == 'provinces' %} class="active"{% endif %}><a href="#provinces" data-toggle="tab" aria-active_tabed="true">Propinsi</a></li>
	{% if in_array(active_tab, ['cities', 'subdistricts', 'villages']) %}
		<li{% if active_tab == 'cities' %} class="active"{% endif %}><a href="#cities" data-toggle="tab" aria-active_tabed="true">Kabupaten / Kota</a></li>
		{% if in_array(active_tab, ['subdistricts', 'villages']) %}
			<li{% if active_tab == 'subdistricts' %} class="active"{% endif %}><a href="#subdistricts" data-toggle="tab" aria-active_tabed="true">Kecamatan</a></li>
			{% if active_tab == 'villages' %}
				<li class="active"><a href="#villages" data-toggle="tab" aria-active_tabed="true">Kelurahan</a></li>
			{% endif %}
		{% endif %}
	{% endif %}
</ul>