<ul class="nav nav-tabs">
	<li{% if expand == 'group' %} class="active"{% endif %}><a href="/admin/groups">Group Produk</a></li>
	{% if group.id %}
		<li{% if expand == 'index' %} class="active"{% endif %}><a href="/admin/group_products/index/group_id:{{ group.id}}">Daftar Produk</a></li>
		<li{% if expand == 'create' %} class="active"{% endif %}><a href="/admin/group_products/create/group_id:{{ group.id}}">Tambah Produk Ke Group</a></li>
	{% endif %}
</ul>