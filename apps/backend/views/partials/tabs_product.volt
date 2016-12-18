<ul class="nav nav-tabs">
	<li{% if expand == 'product' %} class="active"{% endif %}><a {% if expand == 'product' %}href="#product" data-toggle="tab" aria-expanded="true"{% elseif product.id %}href="/admin/products/update/{{ product.id }}"{% endif %}>Produk</a></li>
	{% if product.id %}
	<li{% if expand == 'stock_units' %} class="active"{% endif %}><a {% if expand == 'stock_units' %}href="#stock_units" data-toggle="tab" aria-expanded="true"{% else %}href="/admin/product_stock_units/index/product_id:{{ product.id }}"{% endif %}>Satuan</a></li>
	{% endif %}
</ul>
