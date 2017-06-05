<ul class="nav nav-tabs">
	<li role="presentation"><a href="/admin/coupons">Daftar Kupon</a></li>
	<li role="presentation"{% if expand == 'detail' %} class="active"{% endif %}><a href="/admin/coupons/{{ coupon.id }}">Detail Kupon</a></li>
	<li role="presentation"{% if expand == 'usages' %} class="active"{% endif %}><a href="/admin/coupons/{{ coupon.id }}/usages">Penggunaan</a></li>
</ul>
