<ul class="nav nav-tabs">
	<li{% if active_tab == 'product' %} class="active"{% endif %}><a href="/admin/products/update/{{ product.id }}">Produk</a></li>
	{% if product.id %}
		<li{% if active_tab == 'accessors' %} class="active"{% endif %}><a href="/admin/product_accessors/index/product_id:{{ product.id }}">Akses Produk</a></li>
	{% endif %}
</ul>