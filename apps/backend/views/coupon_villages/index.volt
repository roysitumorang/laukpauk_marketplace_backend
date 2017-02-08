<section class="body">
	<!-- start: header -->
	{{ partial('partials/top_menu') }}
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		{{ partial('partials/left_side') }}
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<a href="/admin/coupons"><h2>Kupon Member</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Kupon</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">
					Kode Kupon: <strong>{{ coupon.code }}</strong>&nbsp;
					<img src="/assets/image/bullet-{% if coupon.status == 1 %}green{% else %}red{% endif %}.png" border="0">
				</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{{ partial('partials/tabs_coupon', ['coupon': coupon, 'expand': 'villages']) }}
				<form method="POST" action="/admin/coupon_villages/create/coupon_id:{{ coupon.id }}" id="new_village">
					<table class="table table-striped">
						<tr>
							<td>
								Kecamatan :
								<select name="subdistrict_id">
									{% for subdistrict in subdistricts %}
									<option value="{{ subdistrict.id }}"{% if subdistrict.id == current_subdistrict.id %} selected{% endif %}>{{ subdistrict.name }}</option>
									{% endfor %}
								</select>
								Kelurahan :
								<select name="village_id">
									{% for village in villages[subdistricts[0].id] %}
									<option value="{{ village.id }}">{{ village.name }}</option>
									{% endfor %}
								</select>
								<button type="submit" class="btn btn-info">SIMPAN</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25"><b>No</b></th>
							<th><b>Kecamatan</b></th>
							<th><b>Kelurahan</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for village in coupon_villages %}
						<tr>
							<td>{{ village.rank }}</td>
							<td>{{ village.subdistrict }}</td>
							<td>{{ village.name }}</td>
							<td><i class="fa fa-trash-o" data-coupon-id="{{ coupon.id }}" data-village-id="{{ village.id }}"></i></td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="3">Belum ada kelurahan</td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/coupon_villages/index/coupon_id:{{ coupon.id }}/page:{{ i }}">{{ i }}</a>
							{% endif %}
						{% endfor %}
					</p>
				</div>
				{% endif %}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	let villages = {{ villages | json_encode }}, subdistrict_id = document.querySelector('select[name=subdistrict_id]');
	subdistrict_id.onchange = () => {
		let options = [];
		villages[subdistrict_id.value].forEach(function(item) {
			options.push('<option value="' + item.id + '">' + item.name + '</option>');
		}),
		document.querySelector('select[name=village_id]').innerHTML = options.join('')
	}
	document.querySelectorAll('.fa-trash-o').forEach(function(item) {
		item.onclick = () => {
			if (confirm('Anda yakin ingin menghapus member ini ?')) {
				let form = document.createElement('form');
				form.method = 'POST',
				form.action = '/admin/coupon_villages/delete/' + item.dataset.villageId + '/coupon_id:' + item.dataset.couponId,
				document.body.appendChild(form),
				form.submit()
			}
		}
	})
</script>
