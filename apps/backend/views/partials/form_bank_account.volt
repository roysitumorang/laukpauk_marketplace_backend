{{ flashSession.output() }}
{{ form(action) }}
	<table class="table table-striped">
		<tr>
			<td>
				Bank<br>
				{{ text_field('bank', 'value': bank_account.bank, 'size': 10, 'maxlength': 10, 'placeholder': 'Bank') }}
			</td>
		</tr>
		<tr>
			<td>
				Nomor Rekening<br>
				{{ text_field('number', 'value': bank_account.number, 'size': 15, 'maxlength': 15, 'placeholder': 'Nomor Rekening') }}
			</td>
		</tr>
		<tr>
			<td>
				Nama Pemegang Rekening<br>
				{{ text_field('holder', 'value': bank_account.holder, 'size': 50, 'maxlength': 50, 'placeholder': 'Nama Pemegang Rekening') }}
			</td>
		</tr>
		<tr>
			<td>
				Tampilkan<br>
				{{ radio_field('published', 'value': 1, 'checked': bank_account.published ? true : null, 'id': 'published_' ~ 1) }} Ya&nbsp;&nbsp;
				{{ radio_field('published', 'value': 0, 'checked': bank_account.published ? null : true, 'id': 'published_' ~ 0) }} Tidak
				<br>
				<i>Pilih "Ya" jika Anda ingin menampilkan jenis produk ini atau "Tidak" jika Anda tidak ingin menampilkan rekening ini</i>
			</td>
		</tr>
		<tr>
			<td>
				<a type="button" href="/admin/bank_accounts" class="btn btn-default"><i class="fa fa-chevron-left"></i> KEMBALI</a>
				<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
			</td>
		</tr>
	</table>
{{ endForm() }}