<ul class="nav nav-tabs">
	<li><a href="/admin/users">Daftar Member</a></li>
	<li{% if expand == 'profile' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}">Profil</a></li>
	{% if user.id and user.role.name == 'Merchant' %}
		<li{% if expand == 'areas' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/service_areas">Area Operasional</a></li>
		<li{% if expand == 'products' %} class="active"{% endif %}><a href="/admin/users/{{ user.id }}/store_items">Produk</a></li>
	{% endif %}
</ul>