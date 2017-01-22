{{ flashSession.output() }}
<form method="POST" action="{{ action }}" enctype="multipart/form-data">
	<table class="table table-striped">
		<tr>
			<td>
				<b><font color="#000099">Title</font></b><br>
				<input type="text" name="name" value="{{ page.name }}" size="60" class="form form-control form-60">
			</td>
		</tr>
		{% if page_category.has_content %}
		<tr>
			<td>
				<b><font color="#000099">Content</font></b><br>
				<textarea name="body"{% if page_category.has_rich_editor %} class="summernote" data-plugin-summernote data-plugin-options="{height:180,codemirror:{theme:'ambiance'}}"{% else %} class="form form-control"{% endif %} id="body" cols="80" rows="20">{{ page.body }}</textarea>
			</td>
		</tr>
		{% endif %}
		{% if page_category.has_link_target %}
		<tr>
			<td>
				<b><font color="#000099">Link Target</font></b>
				<br>
				<select name="url_target" class="form form-control form-30">
				{% for url_target in url_targets %}
					<option value="{{ url_target }}"{% if page.url_target == url_target %} selected{% endif %}>{{ url_target }}</option>
				{% endfor %}
				</select>
			</td>
		</tr>
		{% endif %}
		{% if page_category.has_picture_icon %}
		<tr>
			<td>
				<b><font color="#000099">Picture Icon</font></b>
				<br>
				<input type="file" name="picture" class="form form-control form-30">
				Picture file should be bellow of 200 Kb
				{% if page.picture %}
				<br><a class="image-popup-no-margins" href="/assets/image/{{ page.picture }}"><img src="/assets/image/{{ page.thumbnail }}" border="0"></a><br>
				<a href="javascript:void(0)" class="delete-picture" data-page-category-id="{{ page_category.id }}" data-id="{{ page.id }}"{% if parent_id %} data-parent-id="{{ parent_id }}"{% endif %} title="Hapus Gambar"><i class="fa fa-trash-o"></i></a>
				{% endif %}
			</td>
		</tr>
		{% endif %}
		{% if page_category.has_url %}
		<tr>
			<td>
				<b><font color="#000099">URL</font></b>
				<br>
				<input type="text" name="url" value="{{ page.url }}" size="60" class="form form-control form-60"><br>
				<i>(Kosongkan jika tidak ingin melink halaman ini ke URL lain)</i>
			</td>
		</tr>
		{% endif %}
	</table>
	<p style="margin-left:5px;margin-top:-10px"><i class="fa fa-plus-square"></i>&nbsp;<a href="javascript:void(0)" onclick="var extra_form=document.getElementById('extra-form');extra_form.setAttribute('style','display:'+(extra_form.getAttribute('style')=='display:none'?'block':'none'))">Form Tambahan</a></p>
	<table id="extra-form" class="table table-striped" style="display:none">
		<tr>
			<td>
				<b><font color="#000099">Permalink</font></b><br>
				<input type="text" name="permalink" value="{{ page.permalink }}" size="50" class="form form-control form-50"><br>
				<i>Biarkan kosong jika ingin mengisi permalink secara otomatis</i>
			</td>
		</tr>
		<tr>
			<td bgcolor="#e5f2ff">
				<b><font color="#000099">Meta Title</font></b><br>
				<input type="text" name="meta_title" value="{{ page.meta_title }}" size="60" class="form form-control form-50"><br>
				<i>Maximum 60 characters</i>
			</td>
		</tr>
		<tr>
			<td bgcolor="#e5f2ff">
				<b><font color="#000099">Meta Description</font></b><br>
				<textarea name="meta_desc" class="form form-control form-50" cols="60" rows="3">{{ page.meta_desc }}</textarea><br>
				<i>Maximum 160 characters</i>
			</td>
		</tr>
		<tr>
			<td bgcolor="#e5f2ff">
				<b><font color="#000099">Meta Keyword</font></b><br>
				<input type="text" name="meta_keyword" value="{{ page.meta_keyword }}" size="60" class="form form-control form-50">
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Status</font></b><br>
				<input type="radio" name="published" value="1"{% if page.published %} checked{% endif %}> Tampilkan
				<input type="radio" name="published" value="0"{% if !page.published %} checked{% endif %}> Sembunyikan
			</td>
		</tr>
		<tr>
			<td>
				<b><font color="#000099">Urutan</font></b>
				<br>
				<input type="text" name="position" value="{{ page.position }}" class="form form-control form-20" size="5">
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
<script>
$(function() {
	$('.summernote').summernote()
	{% if page.id and page.picture %}
	document.querySelector('.delete-picture').onclick = function() {
		if (!confirm('Anda yakin ingin menghapus gambar icon ini ?')) {
			return !1
		}
		var form = document.createElement('form');
		form.method = 'POST',
		form.action = '/admin/pages/update/' + this.dataset.id + '/page_category_id:' + this.dataset.pageCategoryId + '/delete_picture:1',
		document.body.appendChild(form),
		form.submit()
	}
	{% endif %}
})
</script>
