<ul class="nav nav-tabs">
	<li{% if expand == 'profile' %} class="active"{% endif %}><a {% if expand == 'profile' %}href="#profile" data-toggle="tab" aria-expanded="true"{% elseif user.id %}href="/admin/users/update/{{ user.id }}"{% endif %}>Profile</a></li>
	{% if user.id and user.role.name == 'Buyer' %}
	<li{% if expand == 'areas' %} class="active"{% endif %}><a {% if expand == 'areas' %}href="#areas" data-toggle="tab" aria-expanded="true"{% else %}href="/admin/service_areas/index/user_id:{{ user.id }}"{% endif %}>Area Operasional</a></li>
	<li{% if expand == 'products' %} class="active"{% endif %}><a {% if expand == 'products' %}href="#products" data-toggle="tab" aria-expanded="true"{% else %}href="/admin/product_prices/index/user_id:id{{ user.id }}"{% endif %}>Produk</a></li>
	{% endif %}
</ul>