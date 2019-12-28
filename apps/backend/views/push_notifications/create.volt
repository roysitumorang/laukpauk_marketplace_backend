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
				<a href="/admin/push_notifications"><h2>Kirim Notifikasi</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/push_notifications">Push Notifikasi</a></span></li>
						<li><span>Kirim Notifikasi</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Kirim Notifikasi</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{{ form('/admin/push_notifications/create', 'method': 'POST', 'enctype': 'multipart/form-data') }}
					<table class="table table-striped">
						<tr>
							<td>
								<b>Dari :</b><br>
								{{ current_user.name }}
							</td>
						</tr>
						<tr>
							<td>
								<b>Kepada :</b><br>
								Role
								<select name="role_id" id="role_id">
									<option value="">Merchant + Buyer</option>
									{% for role in roles %}
										<option value="{{ role.id }}"{% if role.id == role_id %} selected{% endif %}>{{ role.name }} aja</option>
									{% endfor %}
								</select>
								Member
								{{ select({'user_id', recipients, 'using': ['id', 'label'], 'value': user_id, 'useEmpty': true, 'emptyText': '- Semua Member -'}) }}
							</td>
						</tr>
						<tr>
							<td>
								<b>Judul :</b><br>
								<input type="text" name="title" value="{{ notification.title }}" size="50" maxlength="200">
							</td>
						</tr>
						<tr>
							<td>
								<b>Gambar :</b><br>
								{{ file_field('new_image') }}
							</td>
						</tr>
						<tr>
							<td>
								<b>Pesan :</b><br>
								<textarea name="message" cols="80" rows="10" maxlength="1024">{{ notification.message }}</textarea>
							</td>
						</tr>
						<tr>
							<td>
								<a type="button" class="btn btn-default" href="/admin/push_notifications"><i class="fa fa-chevron-left"></i> Kembali</a>
								<button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Kirim</button>
							</td>
						</tr>
					</table>
				{{ endForm() }}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	document.querySelector('#role_id').onchange = event => {
		let value = event.target.value, target = document.querySelector('#user_id');
		target.options.length = 1,
		fetch('/admin/push_notifications/recipients' + (value ? '/role_id=' + value : ''), { credentials: 'include' }).then(response => response.json()).then(payload => {
			payload.forEach(function(item) {
				let option = document.createElement('option');
				option.value = item.id,
				option.appendChild(document.createTextNode(item.label)),
				target.appendChild(option)
			})
		})
	}
</script>