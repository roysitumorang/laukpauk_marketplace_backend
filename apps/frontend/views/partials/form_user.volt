{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td bgcolor="#e5f2ff">
				Password Baru (*) :<br>
				<input type="password" name="new_password" size="20" placeholder="Password baru">
				{% if user.id %}
				<br><i>Kosongkan jika Anda tidak ingin mengubah password lama</i>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td bgcolor="#e5f2ff">
				Password Baru Sekali Lagi (*):<br>
				<input type="password" name="new_password_confirmation" size="20" placeholder="Password baru sekali lagi">
				{% if user.id %}
				<br><i>Kosongkan jika Anda tidak ingin mengubah password lama</i>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				Nama (*) :<br>
				<input type="text" name="name" value="{{ user.name }}" size="50" placeholder="Nama">
			</td>
		</tr>
		<tr>
			<td>
				Nomor HP (*) :<br>
				<input type="text" name="mobile_phone" value="{{ user.mobile_phone }}" size="15" placeholder="Nomor HP">
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
				<input type="text" name="date_of_birth" value="{{ user.date_of_birth }}" data-plugin-datepicker data-date-format="yyyy-mm-dd" size="10" placeholder="Tanggal lahir">
			</td>
		</tr>
		<tr>
			<td>
				Alamat :<br>
				<textarea name="address" cols="50" rows="5" placeholder="Alamat">{{ user.address }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				Propinsi (*) :<br>
				<select name="province_id" id="province_id">
				{% for id, name in provinces %}
					<option value="{{ id }}"{% if user.village.subdistrict.city.province_id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kabupaten / Kota (*) :<br>
				<select name="city_id" id="city_id">
				{% for id, name in current_cities %}
					<option value="{{ id }}"{% if user.village.subdistrict.city_id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kecamatan (*) :<br>
				<select name="subdistrict_id" id="subdistrict_id">
				{% for id, name in current_subdistricts %}
					<option value="{{ id }}"{% if user.village.subdistrict_id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kelurahan (*) :<br>
				<select name="village_id" id="village_id">
				{% for id, name in current_villages %}
					<option value="{{ id }}"{% if user.village_id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
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
				<input type="file" name="new_avatar" size="30">
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
<script>
	let cities = {{ cities | json_encode }}, subdistricts = {{ subdistricts | json_encode }}, villages = {{ villages | json_encode }}, province = document.getElementById('province_id'), city = document.getElementById('city_id'), subdistrict = document.getElementById('subdistrict_id'), village = document.getElementById('village_id'), avatar = document.querySelector('.delete-avatar');
	province.onchange = () => {
		let current_cities = cities[province.value], new_options = '';
		for (var i in current_cities) {
			new_options += '<option value="' + i + '">' + current_cities[i] + '</option>'
		}
		city.innerHTML = new_options
	}
	city.onchange = () => {
		let current_subdistricts = subdistricts[city.value], new_options = '';
		for (var i in current_subdistricts) {
			new_options += '<option value="' + i + '">' + current_subdistricts[i] + '</option>'
		}
		subdistrict.innerHTML = new_options
	}
	subdistrict.onchange = () => {
		let current_villages = villages[subdistrict.value], new_options = '';
		for (var i in current_villages) {
			new_options += '<option value="' + i + '">' + current_villages[i] + '</option>'
		}
		village.innerHTML = new_options
	}
	avatar.onclick = () => {
		if (confirm('Anda yakin menghapus gambar ini ?')) {
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = '/users/' + avatar.dataset.id + '/update/delete_avatar:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>