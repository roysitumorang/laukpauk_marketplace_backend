<ul class="nav nav-tabs">
	<li{% if expand == 'provinces' %} class="active"{% endif %}><a href="#provinces" data-toggle="tab" aria-expanded="true">Propinsi</a></li>
	<li{% if expand == 'cities' %} class="active"{% endif %}><a href="#cities" data-toggle="tab" aria-expanded="true">Kabupaten / Kota</a></li>
	<li{% if expand == 'subdistricts' %} class="active"{% endif %}><a href="#subdistricts" data-toggle="tab" aria-expanded="true">Kecamatan</a></li>
	<li{% if expand == 'villages' %} class="active"{% endif %}><a href="#villages" data-toggle="tab" aria-expanded="true">Kelurahan</a></li>
</ul>