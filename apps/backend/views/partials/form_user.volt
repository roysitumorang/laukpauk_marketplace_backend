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
				Role (*) :<br>
				{% for role in roles %}
				<input type="radio" name="role_id" value="{{ role.id }}"{% if role.id == user.role_id %} checked{% endif %}> {{ role.name }}&nbsp;&nbsp;
				{% endfor %}
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
				Nama Toko (* untuk merchant) :<br>
				<input type="text" name="company" value="{{ user.company }}" size="50" placeholder="Nama toko">
			</td>
		</tr>
		<tr>
			<td>
				Premium Merchant (untuk merchant) :<br>
				<input type="checkbox" name="premium_merchant" value="1"{% if user.premium_merchant %} checked{% endif %}>
			</td>
		</tr>
		<tr>
			<td>
				Domain (untuk premium merchant) :<br>
				<input type="text" name="domain" value="{{ user.domain }}" size="50" placeholder="Domain">
			</td>
		</tr>
		<tr>
			<td>
				Profil Toko (* untuk premium merchant) :<br>
				<textarea name="company_profile" cols="50" rows="5" placeholder="Profil toko">{{ user.company_profile }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				Syarat dan Ketentuan (* untuk premium merchant) :<br>
				<textarea name="terms_conditions" cols="50" rows="5" placeholder="Syarat dan ketentuan">{{ user.terms_conditions }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				Minimal Order (untuk merchant) :<br>
				<input type="text" name="minimum_purchase" value="{{ user.minimum_purchase }}" size="20" placeholder="Minimal order">
			</td>
		</tr>
		<tr>
			<td>
				Biaya Administrasi (untuk merchant) :<br>
				<input type="text" name="admin_fee" value="{{ user.admin_fee }}" size="20" placeholder="Biaya administrasi">
			</td>
		</tr>
		<tr>
			<td>
				Catatan Penjual (untuk merchant) :<br>
				<textarea name="merchant_note" cols="50" rows="5" placeholder="Catatan penjual">{{ user.merchant_note }}</textarea>
			</td>
		</tr>
		{% if user.role.name == 'Merchant' %}
		<tr>
			<td>
				Deposit (untuk merchant) :<br>
				<input type="text" name="deposit" value="{{ user.deposit }}" size="20" placeholder="Deposit">
			</td>
		</tr>
		{% endif %}
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
				Email :<br>
				<input type="text" name="email" value="{{ user.email }}" size="50" placeholder="Email">
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
					<option value="{{ id }}"{% if user.village.subdistrict.city.province.id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kabupaten / Kota (*) :<br>
				<select name="city_id" id="city_id">
				{% for id, name in current_cities %}
					<option value="{{ id }}"{% if user.village.subdistrict.city.id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kecamatan (*) :<br>
				<select name="subdistrict_id" id="subdistrict_id">
				{% for id, name in current_subdistricts %}
					<option value="{{ id }}"{% if user.village.subdistrict.id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kelurahan (*) :<br>
				<select name="village_id" id="village_id">
				{% for id, name in current_villages %}
					<option value="{{ id }}"{% if user.village.id == id %} selected{% endif %}>{{ name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Hari Operasional (* untuk merchant) :<br>
				<input type="checkbox" name="open_on_sunday" value="1"{% if user.open_on_sunday %} checked{% endif %}> Minggu&nbsp;&nbsp;
				<input type="checkbox" name="open_on_monday" value="1"{% if user.open_on_monday %} checked{% endif %}> Senin&nbsp;&nbsp;
				<input type="checkbox" name="open_on_tuesday" value="1"{% if user.open_on_tuesday %} checked{% endif %}> Selasa&nbsp;&nbsp;
				<input type="checkbox" name="open_on_wednesday" value="1"{% if user.open_on_wednesday %} checked{% endif %}> Rabu&nbsp;&nbsp;
				<input type="checkbox" name="open_on_thursday" value="1"{% if user.open_on_thursday %} checked{% endif %}> Kamis&nbsp;&nbsp;
				<input type="checkbox" name="open_on_friday" value="1"{% if user.open_on_friday %} checked{% endif %}> Jumat&nbsp;&nbsp;
				<input type="checkbox" name="open_on_saturday" value="1"{% if user.open_on_saturday %} checked{% endif %}> Sabtu&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				Jam Operasional (* untuk merchant) :<br>
				Buka
				<select name="business_opening_hour">
					<option value="">-</option>
					{% for hour, label in business_hours %}
						<option value="{{ hour }}"{% if user.business_opening_hour == hour %} selected{% endif %}>{{ label }}</option>
					{% endfor %}
				</select>
				- Tutup
				<select name="business_closing_hour">
					<option value="">-</option>
					{% for hour, label in business_hours %}
						<option value="{{ hour }}"{% if user.business_closing_hour == hour %} selected{% endif %}>{{ label }}</option>
					{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Jam Pengantaran (* untuk merchant) :<br>
				{% for hour, label in business_hours %}
				<input type="checkbox" name="delivery_hours[]" value="{{ hour }}"{% if in_array(hour, user.delivery_hours) %} checked{% endif %}> {{ label }}&nbsp;&nbsp;
				{% endfor %}
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
			form.action = '/admin/users/' + avatar.dataset.id + '/update/delete_avatar:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>