<ul class="nav nav-tabs">
	<li role="presentation"><a href="/admin/coupons">Daftar Kupon</a></li>
	<li role="presentation"{% if expand == 'detail' %} class="active"{% endif %}><a href="/admin/coupons/show/{{ coupon.id }}">Detail Kupon</a></li>
	<li role="presentation"{% if expand == 'users' %} class="active"{% endif %}><a href="/admin/coupon_users/index/coupon_id:{{ coupon.id }}">Berlaku Untuk Member</a></li>
	<li role="presentation"{% if expand == 'usages' %} class="active"{% endif %}><a href="/admin/coupon_usages/index/coupon_id:{{ coupon.id }}">Penggunaan</a></li>
</ul>
