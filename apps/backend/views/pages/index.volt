<section class="body">
	<!-- start: header -->
	{{ partial('partials/top_menu') }}
	<!-- end: header -->
	<div class="inner-wrapper">
		<!-- start: sidebar -->
		{{ partial('partials/left_side') }}
		<!-- end: sidebar -->
		<section role="main" class="content-body">
			<header class="page-header">
				<a href="/admin/pages/index/page_category_id:{{ page_category.id }}"><h2>Menu: {{ page_category.name }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/page_categories">Pages</a></span></li>
						<li><span><a href="/admin/pages/index/page_category_id:{{ page_category.id }}">{{ page_category.name }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">{{ page_category.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				{% if page_category.has_create_page_menu %}
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/pages/create/page_category_id:{{ page_category.id }}{% if parent_id%}/parent_id:{{ parent_id }}{% endif %}" class="main">New Menu</a>
				{% endif %}
				<br><br>
				<table class="table table-striped">
				{% for page in pages %}
					<tr>
						<td width="30%">
							{% if page_category.has_picture_icon and page.picture %}
							<a class="image-popup-no-margins" href="/assets/images/{{ page.picture }}"><img src="/assets/images/{{ page.thumbnail }}" border="0"></a>
							<a href="javascript:void(0)" class="delete-picture" data-page-category-id="{{ page_category.id }}" data-id="{{ page.id }}" title="Hapus Gambar"><i class="fa fa-trash-o"></i></a>
							{% else %}
							<i class="fa fa-file fa-5x"></i>
							{% endif %}
							<br>
							{{ page.name }}<br>
							{% if page_category.has_link_target and page.url %}
							{{ page.url }}<br>
							{% endif %}
							<a href="/admin/pages/update/{{ page.id }}/page_category_id:{{ page_category.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
							{% if page.removable %}
							&nbsp;<a href="javascript:void(0)" class="delete" data-page-category-id="{{ page_category.id }}" data-id="{{ page.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							{% endif %}
							{% if page_category.has_create_page_menu %}
							&nbsp;<a href="/admin/pages/create/page_category_id:{{ page_category.id }}/parent_id:{{ page.id }}" title="Tambah Page"><i class="fa fa-plus-square fa-2x"></i></a>
							{% endif %}
							&nbsp;<a href="/admin/pages/index/page_category_id:{{ page_category.id }}/parent_id:{{ page.id }}" title="Sub Page"><i class="fa fa-list fa-2x"></i></a>
						</td>
						<td>
							{% if page.meta_title %}
							<b>Meta Title:</b><br>{{ page.meta_title }}
							<br><br>
							{% endif %}
							{% if page.meta_desc %}
							<b>Meta Description:</b><br>{{ page.meta_desc }}
							<br><br>
							{% endif %}
							{% if page.meta_keyword %}
							<b>Meta Keyword:</b><br>{{ page.meta_keyword }}
							{% endif %}
						</td>
						<td>
							<font color="#FF0000"><b>ID Menu:&nbsp;</b></font>{{ page.id }}<br>
							<font color="#000099"><b>ID Category:&nbsp;</b></font>{{ page.name }}<br>
							<font color="#000099"><b>ID Top Page:&nbsp;</b></font>{{ page.parent_id|default('-') }}<br>
							<font color="#000000"><b>Urutan:&nbsp;</b></font>{{ page.position }}
						</td>
						<td align="right">
							<a href="javascript:void(0)" class="published" data-page-category-id="{{ page_category.id }}" data-id="{{ page.id }}">
								{% if page.published %}
								<span style="padding:5px;width:30%;background:#33cc33;color:#000000">Online<span>
								{% else %}
								<span style="padding:5px;width:30%;background:#ff0000;color:#FFFFFF">Offline<span>
								{% endif %}
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				{% endfor %}
				</table>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	for (var items = document.querySelectorAll('.published'), i = items.length; i--; ) {
		items[i].onclick = function() {
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/pages/update/' + this.dataset.id + '/page_category_id:' + this.dataset.pageCategoryId + '/published:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus menu ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/pages/delete/' + this.dataset.id + '/page_category_id:' + this.dataset.pageCategoryId,
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.delete-picture'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus gambar icon ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/pages/update/' + this.dataset.id + '/page_category_id:' + this.dataset.pageCategoryId + '/delete_picture:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>