{{ flashSession.output() }}
<form method="POST" action="{{ action }}">
	<table class="table table-striped">
		<tr>
			<td class="text-right">
				Directory :
			</td>
			<td>
				<input type="text" name="name" value="{{ page_category.name }}" size="50" class="form form-control form-40">
			</td>
		</tr>
		<tr>
			<td class="text-right">
				"Create Page" Menu :
			</td>
			<td>
				<input type="radio" name="has_create_page_menu" value="1"{% if page_category.has_create_page_menu %} checked{% endif %}> Show
				<input type="radio" name="has_create_page_menu" value="0"{% if !page_category.has_create_page_menu %} checked{% endif %}> Hide
			</td>
		</tr>
		<tr>
			<td class="text-right">
				Show Picture Icon :
			</td>
			<td>
				<input type="radio" name="has_picture_icon" value="1"{% if page_category.has_picture_icon %} checked{% endif %}> Show
				<input type="radio" name="has_picture_icon" value="0"{% if !page_category.has_picture_icon %} checked{% endif %}> Hide
			</td>
		</tr>
		<tr>
			<td class="text-right">
				Show Content :
			</td>
			<td>
				<input type="radio" name="has_content" value="1"{% if page_category.has_content %} checked{% endif %}> Show
				<input type="radio" name="has_content" value="0"{% if !page_category.has_content %} checked{% endif %}> Hide
			</td>
		</tr>
		<tr>
			<td class="text-right">
				Show URL :
			</td>
			<td>
				<input type="radio" name="has_url" value="1"{% if page_category.has_url %} checked{% endif %}> Show
				<input type="radio" name="has_url" value="0"{% if !page_category.has_url %} checked{% endif %}> Hide
			</td>
		</tr>
		<tr>
			<td class="text-right">
				Show Link Target :
			</td>
			<td>
				<input type="radio" name="has_link_target" value="1"{% if page_category.has_link_target %} checked{% endif %}> Show
				<input type="radio" name="has_link_target" value="0"{% if !page_category.has_link_target %} checked{% endif %}> Hide
			</td>
		</tr>
		<tr>
			<td class="text-right">
				Show Rich Editor :
			</td>
			<td>
				<input type="radio" name="has_rich_editor" value="1"{% if page_category.has_rich_editor %} checked{% endif %}> Show
				<input type="radio" name="has_rich_editor" value="0"{% if !page_category.has_rich_editor %} checked{% endif %}> Hide
			</td>
		</tr>
		<tr>
			<td></td>
			<td>
				<button type="submit" class="btn btn-info">SIMPAN</button>
			</td>
		</tr>
	</table>
</form>