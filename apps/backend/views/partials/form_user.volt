{{ flashSession.output() }}
<div class="tabs">
	{{ partial('partials/tabs_user', ['user': user, 'expand': 'profile']) }}
	<div class="tab-content">
		<div id="profile" class="tab-pane active">
			<form method="POST" action="{{ action }}" enctype="multipart/form-data">
				<table class="table table-striped">
					<tr>
						<td bgcolor="#e5f2ff">
							Password Baru (*) :<br>
							<input type="password" name="new_password" size="40" class="form form-control form-50">
							{% if user.id %}
							<br><i>Kosongkan jika Anda tidak ingin mengubah password lama</i>
							{% endif %}
						</td>
					</tr>
					<tr>
						<td bgcolor="#e5f2ff">
							Password Baru Sekali Lagi (*):<br>
							<input type="password" name="new_password_confirmation" size="40" class="form form-control form-50">
							{% if user.id %}
							<br><i>Kosongkan jika Anda tidak ingin mengubah password lama</i>
							{% endif %}
						</td>
					</tr>
					<tr>
						<td>
							Role (*) :<br>
							{% for role in roles %}
							<input type="radio" name="role_id" value="{{ role.id }}"{% if role.id == user.role_id %} checked{% endif %}> {{ role.name }}&nbsp;&nbsp;
							{% endfor %}
						</td>
					</tr>
					<tr>
						<td>
							Nama (*) :<br>
							<input type="text" name="name" value="{{ user.name }}" class="form form-control form-50" size="40">
						</td>
					</tr>
					<tr>
						<td>
							Nomor HP (*) :<br>
							<input type="text" name="mobile_phone" value="{{ user.mobile_phone }}" class="form form-control form-40" size="40">
						</td>
					</tr>
					<tr>
						<td>
							Jenis Kelamin :<br>
							{% for gender in genders %}
							<input type="radio" name="gender" value={{ gender }}{% if user.gender == gender %} checked{% endif %}> {{ gender }}&nbsp;&nbsp;
							{% endfor %}
						</td>
					</tr>
					<tr>
						<td>
							Tanggal Lahir :<br>
							<input type="text" name="date_of_birth" value="{{ user.date_of_birth }}" data-plugin-datepicker data-date-format="yyyy-mm-dd" class="form form-control form-30" size="12">
						</td>
					</tr>
					<tr>
						<td>
							Nama Toko (* untuk merchant) :<br>
							<input type="text" name="company" value="{{ user.company }}" class="form form-control form-50" size="50">
						</td>
					</tr>
					<tr>
						<td>
							Email :<br>
							<input type="text" name="email" value="{{ user.email }}" class="form form-control form-50" size="50">
						</td>
					</tr>
					<tr>
						<td>
							Alamat :<br>
							<textarea name="address" cols="50" rows="5" class="form form-control form-50">{{ user.address }}</textarea>
						</td>
					</tr>
					<tr>
						<td>
							Kecamatan (*) :<br>
							<select name="subdistrict_id" id="subdistrict_id">
							{% for subdistrict in subdistricts %}
								<option value="{{ subdistrict.id }}"{% if user.village.subdistrict.id == subdistrict.id %} selected{% endif %}>{{ subdistrict.name }}</option>
							{% endfor %}
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Kelurahan (*) :<br>
							<select name="village_id" id="village_id">
							{% for village in current_villages %}
								<option value="{{ village.id }}"{% if user.village.id == village.id %} selected{% endif %}>{{ village.name }}</option>
							{% endfor %}
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Hari Operasional (* untuk merchant) :<br>
							<input type="checkbox" name="open_on_sunday" value="1"{% if user.open_on_sunday %} checked{% endif %}> Minggu&nbsp;&nbsp;
							<input type="checkbox" name="open_on_monday" value="1"{% if user.open_on_monday %} checked{% endif %}> Senin&nbsp;&nbsp;
							<input type="checkbox" name="open_on_wednesday" value="1"{% if user.open_on_wednesday %} checked{% endif %}> Selasa&nbsp;&nbsp;
							<input type="checkbox" name="open_on_tuesday" value="1"{% if user.open_on_tuesday %} checked{% endif %}> Rabu&nbsp;&nbsp;
							<input type="checkbox" name="open_on_thursday" value="1"{% if user.open_on_thursday %} checked{% endif %}> Kamis&nbsp;&nbsp;
							<input type="checkbox" name="open_on_friday" value="1"{% if user.open_on_friday %} checked{% endif %}> Jumat&nbsp;&nbsp;
							<input type="checkbox" name="open_on_saturday" value="1"{% if user.open_on_saturday %} checked{% endif %}> Sabtu&nbsp;&nbsp;
						</td>
					</tr>
					<tr>
						<td>
							Jam Operasional (* untuk merchant) :<br>
							<input type="text" name="business_opening_hour" value="{{ user.business_opening_hour }}" class="form form-control form-20 text-center" size="5"> - <input type="text" name="business_closing_hour" value="{{ user.business_closing_hour }}" class="form form-control form-20 text-center" size="5">
						</td>
					</tr>
					{% if user.id %}
					<tr>
						<td>
							Registration Date:<br>
							{{ user.created_at }}
						</td>
					</tr>
					<tr>
						<td>
							Kode Aktivasi:<br>
							{{ user.activation_token }}
						</td>
					</tr>
					{% endif %}
					<tr>
						<td>
							Avatar:<br>
							<input type="file" name="new_avatar" size="30" class="form form-control form-30">
							{% if user.avatar %}
							<br><img src="/assets/image/{{ user.avatar }}" border="0"><br>
							<a href="javascript:void(0)" data-id="{{ user.id }}" class="main delete-avatar" title="Delete Avatar">Delete Avatar</a>
							{% endif %}
						</td>
					</tr>
					{% if user.activated_at %}
					<tr>
						<td>
							Tanggal Aktif:<br>
							{{ user.activated_at }}
						</td>
					</tr>
					{% endif %}
					{% if user.id %}
					<tr>
						<td>
							Registration IP:<br>
							{{ user.registration_ip }}
						</td>
					</tr>
					{% endif %}
					<tr>
						<td>
							<button type="submit" class="btn btn-info">SIMPAN</button>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</div>
<script>
	var villages = {{ villages_json }}, subdistrict = document.getElementById('subdistrict_id'), village = document.getElementById('village_id');
	subdistrict.onchange = function() {
		var current_villages = villages[this.value], new_options = '';
		for (var item in current_villages) {
			new_options += '<option value="' + current_villages[item].id + '">' + current_villages[item].name + '</option>';
		}
		village.innerHTML = new_options;
	}
	document.querySelector('.delete-avatar').onclick = function() {
		if (!confirm('Anda yakin menghapus gambar ini ?')) {
			return !1
		}
		var form = document.createElement('form');
		form.method = 'POST',
		form.action = '/admin/users/update/' + this.dataset.id + '/delete_avatar:1',
		document.body.appendChild(form),
		form.submit()
	}
</script>