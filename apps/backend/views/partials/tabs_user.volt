<ul class="nav nav-tabs">
	<li{% if expand == 'profile' %} class="active"{% endif %}><a href="/admin/users/show/{{ user.id }}">Profile</a></li>
	{% if user.id and user.role.name == 'Merchant' %}
		<li{% if expand == 'areas' %} class="active"{% endif %}><a href="/admin/service_areas/index/user_id:{{ user.id }}">Area Operasional</a></li>
		<li{% if expand == 'products' %} class="active"{% endif %}><a href="/admin/store_items/index/user_id:{{ user.id }}">Produk</a></li>
	{% endif %}
</ul>