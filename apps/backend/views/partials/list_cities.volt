<table class="table table-striped">
	<thead>
		<tr>
			<th class="text-center" width="5%"><b>No</b></th>
			<th class="text-center"><b>Kabupaten / Kota</b></th>
			<th class="text-center"><b>#</b></th>
		</tr>
	</thead>
	<tbody>
	{% for city in cities %}
		<tr>
			<td class="text-right">{{ city.rank }}</td>
			<td><a href="/admin/subdistricts/index/city_id:{{ city.id }}">{{ city.type }} {{ city.name }}</a></td>
			<td class="text-center">
				<a href="/admin/cities/update/{{ city.id }}/province_id:{{ province.id }}{% if pagination.current > 1 %}/page:{{ pagination.current }}{% endif %}" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
			</td>
		</tr>
	{% elsefor %}
		<tr>
			<td colspan="3"><i>Belum ada kabupaten / kota</i></td>
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
				<a href="/admin/cities/index/province_id:{{ province.id }}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
			{% endif %}
		{% endfor %}
	</p>
</div>
{% endif %}