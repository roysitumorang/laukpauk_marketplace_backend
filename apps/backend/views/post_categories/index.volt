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
				<a href="/admin/post_categories"><h2>Content Category</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/post_categories">Content Category</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Content Category</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<p style="margin-left:5px"><i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/post_categories/create">New Category</a></p>
				<div class="row">
				{% for post_category in post_categories %}
					<div class="col-md-4 panel" style="width:20%;">
						<div class="panel-body panel-featured">
							<a href="/admin/posts/index/post_category_id:{{ post_category.id }}" title="{{ post_category.name }}"><i class="fa fa-newspaper-o fa-5x"></i></a><br>
							<a href="javascript:void(0)" class="published" data-id="{{ post_category.id }}">
								<img src="/assets/images/bullet-{% if post_category.published %}green{% else %}red{% endif %}.png" border="0">
							</a>
							{{ post_category.name }}&nbsp;(<font color="#FF0000"><b>ID: </b></font>{{ post_category.id }})<br>
							({{ post_category.permalink }})
							<br><br>
							{% if post_category.allow_comments %}
							<b><font color="#0033FF">Komentar:</font></b> ON<br>
							Moderasi: <b>{% if post_category.comment_moderation %}YA{% else %}TIDAK{% endif %}
							{% else %}
							<b><font color="#FF0000">Komentar: OFF</font></b>
							{% endif %}
							<br>{{ post_category.posts.count() }} content
							<br><br>
							<a href="/admin/post_categories/update/{{ post_category.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
							{% if post_category.removable %}&nbsp;
							<a href="javascript:void(0)" class="delete" data-id="{{ post_category.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							{% endif %}
						</div>
					</div>
				{% endfor %}
				</div>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/post_categories/page:{{ i }}">{{ i }}</a>
							{% endif %}
						{% endfor %}
					</p>
				</div>
				{% endif %}
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>
<script>
	for (var items = document.querySelectorAll('.delete'), i = items.length; i--; ) {
		items[i].onclick = function() {
			if (!confirm('Anda yakin ingin menghapus kategori ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/post_categories/delete/' + this.dataset.id,
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.published'), i = items.length; i--; ) {
		items[i].onclick = function() {
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/post_categories/update/' + this.dataset.id + '/published:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>