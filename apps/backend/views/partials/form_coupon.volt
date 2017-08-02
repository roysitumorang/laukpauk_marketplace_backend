{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Kode Kupon :</b><br>
				<input type="text" name="code" value="{{ coupon.code }}"{% if coupon.id %} disabled{% endif %} size="15" maxlength="15" class="form form-control form-40" placeholder="Kode Kupon">
			</td>
		</tr>
		<tr>
			<td>
				<b>Diskon :</b><br>
				<input type="text" name="price_discount" value="{{ coupon.price_discount }}" size="40" class="form form-control form-30">
				<select name="discount_type" class="form form-control form-20">
				{% for key, value in discount_types %}
					<option value="{{ key }}"{% if coupon.discount_type == key %} selected{% endif %}>{{ value }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b>Tanggal Berlaku :</b><br>
				<input type="text" name="effective_date" value="{{ coupon.effective_date }}" size="10" maxlength="10" placeholder="YYYY-mm-dd" data-plugin-datepicker data-plugin-options='{"format": "yyyy-mm-dd"}' class="form form-control form-30">
			</td>
		</tr>
		<tr>
			<td>
				<b>Tanggal Expired :</b><br>
				<input type="text" name="expiry_date" value="{{ coupon.expiry_date }}" size="10" maxlength="10" placeholder="YYYY-mm-dd" data-plugin-datepicker data-plugin-options='{"format": "yyyy-mm-dd"}' class="form form-control form-30">
			</td>
		</tr>
		<tr>
			<td>
				<b>Minimum Pembelian (Rp) :</b><br>
				<input type="text" name="minimum_purchase" value="{{ coupon.minimum_purchase }}" size="10" maxlength="10" placeholder="0" class="form form-control form-30"><br>
				Catatan: Nilai kupon akan dihitung dari nilai total belanja
			</td>
		</tr>
		<tr>
			<td>
				<b>Status :</b><br>
				<select name="status" class="form form-control form-30">
				{% for key, value in status %}
					<option value="{{ key }}"{% if coupon.status == key %} selected{% endif %}>{{ value }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b>Cara Penggunaan :</b><br>
				<select name="multiple_use" class="form form-control form-30">
				{% for key, value in usage_types %}
					<option value="{{ key }}"{% if coupon.multiple_use == key %} selected{% endif %}>{{ value }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b>Pemakaian maksimal :</b><br>
				<input type="text" name="maximum_usage" value="{{ coupon.maximum_usage }}" size="15" maxlength="15" class="form form-control form-30" placeholder="Pemakaian maksimal">
			</td>
		</tr>
		<tr>
			<td>
				<b>Berlaku untuk versi minimal :</b><br>
				<select name="release_id" class="form form-control form-30">
					<option value="">Semua versi</option>
					{% for release in releases %}
						<option value="{{ release.id }}"{% if coupon.release_id == release.id %} selected{% endif %}>{{ release.version }}</option>
					{% endfor %}
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<b>Deskripsi :</b><br>
				<textarea name="description" placeholder="Deskripsi Kupon" cols="50" rows="5" class="form form-control form-50">{{ coupon.description }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
