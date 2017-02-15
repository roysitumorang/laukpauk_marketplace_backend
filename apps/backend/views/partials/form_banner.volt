{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Banner Name:</font></b>
				<br>
				<input type="text" name="name" value="{{ banner.name }}" size="60" placeholder="Banner name">
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">File URL:</font></b>
				<br>
				<input type="text" name="file_url" value="{{ banner.file_url }}" size="60" placeholder="File URL"><br><br><b>ATAU</b><br><br>
				<b><font color="#000099">Banner File:</font></b>
				<br>
				<input type="file" name="new_file" size="50">
				<br>
				{% if banner.file_name %}
				<a class="image-popup-no-margins" href="/assets/image/{{ banner.file_name }}"><img src="/assets/image/{{ banner.thumbnail }}" border="0"></a>
				<br><a href="javascript:void(0)" class="delete-picture" data-banner-category-id="{{ banner_category.id }}" data-id="{{ banner.id }}" title="Hapus"><i class="fa fa-trash-o"></i></a><br>
				{% endif %}
				<i>Kosongkan "FILE URL" jika Anda ingin meng-upload file banner dari komputer Anda</i>
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Banner URL:</font></b>
				<br>
				<input type="text" name="link" value="{{ banner.link }}" size="60" placeholder="Banner URL"><br><b>Contoh:</b> http://www.domain-anda.com
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Banner Type:</font></b>
				<br>
				{% for key, label in banner_types %}
				<input type="radio" name="type" value="{{ key }}"{% if banner.type == key %} checked{% endif %}> {{ label }}&nbsp;
				{% endfor %}
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Status:</font></b>
				<br>
				<input type="radio" name="published" value="1"{% if banner.published %} checked{% endif %}> Tampilkan&nbsp;
				<input type="radio" name="published" value="0"{% if !banner.published %} checked{% endif %}> Sembunyikan
			</td>
		</tr>
		<tr>
			<td>
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
{% if banner.id and banner.file_name %}
<script>
	for (var items = document.querySelectorAll('.delete-picture'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus gambar banner ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/banners/update/' + this.dataset.id + '/banner_category_id:' + this.dataset.bannerCategoryId + '/delete_picture:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>
{% endif %}