<table class="table table-striped">
	<thead>
		<tr>
			<th class="text-center" width="5%"><b>No</b></th>
			<th class="text-center"><b>Nama</b></th>
			<th class="text-center"><b>Nilai</b></th>
			<th class="text-center"><b>#</b></th>
		</tr>
	</thead>
	<tbody>
	{% for setting in settings %}
		<tr>
			<td class="text-right">{{ setting.rank }}</td>
			<td>{{ setting.name }}</a></td>
			<td>{{ setting.value }}</a></td>
			<td class="text-center">
				<a href="/admin/settings/update/{{ setting.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
			</td>
		</tr>
	{% elsefor %}
		<tr>
			<td colspan="4"><i>Belum ada setting</i></td>
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
			<a href="/admin/settings{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
			{% endif %}
		{% endfor %}
	</p>
</div>
{% endif %}
