<ul class="nav nav-tabs">
	<li><a href="/admin/products">Daftar Produk</a></li>
	<li{% if active_tab == 'product' %} class="active"{% endif %}><a href="/admin/products/{{ product.id }}/update">Produk</a></li>
	{% if product.id %}
		<li{% if active_tab == 'merchants' %} class="active"{% endif %}><a href="/admin/products/{{ product.id }}/merchants">Daftar Merchant</a></li>
	{% endif %}
</ul>