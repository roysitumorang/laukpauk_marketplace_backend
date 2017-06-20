<ul class="nav nav-tabs">
	<li><a href="/admin/users">Daftar Member</a></li>
	<li{% if expand == 'profile' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}">Profil</a></li>
	{% if user.id and user.role.name == 'Merchant' %}
		<li{% if expand == 'areas' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/coverage_areas">Area Operasional</a></li>
		<li{% if expand == 'product_categories' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/product_categories">Kategori Produk</a></li>
		<li{% if expand == 'products' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/products">Daftar Produk</a></li>
	{% endif %}
</ul>
