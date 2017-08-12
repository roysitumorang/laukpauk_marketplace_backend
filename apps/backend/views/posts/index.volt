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
				<a href="/admin/posts{% if user_id %}/index/user_id:{{ user_id }}{% endif %}{% if page.current > 1 %}/page:{{ page.current }}{% endif %}"><h2>Konten</h2></a>
				<div class="right-wrapper pull-right">
					<ol class="breadcrumbs">
						<li><a href="/admin"><i class="fa fa-home"></i></a></li>
						<li><span>Konten</span></li>
					</ol>
					<a class="sidebar-right-toggle" data-open="sidebar-right"><i class="fa fa-chevron-left"></i></a>
				</div>
			</header>
			<!-- start: page -->
			<header class="panel-heading">
				<h2 class="panel-title">Konten</h2>
			</header>
			<div class="panel-body">
				<!-- Content //-->
				{{ flashSession.output() }}
				<form method="POST" action="/admin/posts/save" enctype="multipart/form-data">
					<table class="table table-striped">
						<tr>
							<td>Merchant :</td>
							<td>
								<select name="user_id" onchange="location.href='/admin/posts'+(this.value?'/index/user_id:'+this.value:'')">
									{% for item in users %}
										<option value="{{ item.id }}"{% if item.id == user.id %} selected{% endif %}>{{ item.company }}</option>
									{% endfor %}
								</select>
							</td>
						</tr>
						<tr>
							<td>Tentang Kami :</td>
							<td><textarea name="company_profile" cols="70" rows="10" placeholder="Tentang Kami">{{ user.company_profile }}</textarea></td>
						</tr>
						<tr>
							<td>Kebijakan dan Privasi :</td>
							<td><textarea name="terms_conditions" cols="70" rows="10" placeholder="Kebijakan dan Privasi">{{ user.terms_conditions }}</textarea></td>
						</tr>
						<tr>
							<td>Hubungi Kami :</td>
							<td><textarea name="contact" cols="70" rows="10" placeholder="Hubungi Kami">{{ user.contact }}</textarea></td>
						</tr>
					</table>
					<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> SIMPAN</button>
				</form>
				<!-- eof Content //-->
			</div>
			<!-- end: page -->
		</section>
	</div>
	{{ partial('partials/right_side') }}
</section>