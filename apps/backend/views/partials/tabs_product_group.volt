<ul class="nav nav-tabs">
	<li{% if expand == 'group' %} class="active"{% endif %}><a href="/admin/product_groups">Group Produk</a></li>
	{% if product_group.id %}
		<li{% if expand == 'index' %} class="active"{% endif %}><a href="/admin/product_group_members/index/product_group_id:{{ product_group.id}}">Daftar Produk</a></li>
		<li{% if expand == 'create' %} class="active"{% endif %}><a href="/admin/product_group_members/create/product_group_id:{{ product_group.id}}">Tambah Produk Ke Group</a></li>
	{% endif %}
</ul>