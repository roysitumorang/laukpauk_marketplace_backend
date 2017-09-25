<ul class="nav nav-tabs">
	<li{% if expand == 'users' %} class="active"{% endif %}><a href="/admin/users">Daftar Member</a></li>
	<li{% if expand == 'create_user' %} class="active"{% endif %}><a href="/admin/users/create">Tambah Member</a></li>
	{% if user.id %}
		<li{% if expand == 'show_user' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}">Profil</a></li>
		<li{% if expand == 'update_user' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/update">Edit Profil</a></li>
		{% if user.role.name == 'Merchant' %}
			<li{% if expand == 'areas' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/coverage_areas">Area Operasional</a></li>
			<li{% if expand == 'products' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/products">Daftar Produk</a></li>
			<li{% if expand == 'sale_packages' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/sale_packages">Daftar Paket Penjualan</a></li>
		{% endif %}
	{% endif %}
</ul>
