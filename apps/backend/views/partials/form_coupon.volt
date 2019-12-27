{{ flashSession.output() }}
{{ form(action) }}
	<table class="table table-striped">
		<tr>
			<td>
				<b>Kode Kupon :</b><br>
				{{ text_field('code', 'value': coupon.code, 'disabled': coupon.id ? true : null, 'size': 15, 'maxlength': 15, 'class': 'form form-control form-40', 'placeholder': 'Kode Kupon') }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Diskon :</b><br>
				{{ text_field('price_discount', 'value': coupon.price_discount, 'size': 40, 'class': 'form form-control form-30', 'placeholder': 'Diskon') }}
				{{ select_static({'discount_type', discount_types, 'value': coupon.discount_type, 'class': 'form form-control form-20'}) }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Tanggal Berlaku :</b><br>
				{{ text_field('effective_date', 'value': coupon.effective_date, 'size': 10, 'maxlength': 10, 'placeholder': 'YYYY-mm-dd', 'data-plugin-datepicker': true, 'data-date-format': 'yyyy-mm-dd', 'class': 'form form-control form-30') }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Tanggal Expired :</b><br>
				{{ text_field('expiry_date', 'value': coupon.expiry_date, 'size': 10, 'maxlength': 10, 'placeholder': 'YYYY-mm-dd', 'data-plugin-datepicker': true, 'data-date-format': 'yyyy-mm-dd', 'class': 'form form-control form-30') }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Minimum Pembelian (Rp) :</b><br>
				{{ text_field('minimum_purchase', 'value': coupon.minimum_purchase, 'size': 10, 'maxlength': 10, 'placeholder': '0', 'class': 'form form-control form-30') }}<br>
				Catatan: Nilai kupon akan dihitung dari nilai total belanja
			</td>
		</tr>
		<tr>
			<td>
				<b>Status :</b><br>
				{{ select_static({'status', coupon_status, 'value': coupon.status, 'class': 'form form-control form-30'}) }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Cara Penggunaan :</b><br>
				{{ select_static({'multiple_use', usage_types, 'value': coupon.multiple_use, 'class': 'form form-control form-30'}) }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Pemakaian maksimal :</b><br>
				{{ text_field('maximum_usage', 'value': coupon.maximum_usage, 'size': 15, 'maxlength': 15, 'class': 'form form-control form-30', 'placeholder': 'Pemakaian maksimal') }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Berlaku untuk versi minimal :</b><br>
				{{ select({'release_id', releases, 'using': ['id', 'version'], 'value': coupon.release_id, 'useEmpty': true, 'emptyText': '- semua versi -', 'emptyValue': ''}) }}
			</td>
		</tr>
		<tr>
			<td>
				<b>Deskripsi :</b><br>
				{{ text_area('description', 'value': coupon.description, 'placeholder': 'Deskripsi Kupon', 'cols': 50, 'rows': 5, 'class': 'form form-control form-50') }}
			</td>
		</tr>
		<tr>
			<td>
				<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
			</td>
		</tr>
	</table>
{{ endForm() }}