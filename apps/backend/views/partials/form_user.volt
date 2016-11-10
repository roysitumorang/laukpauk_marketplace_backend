{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td bgcolor="#e5f2ff">
				Password Baru (*):<br>
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
				Nama (*):<br>
				<input type="text" name="name" value="{{ user.name }}" class="form form-control form-50" size="40">
			</td>
		</tr>
		<tr>
			<td>
				Jenis Kelamin:<br>
				{% for gender in genders %}
				<input type="radio" name="gender" value={{ gender }}{% if user.gender == gender %} checked{% endif %}> {{ gender }}&nbsp;&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				Tanggal Lahir:<br>
				<input type="text" name="date_of_birth" value="{{ user.date_of_birth }}" data-plugin-datepicker data-plugin-options="{format:'yyyy-mm-dd'}" class="form form-control form-30" size="12">
			</td>
		</tr>
		<tr>
			<td>
				KTP / SIM / Paspor:<br>
				<input type="text" name="ktp" value="{{ user.ktp }}" class="form form-control form-50" size="50">
			</td>
		</tr>
		<tr>
			<td>
				Nama Usaha:<br>
				<input type="text" name="company" value="{{ user.company }}" class="form form-control form-50" size="50">
			</td>
		</tr>
		<tr>
			<td>
				NPWP:<br>
				<input type="text" name="npwp" value="{{ user.npwp }}" class="form form-control form-50" size="50">
			</td>
		</tr>
		<tr>
			<td>
				Email (*):<br>
				<input type="text" name="email" value="{{ user.email }}" class="form form-control form-50" size="50">
			</td>
		</tr>
		<tr>
			<td>
				Alamat:<br>
				<textarea name="address" cols="50" rows="5" class="form form-control form-50">{{ user.address }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				Propinsi<br>
				<select name="province_id" id="province_id">
				{% for province in provinces %}
					<option value="{{ province.id }}"{% if user.province_id == province.id %} selected{% endif %}>{{ province.name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kota<br>
				<select name="city_id" id="city_id">
				{% for city in cities %}
					<option value="{{ city.id }}"{% if user.city_id == city.id %} selected{% endif %}>{{ city.type }} {{ city.name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Kecamatan<br>
				<select name="subdistrict_id" id="subdistrict_id">
				{% for subdistrict in subdistricts %}
					<option value="{{ subdistrict.id }}"{% if user.subdistrict_id == subdistrict.id %} selected{% endif %}>{{ subdistrict.name }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Post Code:<br>
				<input type="text" name="zip_code" value="{{ user.zip_code }}" class="form form-control form-30" size="5">
			</td>
		</tr>
		<tr>
			<td>
				Phone Number (*):<br>
				<input type="text" name="phone" value="{{ user.phone }}" class="form form-control form-40" size="40">
			</td>
		</tr>
		<tr>
			<td>
				Mobile Number:<br>
				<input type="text" name="mobile" value="{{ user.mobile }}" class="form form-control form-40" size="40">
			</td>
		</tr>
		{% if user.id %}
		<tr>
			<td>
				Registration Date:<br>
				{{ user.created_at }}
			</td>
		</tr>
		{% endif %}
		<tr>
			<td>
				Membership (*):<br>
				{% for value, label in memberships %}
				<input type="radio" name="premium" value="{{ value }}"{% if user.premium == value %} checked{% endif %}> {{ label }}&nbsp;&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				Status Keaktifan (*):<br>
				{% for label, value in status %}
				<input type="radio" name="status" value="{{ value }}"{% if user.status == value %} checked{% endif %}> {{ label }}&nbsp;&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				Dompet (Rp):<br>
				<input type="text" name="deposit" value="{{ user.deposit }}" class="form form-control form-50" size="20">
			</td>
		</tr>
		<tr>
			<td>
				Reward (Rp):<br>
				<input type="text" name="reward" value="{{ user.reward }}" class="form form-control form-50" size="20">
			</td>
		</tr>
		<tr>
			<td>
				Poin Beli:<br>
				<input type="text" name="buy_point" value="{{ user.buy_point }}" class="form form-control form-20" size="20">
			</td>
		</tr>
		<tr>
			<td>
				Poin Affiliasi:<br>
				<input type="text" name="affiliate_point" value="{{ user.affiliate_point }}" class="form form-control form-20" size="20">
			</td>
		</tr>
		{% if user.id %}
		<tr>
			<td>
				Kode Aktivasi:<br>
				{{ user.activation_code }}
			</td>
		</tr>
		{% endif %}
		<tr>
			<td>
				Avatar:<br>
				<input type="file" name="avatar" size="30" class="form form-control form-30">
				{% if user.avatar %}
				<br><img src="{{ user.thumbnail }}" border="0"><br>
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
		{% if user.id %}
		<tr>
			<td>
				Twitter ID:<br>
				{{ user.twitter_id }}
			</td>
		</tr>
		<tr>
			<td>
				Google ID:<br>
				{{ user.google_id }}
			</td>
		</tr>
		<tr>
			<td>
				Facebook ID:<br>
				{{ user.facebook_id }}
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
	var provinces = {{ regions }}, province = document.getElementById('province_id'), city = document.getElementById('city_id'), subdistrict = document.getElementById('subdistrict_id');
	province.onchange = function() {
		var cities = provinces[this.value].cities, subdistricts = cities[Object.keys(cities)[0]].subdistricts, city_options = '', subdistrict_options = '';
		for (var item in cities) {
			city_options += '<option value="' + cities[item].id + '">' + cities[item].type + ' ' + cities[item].name  + '</option>';
		}
		for (var item in subdistricts) {
			subdistrict_options += '<option value="' + subdistricts[item].id + '">' + subdistricts[item].name  + '</option>';
		}
		city.innerHTML = city_options;
		subdistrict.innerHTML = subdistrict_options;
	}
	city.onchange = function() {
		var cities = provinces[province.value].cities, subdistricts = cities[this.value].subdistricts, subdistrict_options = '';
		for (var item in subdistricts) {
			subdistrict_options += '<option value="' + subdistricts[item].id + '">' + subdistricts[item].name  + '</option>';
		}
		subdistrict.innerHTML = subdistrict_options;
	}
</script>