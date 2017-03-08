<ul class="nav nav-tabs">
	<li{% if active_tab == 'product' %} class="active"{% endif %}><a href="/admin/products/update/{{ product.id }}">Produk</a></li>
	{% if product.id %}
		<li{% if active_tab == 'linked_products' %} class="active"{% endif %}><a href="/admin/product_links/index/product_id:{{ product.id }}">Produk Terkait</a></li>
	{% endif %}
</ul>