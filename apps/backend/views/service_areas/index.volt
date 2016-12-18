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
				<a href="/admin/users/update/{{ user.id }}"><h2>Update Member #{{ user.id }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/users">Daftar Member</a></span></li>
						<li><span><a href="/admin/users/update/{{ user.id }}">Update Member #{{ user.id }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Update Member #{{ user.id }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<div class="tabs">
					{{ partial('partials/tabs_user', ['user': user, 'expand': 'areas']) }}
					<div class="tab-content">
						<div id="areas" class="tab-pane active">
							<table class="table table-striped">
								<tr>
									<td>
										<!-- Main Content //-->
										<form method="POST" action="/admin/service_areas/create/user_id:{{ user.id }}">
											<b>Kecamatan :</b>&nbsp;&nbsp;
											<select id="subdistrict_id" class="form form-control form-20">
												{% for subdistrict in subdistricts %}
												<option value="{{ subdistrict.id }}">{{ subdistrict.name }}</option>
												{% endfor %}
											</select>&nbsp;&nbsp;
											<b>Kelurahan :</b>&nbsp;&nbsp;
											<select name="village_id" id="village_id" class="form form-control form-20">
												{% for village in current_villages %}
												<option value="{{ village.id }}">{{ village.name }}</option>
												{% endfor %}
											</select>&nbsp;&nbsp;
											<button type="submit" class="btn btn-info">TAMBAH</button>
										</form>
									</td>
								</tr>
							</table>
							<table class="table table-striped">
								<thead>
									<tr>
										<th width="5%"><b>No</b></th>
										<th><b>Kecamatan</b></th>
										<th><b>Kelurahan</b></th>
										<th><b>#</b></th>
									</tr>
								</thead>
								<tbody>
								{% for service_area in service_areas %}
									<tr>
										<td>{{ service_area.rank }}</td>
										<td>{{ service_area.subdistrict }}</td>
										<td>{{ service_area.village }}</td>
										<td><a href="javascript:void(0)" data-user-id="{{ user.id }}" data-id="{{ service_area.id }}" class="delete" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a></td>
									</tr>
								{% elsefor %}
									<tr>
										<td colspan="4"><i>Belum ada area operasional</i></td>
									</tr>
								{% endfor %}
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	var villages = {{ villages_json }}, subdistrict = document.getElementById('subdistrict_id'), village = document.getElementById('village_id'), items = document.querySelectorAll('.delete');
	subdistrict.onchange = function() {
		var current_villages = villages[this.value], new_options = '';
		for (var item in current_villages) {
			new_options += '<option value="' + current_villages[item].id + '">' + current_villages[item].name + '</option>';
		}
		village.innerHTML = new_options;
	}
	for (var i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin menghapus data ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/service_areas/delete/' + this.dataset.id + '/user_id:' + this.dataset.userId,
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>