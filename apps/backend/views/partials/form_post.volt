{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Judul Content:</font></b><br>
				<input type="text" name="subject" value="{{ post.subject }}" size="60" placeholder="Judul">
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Content Detail:</font></b><br>
				<textarea name="body" id="body" class="summernote" data-plugin-summernote data-plugin-options="{'height':180,'codemirror':{'theme':'ambiance'}}" cols="80" rows="40" placeholder="Body">{{ post.body }}</textarea>
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Photo</font></b>
				<br>
				<input type="file" name="picture" size="50"><br>
				Picture file should be bellow of 200 Kb
				{% if post.picture %}<br>
				<br><a class="image-popup-no-margins" href="/assets/image/{{ post.picture }}"><img src="/assets/image/{{ post.thumbnail }}" border="0"></a>
				&nbsp;<a href="javascript:void(0)" class="delete-picture" data-post-category-id="{{ post_category.id }}" data-id="{{ post.id }}" title="Hapus"><i class="fa fa-trash-o"></i></a>
				{% endif %}
			</td>
		</tr>
	</table>
	<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="javascript:void(0)" onclick="var extra_form=document.getElementById('extra-form');extra_form.setAttribute('style','display:'+(extra_form.getAttribute('style')=='display:none'?'block':'none'))">Form Tambahan</a></p>
	<table id="extra-form" class="table table-striped" style="display:none">
		<tr>
			<td>
				<b><font color="#000099">Permalink:</font></b><br>
				<input type="text" name="new_permalink" value="{{ post.new_permalink }}" size="70" placeholder="Permalink"><br>
				<i>Kosongkan untuk mengisi permalink secara otomatis</i>
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Link Tambahan</font></b>
				<br>
				<input type="text" name="custom_link" value="{{ post.custom_link }}" size="70" placeholder="http://">
			</td>
		</tr>
		<tr>
			<td bgcolor="#e0ebeb">
				<b><font color="#000099">Meta Title</font></b>
				<br>
				<input type="text" name="meta_title" value="{{ post.meta_title }}" size="70" placeholder="Meta title"><br>
				<i>Maximum 60 characters</i>
			</td>
		</tr>
		<tr>
			<td bgcolor="#e0ebeb">
				<b><font color="#000099">Meta Deskripsi</font></b>
				<br>
				<textarea name="meta_desc" cols="70" rows="3" placeholder="Meta Deskripsi">{{ post.meta_desc }}</textarea><br>
				<i>Maximum 160 characters</i>
			</td>
		</tr>
		<tr>
			<td bgcolor="#e0ebeb">
				<b><font color="#000099">Meta Keywords</font></b>
				<br>
				<input type="text" name="meta_keyword" value="{{ post.meta_keyword }}" size="70" placeholder="Meta keywords">
			</td>
		</tr>
		<tr>
			<td bgcolor="#e0ebeb">
				<b><font color="#000099">Status Content</font></b>
				<br>
				<input type="radio" name="published" value="1"{% if post.published %} checked{% endif %}> Tampil
				<input type="radio" name="published" value="0"{% if !post.published %} checked{% endif %}> Sembunyikan
			</td>
		</tr>
	</table>
	<table class="table table-striped">
		<tr>
			<td>
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>
{% if post.id and post.picture %}
<script>
	document.querySelector('.delete-picture').onclick = () => {
		if (!confirm('Anda yakin mau menghapus gambar ini ?')) {
			return !1
		}
		let form = document.createElement('form');
		form.method = 'POST',
		form.action = '/admin/posts/update/' + this.dataset.id + '/post_category_id:' + this.dataset.postCategoryId + '/delete_picture:1',
		document.body.appendChild(form),
		form.submit()
	}
</script>
{% endif %}
