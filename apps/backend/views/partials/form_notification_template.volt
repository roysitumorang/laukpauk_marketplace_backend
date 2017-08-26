{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				<b>Nama</b>
				<br>
				<input type="text" name="name" value="{{ notification_template.name }}" placeholder="Nama" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				<b>Teks</b>
				<br>
				<input type="text" name="title" value="{{ notification_template.title }}" placeholder="Teks" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				<b>Link Admin</b>
				<br>
				<input type="text" name="admin_target_url" value="{{ notification_template.admin_target_url }}" placeholder="Link Admin" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				<b>Link Merchant</b>
				<br>
				<input type="text" name="merchant_target_url" value="{{ notification_template.merchant_target_url }}" placeholder="Link Merchant" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				<b>Link Mobile Lama</b>
				<br>
				<input type="text" name="old_mobile_target_url" value="{{ notification_template.old_mobile_target_url }}" placeholder="Link Mobile Lama" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				<b>Link Mobile Baru</b>
				<br>
				<input type="text" name="new_mobile_target_url" value="{{ notification_template.new_mobile_target_url }}" placeholder="Link Mobile Baru" class="form-control">
			</td>
		</tr>
		<tr>
			<td>
				<a type="button" href="/admin/notification_templates" class="btn btn-default"><i class="fa fa-chevron-left"></i> KEMBALI</a>
				<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
			</td>
		</tr>
	</table>
</form>