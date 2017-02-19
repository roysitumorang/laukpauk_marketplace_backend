<table class="table table-striped">
	<thead>
		<tr>
			<th class="text-center" width="5%"><b>No</b></th>
			<th class="text-center"><b>Propinsi</b></th>
			<th class="text-center"><b>#</b></th>
		</tr>
	</thead>
	<tbody>
	{% for province in provinces %}
		<tr>
			<td class="text-right">{{ province.rank }}</td>
			<td>{{ province.name }}</td>
			<td class="text-center">
				<a href="/admin/provinces/update/{{ province.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
			</td>
		</tr>
	{% elsefor %}
		<tr>
			<td colspan="3"><i>Belum ada propinsi</i></td>
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
			<a href="/admin/provinces{% if i > 1 %}/index/page:{{ i }}{% endif %}">{{ i }}</a>
			{% endif %}
		{% endfor %}
	</p>
</div>
{% endif %}
