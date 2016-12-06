{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td>
				Category Name:<br>
				<input type="text" name="name" value="{{ post_category.name }}" size="50" class="form form-control form-50"><br>
				Permalink:<br>
				<input type="text" name="new_permalink" value="{{ post_category.new_permalink }}" size="50" class="form form-control form-50"><br>
				<i>Kosongkan jika ingin mengisi permalink secara otomatis</i>
				<br><br>
				Comments Available:<br>
				<input type="radio" name="allow_comments" value="1"{% if post_category.allow_comments %} checked{% endif %}> Ya
				<input type="radio" name="allow_comments" value="0"{% if !post_category.allow_comments %} checked{% endif %}> Tidak<br>
				Moderate Comments:<br>
				<input type="radio" name="comment_moderation" value="1"{% if post_category.comment_moderation %} checked{% endif %}> Ya
				<input type="radio" name="comment_moderation" value="0"{% if !post_category.comment_moderation %} checked{% endif %}> Tidak<br>
				Tampilkan:<br>
				<input type="radio" name="published" value="1"{% if post_category.published %} checked{% endif %}> Ya
				<input type="radio" name="published" value="0"{% if !post_category.published %} checked{% endif %}> Tidak<br>
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>