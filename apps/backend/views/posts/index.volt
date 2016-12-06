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
				<a href="/admin/posts/index/post_category_id:{{ post_category.id }}"><h2>Content: {{ post_category.name }}</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span><a href="/admin/post_categories">Content</a></span></li>
						<li><span><a href="/admin/posts/index/post_category_id:{{ post_category.id }}">Content: {{ post_category.name }}</a></span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Content: {{ post_category.name }}</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<i class="fa fa-plus-square"></i>&nbsp;<a href="/admin/posts/create/post_category_id:{{ post_category.id }}" class="main">Tambah Content</a>
				<br>
				<form method="GET" action="/admin/posts/index/post_category_id:{{ post_category.id }}">
					<table class="table table-striped">
						<tr>
							<td>
								<input type="text" name="keyword" value="{{ post_category_keyword }}" size="40" class="form form-control form-40">&nbsp;
								<button type="submit" class="btn btn-info">TAMPILKAN</button>
							</td>
						</tr>
					</table>
				</form>
				<table class="table table-striped">
					<thead>
						<tr>
							<th width="25"><b>No</b></th>
							<th><b>Judul Content</b></th>
							<th><b>Deskripsi Singkat</b></th>
							<th><b>#</b></th>
						</tr>
					</thead>
					<tbody>
					{% for post in posts %}
						{% if post.published %}
							{% set background = '' %}
						{% else %}
							{% set background = ' style="opacity:0.4;filter:alpha(opacity=40)"' %}
						{% endif %}
						<tr>
							<td{{ background }}>{{ post.rank }}</td>
							<td{{ background }}>
								{% if post.picture %}
								<img src="/assets/images/{{ post.thumbnail }}" width="120" height="100" border="0">
								{% else %}
								<img src="/assets/images/no_picture_120.png" border="0">
								{% endif %}
								<br>
								<font size="4"><a href="/admin/posts/show/{{ post.id }}/page_category_id:{{ post_category.id }}">{{ post.subject }}</a></font>
								<a href="javascript:void(0)" class="published" data-post-category-id="{{ post_category.id }}" data-id="{{ post.id }}">

									<img src="/assets/images/bullet-{% if post.published %}green{% else %}red{% endif %}.png" border="0">
								</a>
								{% if post.published %}
								<br>
								<a href="https://www.facebook.com/sharer/sharer.php?u={{ fqdn }}/posts/{{ post_category.permalink }}/{{ post.permalink }}" target="_blank" title="Share di Facebook"><i class="fa fa-facebook-square fa-2x"></i></a>
								<a href="https://twitter.com/home?status={{ post.subject }}%20{{ fqdn }}/posts/{{ post_category.permalink }}/{{ post.permalink }}" title="Share di Twitter" target="_blank"><i class="fa fa-twitter-square fa-2x"></i></a>
								<a href="https://plus.google.com/share?url={{ fqdn }}/posts/{{ post_category.permalink }}/{{ post.permalink }}" target="_blank" title="Share di Google"><i class="fa fa-google-plus-square fa-2x"></i></a>
								<a href="{{ fqdn }}/posts/{{ post_category.permalink }}/{{ post.permalink }}" target="_blank" title="{{ fqdn }}/posts/{{ post_category.permalink }}/{{ post.permalink }}"><i class="fa fa-external-link-square fa-2x"></i></a>
								{% endif %}
							</td>
							<td{{ background }}>
								{% if post.body %}
								{{ substr(strip_tags(post.body), 0, 150) ~ '...' }}<br>
								{% endif %}
								<b>ID: </b>{{ post.id }}<br>
								<b>Category ID: </b>{{ post_category.id }}<br>
								<strong>Tgl Publikasi:</strong>&nbsp;{{ strftime('%A, %e %B %Y', strtotime(post.created_at)) }}<br>
								<strong>Komentar:</strong>&nbsp;{{ post.comments.count() }} komentar
							</td>
							<td{{ background }}>
								<a href="/admin/posts/show/{{ post.id }}/post_category_id:{{ post_category.id }}" title="Detail"><i class="fa fa-info-circle fa-2x"></i></a>
								<br><a href="/admin/posts/update/{{ post.id }}/post_category_id:{{ post_category.id }}" title="Ubah"><i class="fa fa-pencil-square fa-2x"></i></a>
								<br><a href="javascript:void(0)" class="delete" data-post-category-id="{{ post_category.id }}" data-id="{{ post.id }}" title="Hapus"><i class="fa fa-trash-o fa-2x"></i></a>
							</td>
						</tr>
					{% elsefor %}
						<tr>
							<td colspan="4"><i>No List News</i></td>
						</tr>
					{% endfor %}
					</tbody>
				</table>
				{% if page.total_pages > 1 %}
				<div class="weepaging">
					<p>
						<b>Halaman:</b>&nbsp;&nbsp;
						{% for i in pages %}
							{% if i == page.current %}
							<b>{{ i }}</b>
							{% else %}
							<a href="/admin/posts/index/post_category_id:{{ post_category.id }}/page:{{ i }}">{{ i }}</a>
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
			if (!confirm('Anda yakin ingin menghapus content ini ?')) {
				return !1
			}
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/posts/delete/' + this.dataset.id + '/post_category_id:' + this.dataset.postCategoryId,
			document.body.appendChild(form),
			form.submit()
		}
	}
	for (var items = document.querySelectorAll('.published'), i = items.length; i--; ) {
		items[i].onclick = function() {
			var form = document.createElement('form');
			form.method = 'POST',
			form.action = '/admin/posts/update/' + this.dataset.id + '/post_category_id:' + this.dataset.postCategoryId + '/published:1',
			document.body.appendChild(form),
			form.submit()
		}
	}
</script>