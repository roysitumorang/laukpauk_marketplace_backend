<table class="table table-striped">
	<thead>
		<tr>
			<th class="text-center" width="5%"><b>No</b></th>
			<th class="text-center"><b>Kelurahan</b></th>
			<th class="text-center"><b>#</b></th>
		</tr>
	</thead>
	<tbody>
	{% for village in villages %}
		<tr>
			<td class="text-right">{{ village.rank }}</td>
			<td>{{ village.name }}</td>
			<td class="text-center">
				<a href="/admin/villages/update/{{ village.id }}/subdistrict_id:{{ subdistrict.id }}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}" title="Update"><i class="fa fa-pencil fa-2x"></i></a>
			</td>
		</tr>
	{% elsefor %}
		<tr>
			<td colspan="3"><i>Belum ada kelurahan</i></td>
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
			<a href="/admin/villages/index/subdistrict_id:{{ subdistrict.id }}{% if i > 1 %}/page:{{ i }}{% endif %}">{{ i }}</a>
			{% endif %}
		{% endfor %}
	</p>
</div>
{% endif %}
