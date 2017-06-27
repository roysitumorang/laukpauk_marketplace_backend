{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				Bank<br>
				<input type="text" name="bank" value="{{ bank_account.bank }}" size="10" maxlength="10" placeholder="Bank">
			</td>
		</tr>
		<tr>
			<td>
				Nomor Rekening<br>
				<input type="text" name="number" value="{{ bank_account.number }}" size="15" maxlength="15" placeholder="Nomor Rekening">
			</td>
		</tr>
		<tr>
			<td>
				Nama Pemegang Rekening<br>
				<input type="text" name="holder" value="{{ bank_account.holder }}" size="50" maxlength="50" placeholder="Nama Pemegang Rekening">
			</td>
		</tr>
		<tr>
			<td>
				Tampilkan<br>
				<input type="radio" name="published" value="1"{% if bank_account.published %} checked{% endif %}> Ya&nbsp;&nbsp;
				<input type="radio" name="published" value="0"{% if !bank_account.published %} checked{% endif %}> Tidak
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
</form>