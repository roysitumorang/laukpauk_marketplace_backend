{{ flashSession.output() }}
{{ form(action, 'enctype': 'multipart/form-data') }}
	<table class="table table-striped">
		<tr>
			<td bgcolor="#e5f2ff">
				Password Baru (*) :<br>
				{{ password_field('new_password', 'value': null, 'size': 20, 'placeholder': 'Password baru') }}
				{% if user.id %}
					<br><i>Kosongkan jika Anda tidak ingin mengubah password lama</i>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td bgcolor="#e5f2ff">
				Password Baru Sekali Lagi (*):<br>
				{{ password_field('new_password_confirmation', 'value': null, 'size': 20, 'placeholder': 'Password baru sekali lagi') }}
				{% if user.id %}
					<br><i>Kosongkan jika Anda tidak ingin mengubah password lama</i>
				{% endif %}
			</td>
		</tr>
		<tr>
			<td>
				Role (*) :<br>
				{% for role in roles %}
					{{ radio_field('role_id', 'value': role.id, 'checked': role.id == user.role_id ? true: null, 'id': 'role_id_' ~ role.id) }} {{ role.name }}&nbsp;&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				Nama (*) :<br>
				{{ text_field('name', 'value': user.name, 'size': 50, 'placeholder': 'Nama') }}
			</td>
		</tr>
		<tr>
			<td>
				Nomor HP (*) :<br>
				{{ text_field('mobile_phone', 'value': user.mobile_phone, 'size': 15, 'placeholder': 'Nomor HP') }}
			</td>
		</tr>
		<tr>
			<td>
				Nama Toko (* untuk merchant) :<br>
				{{ text_field('company', 'value': user.company, 'size': 50, 'placeholder': 'Nama toko') }}
			</td>
		</tr>
		<tr>
			<td>
				Catatan Penjual (untuk merchant) :<br>
				{{ text_area('merchant_note', 'value': user.merchant_note, 'cols': 50, 'rows': 5, 'placeholder': 'Catatan penjual') }}
			</td>
		</tr>
		<tr>
			<td>
				Minimal Order (untuk merchant) :<br>
				{{ text_field('minimum_purchase', 'value': user.minimum_purchase, 'size': 20, 'placeholder': 'Minimal order') }}
			</td>
		</tr>
		<tr>
			<td>
				Biaya Administrasi (untuk merchant) :<br>
				{{ text_field('admin_fee', 'value': user.admin_fee, 'size': 20, 'placeholder': 'Biaya administrasi') }}
			</td>
		</tr>
		<tr>
			<td>
				Jumlah Akumulasi Dikenai Biaya Administrasi (untuk merchant) :<br>
				{{ text_field('accumulation_divisor', 'value': user.accumulation_divisor, 'size': 20, 'placeholder': 'Jumlah Akumulasi Dikenai Biaya Administrasi') }}
			</td>
		</tr>
		<tr>
			<td>
				Jarak pengiriman maksimal (untuk merchant) :<br>
				{{ text_field('max_delivery_distance', 'value': user.max_delivery_distance, 'size': 20, 'placeholder': 'Jarak pengiriman maksimal') }}
			</td>
		</tr>
		<tr>
			<td>
				Jarak pengiriman gratis (untuk merchant) :<br>
				{{ text_field('free_delivery_distance', 'value': user.free_delivery_distance, 'size': 20, 'placeholder': 'Jarak pengiriman gratis') }}
			</td>
		</tr>
		<tr>
			<td>
				Tarif pengiriman per KM (untuk merchant) :<br>
				{{ text_field('delivery_rate', 'value': user.delivery_rate, 'size': 20, 'placeholder': 'Tarif pengiriman per KM') }}
			</td>
		</tr>
		{% if user.role.name == 'Merchant' %}
		<tr>
			<td>
				Deposit (untuk merchant) :<br>
				{{ text_field('deposit', 'value': user.deposit, 'size': 20, 'placeholder': 'Deposit') }}
			</td>
		</tr>
		{% endif %}
		<tr>
			<td>
				Jenis Kelamin :<br>
				{% for gender in genders %}
					{{ radio_field('gender', 'value': gender, 'checked': user.gender == gender ? true : null, 'id': 'gender_' ~ gender) }} {{ gender }}&nbsp;&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				Tanggal Lahir :<br>
				{{ text_field('date_of_birth', 'value': user.date_of_birth, 'size': 10, 'data-plugin-datepicker': true, 'data-date-format': 'yyyy-mm-dd', 'placeholder': 'Tanggal lahir') }}
			</td>
		</tr>
		<tr>
			<td>
				Email :<br>
				{{ text_field('email', 'value': user.email, 'size': 50, 'placeholder': 'Email') }}
			</td>
		</tr>
		<tr>
			<td>
				Alamat :<br>
				{{ text_area('address', 'value': user.address, 'cols': 50, 'rows': 5, 'placeholder': 'Alamat') }}
			</td>
		</tr>
		<tr>
			<td>
				Propinsi (*) :<br>
				{{ select('province_id', provinces, 'using': ['id', 'name'], 'value': province.id, 'useEmpty': true, 'emptyText': '- propinsi -', 'emptyValue': '') }}
			</td>
		</tr>
		<tr>
			<td>
				Kabupaten / Kota (*) :<br>
				{{ select_static('city_id', cities, 'using': ['id', 'name'], 'value': city.id, 'useEmpty': true, 'emptyText': '- kabupaten / kota -', 'emptyValue': '') }}
			</td>
		</tr>
		<tr>
			<td>
				Kecamatan (*) :<br>
				{{ select('subdistrict_id', subdistricts, 'using': ['id', 'name'], 'value': subdistrict.id, 'useEmpty': true, 'emptyText': '- kecamatan -', 'emptyValue': '') }}
			</td>
		</tr>
		<tr>
			<td>
				Kelurahan (*) :<br>
				{{ select_static('village_id', villages, 'using': ['id', 'name'], 'value': user.village_id, 'useEmpty': true, 'emptyText': '- kelurahan -', 'emptyValue': '') }}
			</td>
		</tr>
		<tr>
			<td>
				Hari Operasional (* untuk merchant) :<br>
				{{ check_field('open_on_sunday', 'value': 1, 'checked': user.open_on_sunday ? true : null) }} Minggu&nbsp;&nbsp;
				{{ check_field('open_on_monday', 'value': 1, 'checked': user.open_on_monday ? true : null) }} Senin&nbsp;&nbsp;
				{{ check_field('open_on_tuesday', 'value': 1, 'checked': user.open_on_tuesday ? true : null) }} Selasa&nbsp;&nbsp;
				{{ check_field('open_on_wednesday', 'value': 1, 'checked': user.open_on_wednesday ? true : null) }} Rabu&nbsp;&nbsp;
				{{ check_field('open_on_thursday', 'value': 1, 'checked': user.open_on_thursday ? true : null) }} Kamis&nbsp;&nbsp;
				{{ check_field('open_on_friday', 'value': 1, 'checked': user.open_on_friday ? true : null) }} Jumat&nbsp;&nbsp;
				{{ check_field('open_on_saturday', 'value': 1, 'checked': user.open_on_saturday ? true : null) }} Sabtu
			</td>
		</tr>
		<tr>
			<td>
				Jam Operasional (* untuk merchant) :<br>
				Buka
				{{ select('business_opening_hour', business_hours, 'value': user.business_opening_hour, 'useEmpty': true, 'emptyText': '', 'emptyValue': '') }}
				- Tutup
				{{ select('business_closing_hour', business_hours, 'value': user.business_closing_hour, 'useEmpty': true, 'emptyText': '', 'emptyValue': '') }}
			</td>
		</tr>
		<tr>
			<td>
				Jam Pengantaran (* untuk merchant) :<br>
				{% for hour, label in business_hours %}
					{{ check_field('delivery_hours[]', 'value': hour, 'checked': in_array(hour, user.delivery_hours) ? true : null) }} {{ label }}&nbsp;&nbsp;
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
				<button type="submit" class="btn btn-primary">SIMPAN</button>
			</td>
		</tr>
	</table>
{{ endForm() }}
<script>
	let city = document.querySelector('#city_id'), subdistrict = document.querySelector('#subdistrict_id'), village = document.querySelector('#village_id');
	document.querySelector('#province_id').addEventListener('change', event => {
		city.options.length = 1,
		subdistrict.options.length = 1,
		village.options.length = 1,
		event.target.value && fetch('/admin/users/cities/' + event.target.value, { credentials: 'include' }).then(response => response.json()).then(items => {
			items.forEach(item => {
				let option = document.createElement('option');
				option.value = item.id,
				option.appendChild(document.createTextNode(item.name)),
				city.appendChild(option)
			})
		})
	}, false),
	city.addEventListener('change', event => {
		subdistrict.options.length = 1,
		village.options.length = 1,
		event.target.value && fetch('/admin/users/subdistricts/' + event.target.value, { credentials: 'include' }).then(response => response.json()).then(items => {
			items.forEach(item => {
				let option = document.createElement('option');
				option.value = item.id,
				option.appendChild(document.createTextNode(item.name)),
				subdistrict.appendChild(option)
			})
		})
	}, false),
	subdistrict.addEventListener('change', event => {
		village.options.length = 1,
		event.target.value && fetch('/admin/users/villages/' + event.target.value, { credentials: 'include' }).then(response => response.json()).then(items => {
			items.forEach(item => {
				let option = document.createElement('option');
				option.value = item.id,
				option.appendChild(document.createTextNode(item.name)),
				village.appendChild(option)
			})
		})
	}, false),
	document.querySelector('.delete-avatar').addEventListener('click', event => {
		if (confirm('Anda yakin menghapus gambar ini ?')) {
			let form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/users/' + avatar.dataset.id + '/delete_avatar',
			document.body.appendChild(form),
			form.submit()
		}
	}, false)
</script>